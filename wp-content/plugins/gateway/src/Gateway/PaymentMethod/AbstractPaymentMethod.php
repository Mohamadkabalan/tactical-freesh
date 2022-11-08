<?php declare(strict_types=1);

namespace CustomPaymentGateway\Gateway\PaymentMethod;

use CustomPaymentGateway\Gateway\Client\CreateCustomerClient;
use CustomPaymentGateway\Gateway\Client\CreatePaymentMethodClient;
use CustomPaymentGateway\Gateway\Client\CreatePlanClient;
use CustomPaymentGateway\Gateway\Client\RefundClient;
use CustomPaymentGateway\Gateway\Client\CreateSubscriptionClient;
use CustomPaymentGateway\Gateway\Client\SaleClient;
use CustomPaymentGateway\Gateway\Client\UpdateSubscriptionClient;
use CustomPaymentGateway\Gateway\Client\VoidClient;
use CustomPaymentGateway\Gateway\Command\CommandInterface;
use CustomPaymentGateway\Gateway\Command\GatewayCommand;
use CustomPaymentGateway\Gateway\Config\AbstractPaymentMethodConfig;
use CustomPaymentGateway\Gateway\Config\GatewayConfig;
use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Gateway\RequestBuilder\AddressBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\BuilderComposite;
use CustomPaymentGateway\Gateway\RequestBuilder\CreateCustomerBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\CreatePaymentMethodBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\PaymentBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\PlanBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\RefundBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\SubscriptionBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\VoidBuilder;
use CustomPaymentGateway\Gateway\ResponseHandler\HandlerChain;
use CustomPaymentGateway\Gateway\ResponseHandler\PaymentHandler;
use CustomPaymentGateway\Gateway\ResponseHandler\SavePaymentMethodHandler;
use CustomPaymentGateway\Gateway\ResponseHandler\PlanHandler;
use CustomPaymentGateway\Gateway\ResponseHandler\RefundHandler;
use CustomPaymentGateway\Gateway\ResponseHandler\SubscriptionHandler;
use CustomPaymentGateway\Gateway\ResponseHandler\CustomerRecordHandler;
use CustomPaymentGateway\Helper\WcSubscriptionsWrapper;
use CustomPaymentGateway\Log\Logger;
use CustomPaymentGateway\Plugin;
use WC_Customer;
use WC_Order;
use WC_Payment_Gateway;
use WC_Payment_Tokens;
use WC_Product;
use WC_Subscription;
use WC_Subscriptions_Admin;
use WC_Subscriptions_Cart;
use WC_Subscriptions_Product;
use WP_Error;

/**
 * Class AbstractPaymentMethod
 */
abstract class AbstractPaymentMethod extends WC_Payment_Gateway {

	/**
	 * @var \CustomPaymentGateway\Gateway\SDK\SDK $connector
	 */
	protected $connector;

	/**
	 * @var \CustomPaymentGateway\Log\LoggerInterface $logger
	 */
	protected $logger;

	/**
	 * @var \WooCommerce $wc
	 */
	protected $wc;

	/**
	 * @var AbstractPaymentMethodConfig
	 */
	public $config;

	/**
	 * AbstractPaymentMethod constructor.
	 */
	public function __construct(AbstractPaymentMethodConfig $config) {
		$this->id = $config::METHOD_ID;
		$this->config = $config;
		$this->icon = apply_filters(Plugin::ID . '_icon', '');
		$this->has_fields = TRUE;
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->config->getOption(AbstractPaymentMethodConfig::KEY_TITLE);
		$this->description = $this->config->getOption(AbstractPaymentMethodConfig::KEY_DESCRIPTION);
		$this->method_title = __('Payment Gateway - Payment method', Plugin::ID);

		// Gateway Connector.
		$gateway_config = GatewayConfig::getOptions();
		$this->connector = new SDK();
		$this->connector->apiKey = $gateway_config[GatewayConfig::KEY_API_KEY];
		$this->connector->url = $gateway_config[GatewayConfig::KEY_API_URL];

		// Logger.
		$this->logger = new Logger();
		$this->logger->setDebug($gateway_config[GatewayConfig::KEY_DEBUG] === 'yes');

		// Actions.
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

		if (class_exists('\WC_Subscriptions_Admin')) {
			add_action('woocommerce_subscription_pending-cancel_' . $this->id, [$this, 'cancelSubscription']);
			add_action('woocommerce_subscription_cancelled_' . $this->id, [$this, 'cancelSubscription']);
		}
		add_filter('woocommerce_available_payment_gateways', [$this, 'filterGateway']);

		if (WcSubscriptionsWrapper::isChangePaymentMethodRequest()) {
			wp_enqueue_script(
				Plugin::ID . '_tokenization_form',
				plugins_url('/gateway/frontend/src/js/tokenization.js'),
				['jquery'],
				'1.0.0',
				TRUE
			);
		}
	}

	/**
	 * @return string|null The token ID if it's available.
	 */
	public function getTokenIdFromPost(): ?string {
		if (isset($_POST['woocommerce-process-checkout-nonce']) && !wp_verify_nonce(sanitize_key(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
			wp_nonce_ays('');
		}
		$token_key = 'wc-' . $this->id . '-payment-token';
		if (isset($_POST[$token_key])
			&& $_POST[$token_key] !== 'new'
		) {
			return sanitize_text_field(wp_unslash($_POST[$token_key]));
		}
		return NULL;
	}

	/**
	 * Decide if the payment method should be saved
	 */
	public function shouldSavePaymentMethod(): bool {
		// On the add payment method page this should always return true
		if (isset($_POST['woocommerce_add_payment_method']) && $_POST['woocommerce_add_payment_method'] === '1') {
			return TRUE;
		}
		$new_token_key = 'wc-' . $this->id . '-new-payment-method';
		return isset($_POST[$new_token_key]) && $_POST[$new_token_key] === 'true' && $this->config->getOption(AbstractPaymentMethodConfig::KEY_SAVE_METHOD) === 'yes';
	}

	/**
	 * Checkbox for saving payment methods
	 */
	public function savePaymentMethodCheckbox(): void {
		?>
		<p class="form-row woocommerce-SavedPaymentMethods-saveNew">
			<input id="wc-<?php echo esc_attr($this->id); ?>-new-payment-method"
				   name="wc-<?php echo esc_attr($this->id); ?>-new-payment-method" type="checkbox" value="true"
				   style="width:auto;"/>
			<label for="wc-<?php echo esc_attr($this->id); ?>-new-payment-method"
				   style="display:inline;"><?php echo esc_html__('Save to account', 'woocommerce'); ?><?php
				if (class_exists('\WC_Subscriptions') && (WC_Subscriptions_Cart::cart_contains_subscription() || WcSubscriptionsWrapper::isChangePaymentMethodRequest())) {
					echo '<span class="required">*</span>';
				}
				?>
			</label>
		</p>
		<?php
	}

	/**
	 * Payment section's fields (wrapper of the parent method).
	 **/
	public function payment_fields(): void {
		$this->paymentFields();
	}

	/**
	 * @inheritDoc
	 */
	public function process_payment($order_id) {
		$order = new WC_Order($order_id);
		$token_id = $this->getTokenIdFromPost();
		$token = $token_id ? WC_Payment_Tokens::get($token_id)->get_token() : NULL;
		$order_contains_subscription = WcSubscriptionsWrapper::orderContainsSubscription($order_id);
		$is_payment_method_change = WcSubscriptionsWrapper::orderIsSubscription($order_id);
		$uid = get_current_user_id();

		// When the user is paying with a token but has no customer_id attached yet.
		if (!get_user_meta($uid, Plugin::ID . '_customer_id', TRUE) && $token) {
			$response = $this->connector->getCustomer($token);
			if ($response['http_code'] !== 200) {
				$this->logger->error('Getting customer data to sync user failed. Gateway response: ' . json_encode(Formatter::maskSensitiveData($response)));
				wc_add_notice(__('Gateway error. Please contact the store owner.', Plugin::ID), 'error');
				return [];
			}
			update_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', $token);
			$token = WC_Payment_Tokens::get($token_id);
			$token->set_token($response['result']['data']['data']['customer']['defaults']['payment_method_id']);
			$token->save();
			$token = $token->get_token();
		}

		// When the user would like to save a payment method and there's no existing token.
		if (!$token && $this->shouldSavePaymentMethod()) {
			$response = $this->updateCustomerData($order);
			if (is_wp_error($response)) {
				wc_add_notice($response->get_error_message(), 'error');
				return [];
			}
			$tokens = WC_Payment_Tokens::get_customer_tokens(get_current_user_id(), $this->id);
			$token = $tokens ? end($tokens)->get_token() : NULL;
		}

		if ($is_payment_method_change || $order_contains_subscription) {
			$items = $order->get_items();
			/** @var WC_Product $product */
			$product = reset($items)->get_product();
			if (!$product->get_meta(Plugin::ID . '_plan_id')) {
				$this->planCommand($product)->execute($order);
			}
			if ($is_payment_method_change) {
				$success = $this->updateSubscriptionCommand($token, $product)->execute($order);
			} else {
				$success = $this->createSubscriptionCommand($token, $product)->execute($order);
			}
		} else {
			$success = $this->paymentCommand($token)->execute($order);
		}

		if (is_wp_error($success)) {
			wc_add_notice($success->get_error_message(), 'error');
			return [];
		}
		if ($success) {
			return [
				'result' => 'success',
				'redirect' => $this->get_return_url($order),
			];
		}
		return [];
	}

	/**
	 * @inheritDoc
	 *
	 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function process_refund($order_id, $amount = NULL, $reason = ''): bool {
		// phpcs:enable
		if (!$amount) {
			return FALSE;
		}
		$order = wc_get_order($order_id);
		if (!$order || !$order->get_transaction_id()) {
			return FALSE;
		}
		$transaction = $this->connector->getTransaction($order->get_transaction_id());
		if (!$transaction) {
			wc_add_notice('Could not connect to gateway.', Plugin::ID);
			return FALSE;
		}
		if ($transaction['http_code'] === 200) {
			$transaction_status = $transaction['result']['data']['status'];
		} else {
			return FALSE;
		}
		if ($transaction_status && in_array($transaction_status, ['settled', 'partially_refunded'], TRUE)) {
			$success = $this->refundCommand($amount)->execute($order);
		} else {
			$success = $this->voidCommand()->execute($order);
		}
		return $success;
	}

	/**
	 * @inheritDoc
	 */
	public function add_payment_method() {
		$response = $this->updateCustomerData(new WC_Customer(get_current_user_id()));
		$result = [
			'redirect' => wc_get_endpoint_url('payment-methods'),
		];
		if ($response['status'] === 'success') {
			$result['result'] = 'success';
		} else {
			$result['result'] = 'failure';
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function validate_fields() {
		$all_green = TRUE;

		if (class_exists('\WC_Subscriptions_Order') && (WC_Subscriptions_Cart::cart_contains_subscription() || WcSubscriptionsWrapper::isChangePaymentMethodRequest())) {
			$token_id = $this->getTokenIdFromPost();
			if (!$token_id) {
				$save_method_form_id = 'wc-' . $this->id . '-new-payment-method';
				if (!isset($_POST[$save_method_form_id]) || $_POST[$save_method_form_id] !== 'true') {
					wc_add_notice(__('It is mandatory to save a payment method when purchasing a subscription.', Plugin::ID), 'error');
					$all_green = FALSE;
				}
			}
		}

		return $all_green;
	}

	/**
	 * @param \WC_Order|\WC_Customer $context
	 */
	private function updateCustomerData($context) {
		if (!get_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', TRUE)) {
			$command = $this->createCustomerCommand();
		} else {
			$command = $this->createPaymentMethodCommand();
		}
		$result = $command->execute($context);
		if (is_wp_error($result)) {
			return $result;
		}
		return $command->getResponse();
	}

	/**
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function filterGateway($available_gateways) {
		$method_id = $this->id;
		if (!isset($available_gateways[$method_id])) {
			return $available_gateways;
		}

		if (!is_checkout()) {
			return $available_gateways;
		}
		if (class_exists('\WC_Subscriptions_Admin')) {
			$cart = WC()->cart;
			if ($cart && WcSubscriptionsWrapper::isMixedCheckout($cart)) {
				unset($available_gateways[$method_id]);
				return $available_gateways;
			}

			$recurring_cart = is_array($cart->recurring_carts) && count($cart->recurring_carts) > 0 ? $cart->recurring_carts : NULL;

			// When the cart contains a subscription.
			if ($recurring_cart) {
				$recurring_cart = reset($recurring_cart);
				$recurring_cart = $recurring_cart->get_cart();
				$subscription = reset($recurring_cart)['data'];
				if (
					Formatter::formatAmount(WC_Subscriptions_Product::get_sign_up_fee($subscription)) > 0 ||
					(WC_Subscriptions_Product::get_period($subscription) === 'day' && (int)WC_Subscriptions_Product::get_interval($subscription) < 3) ||
					$this->config->getOption(AbstractPaymentMethodConfig::KEY_SAVE_METHOD) !== 'yes'
				) {
					unset($available_gateways[$method_id]);
					return $available_gateways;
				}
			}
		}
		return $available_gateways;
	}

	/**
	 * @param WC_Subscription $subscription
	 *
	 * @return bool
	 */
	public function cancelSubscription(WC_Subscription $subscription) {
		$subscription_id = $subscription->get_meta(Plugin::ID . '_subscription_id');
		$response = $this->connector->updateSubscriptionStatus($subscription_id, SDK::SUB_STATUS_CANCELLED);
		$result = $response['result'];
		if ($result['status'] === 'success') {
			return TRUE;
		}
		$this->logger->info(
			sprintf(
			// translators: %1$s: subscription id, %2$s: response message
				__(
					'Cancelling subscription failed. Subscription ID: %1$s / Message: %2$s',
					Plugin::ID
				),
				$subscription_id,
				$result['msg']
			)
		);
		return FALSE;
	}

	/**
	 * Payment section's fields implementation.
	 **/
	public function paymentFields(): void {
		wp_enqueue_script('wc-credit-card-form');
		if (is_user_logged_in() && $this->supports('tokenization') && $this->config->getOption(AbstractPaymentMethodConfig::KEY_SAVE_METHOD) === 'yes' && is_checkout()) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->renderPaymentForm();
			$this->savePaymentMethodCheckbox();
		} else {
			$this->renderPaymentForm();
		}
	}

	/**
	 * @param string     $token
	 * @param WC_Product $product
	 *
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function createSubscriptionCommand(string $token, WC_Product $product) {
		return new GatewayCommand(new BuilderComposite([new SubscriptionBuilder($this->logger, $token, static::PAYMENT_METHOD_TYPE, $product), new PlanBuilder($this->logger, $product)]), new CreateSubscriptionClient($this->logger, $this->connector), new SubscriptionHandler($this->logger));
	}

	/**
	 * @param string     $token
	 * @param WC_Product $product
	 *
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function updateSubscriptionCommand(string $token, WC_Product $product) {
		return new GatewayCommand(new BuilderComposite([new SubscriptionBuilder($this->logger, $token, static::PAYMENT_METHOD_TYPE, $product, TRUE), new PlanBuilder($this->logger, $product)]), new UpdateSubscriptionClient($this->logger, $this->connector), new SubscriptionHandler($this->logger));
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function planCommand(WC_Product $product) {
		return new GatewayCommand(new PlanBuilder($this->logger, $product), new CreatePlanClient($this->logger, $this->connector), new PlanHandler($product));
	}

	/**
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function voidCommand(): CommandInterface {
		return new GatewayCommand(new VoidBuilder($this->logger), new VoidClient($this->logger, $this->connector), new RefundHandler($this->logger));
	}

	/**
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function refundCommand($amount): CommandInterface {
		return new GatewayCommand(new RefundBuilder($this->logger, $amount), new RefundClient($this->logger, $this->connector), new RefundHandler($this->logger));
	}

	/**
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function createCustomerCommand(): CommandInterface {
		return new GatewayCommand(
			new BuilderComposite([new AddressBuilder(TRUE), new CreateCustomerBuilder($this)]),
			new CreateCustomerClient($this->logger, $this->connector),
			new HandlerChain([new CustomerRecordHandler($this->logger), new SavePaymentMethodHandler($this)])
		);
	}

	/**
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function createPaymentMethodCommand(): CommandInterface {
		return new GatewayCommand(
			new CreatePaymentMethodBuilder($this),
			new CreatePaymentMethodClient($this->logger, $this->connector),
			new SavePaymentMethodHandler($this)
		);
	}

	/**
	 * @param string|null $token
	 *
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function paymentCommand(?string $token): CommandInterface {
		$builders = [new PaymentBuilder($this->logger, $this, $token), new AddressBuilder()];
		$command = new GatewayCommand(
			new BuilderComposite($builders),
			new SaleClient($this->logger, $this->connector),
			new PaymentHandler($this->logger)
		);
		if (!$command) {
			$command = new WP_Error('500', 'Error with payment.');
		}
		return $command;
	}

	/**
	 * Echo the form for using a payment method
	 */
	abstract public function renderPaymentForm(): void;

	/**
	 * @return array
	 */
	abstract public static function getPostData(): array;
}
