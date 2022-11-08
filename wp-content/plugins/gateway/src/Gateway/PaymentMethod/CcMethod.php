<?php declare(strict_types=1);

namespace CustomPaymentGateway\Gateway\PaymentMethod;

use CustomPaymentGateway\Gateway\Client\SaleClient;
use CustomPaymentGateway\Gateway\Command\CommandInterface;
use CustomPaymentGateway\Gateway\Command\GatewayCommand;
use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Gateway\RequestBuilder\AddressBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\BuilderComposite;
use CustomPaymentGateway\Gateway\RequestBuilder\PaymentBuilder;
use CustomPaymentGateway\Gateway\RequestBuilder\SurchargeBuilder;
use CustomPaymentGateway\Gateway\ResponseHandler\PaymentHandler;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class CcMethod
 */
final class CcMethod extends AbstractPaymentMethod {
	public const PAYMENT_METHOD_TYPE = 'card';
	public const FORM_ID = CcConfig::METHOD_ID . '_cc_form';
	public const FORM_CC_NUMBER_ID = CcConfig::METHOD_ID . '_card_number';
	public const FORM_CC_EXPIRY_ID = CcConfig::METHOD_ID . '_card_expiry';
	public const FORM_CC_CVC_ID = CcConfig::METHOD_ID . '_card_cvc';

	/**
	 * @var \CustomPaymentGateway\Gateway\Config\CcConfig
	 */
	public $config;

	/**
	 * Constructor method
	 */
	public function __construct() {
		parent::__construct(new CcConfig());
		$this->method_title = __('Payment Gateway - Credit card', Plugin::ID);
		$supports = [
			'products',
			'default_credit_card_form',
			'refunds',
			'subscriptions',
			'gateway_scheduled_payments',
			'subscription_cancellation',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
		];
		if ($this->config->getOption(CcConfig::KEY_SAVE_METHOD) === 'yes') {
			$supports[] = 'tokenization';
		}
		$this->supports = $supports;
	}

	/**
	 * Validate the Credit Card payment fields.
	 */
	public function validate_fields(): bool {
		$all_green = parent::validate_fields();
		$token_id = $this->getTokenIdFromPost();
		if (!$token_id) {
			$post_data = self::getPostData();
			foreach ($post_data as $key => $data) {
				switch ($key) {
					case self::FORM_CC_NUMBER_ID:
						if ($data === '') {
							wc_add_notice(__('Please provide a credit card number.', Plugin::ID), 'error');
							$all_green = FALSE;
						}
						// TODO: Luhn validator.
						break;
					case self::FORM_CC_EXPIRY_ID:
						if ($data === '') {
							wc_add_notice(__('Please provide an expiry date for your credit card.', Plugin::ID), 'error');
							$all_green = FALSE;
						}
						// TODO: Expiry validator.
						break;
					case self::FORM_CC_CVC_ID:
						if ($data === '') {
							wc_add_notice(__('Please provide the card security code.', Plugin::ID), 'error');
							$all_green = FALSE;
						}
						// TODO: CVC validator.
						break;
				}
			}
		}

		return $all_green;
	}

	/**
	 * @inheritdoc
	 */
	public function renderPaymentForm(): void {
		?>
		<fieldset id="<?php echo esc_attr(self::FORM_ID); ?>" class='wc-credit-card-form wc-payment-form'>
			<?php do_action('woocommerce_credit_card_form_start', CcConfig::METHOD_ID); ?>
			<p class="form-row form-row-wide">
				<label for="<?php echo esc_attr(self::FORM_CC_NUMBER_ID); ?>">
					<?php echo esc_html__('Card Number', Plugin::ID); ?>
					<span class="required">*</span>
				</label>
				<input
					id="<?php echo esc_attr(self::FORM_CC_NUMBER_ID); ?>"
					class="input-text wc-credit-card-form-card-number"
					type="text"
					maxlength="20"
					autocomplete="off"
					placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;"
					name="<?php echo esc_attr(self::FORM_CC_NUMBER_ID); ?>"
				/>
			</p>
			<p class="form-row form-row-first">
				<label
					for="<?php echo esc_attr(self::FORM_CC_EXPIRY_ID); ?>"><?php echo esc_html__('Expiry (MM/YY)', Plugin::ID); ?>
					<span class="required">*</span>
				</label>
				<input
					id="<?php echo esc_attr(self::FORM_CC_EXPIRY_ID); ?>"
					class="input-text wc-credit-card-form-card-expiry"
					type="text" maxlength="7"
					autocomplete="off"
					placeholder="MM / YY"
					name="<?php echo esc_attr(self::FORM_CC_EXPIRY_ID); ?>"
				/>
			</p>
			<p class="form-row form-row-last">
				<label
					for="<?php echo esc_attr(self::FORM_CC_CVC_ID); ?>"><?php echo esc_html__('Card Code', Plugin::ID); ?>
					<span class="required">*</span>
				</label>
				<input
					id="<?php echo esc_attr(self::FORM_CC_CVC_ID); ?>"
					class="input-text wc-credit-card-form-card-cvc"
					type="text"
					autocomplete="off"
					maxlength="4"
					placeholder="CVC"
					name="<?php echo esc_attr(self::FORM_CC_CVC_ID); ?>"
				/>
			</p>
			<?php do_action('woocommerce_credit_card_form_end', CcConfig::METHOD_ID); ?>
			<div class="clear"></div>
		</fieldset>
		<?php
	}

	/**
	 * @inheritdoc
	 */
	public static function getPostData(): array {
		if (isset($_POST['woocommerce-process-checkout-nonce']) && !wp_verify_nonce(sanitize_key(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
			wp_nonce_ays('');
		}
		return [
			self::FORM_CC_NUMBER_ID => isset($_POST[self::FORM_CC_NUMBER_ID]) ? Formatter::formatCreditCardNumber(sanitize_text_field(wp_unslash($_POST[self::FORM_CC_NUMBER_ID]))) : NULL,
			self::FORM_CC_EXPIRY_ID => isset($_POST[self::FORM_CC_EXPIRY_ID]) ? Formatter::formatExpiryDate(sanitize_text_field(wp_unslash($_POST[self::FORM_CC_EXPIRY_ID]))) : NULL,
			self::FORM_CC_CVC_ID => isset($_POST[self::FORM_CC_CVC_ID]) ? sanitize_text_field(wp_unslash($_POST[self::FORM_CC_CVC_ID])) : NULL,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getFormattedPaymentData(): array {
		$post_data = self::getPostData();
		return [
			'number' => $post_data[self::FORM_CC_NUMBER_ID],
			'expiration_date' => $post_data[self::FORM_CC_EXPIRY_ID],
			'cvc' => $post_data[self::FORM_CC_CVC_ID],
		];
	}

	/**
	 * @param string|null $token
	 *
	 * @return \CustomPaymentGateway\Gateway\Command\CommandInterface The instance creating, sending and handling the request
	 */
	protected function paymentCommand(?string $token): CommandInterface {
		$builders = [new PaymentBuilder($this->logger, $this, $token), new AddressBuilder()];
		if (is_checkout() && $this->config->getOption(CcConfig::KEY_SURCHARGE_TYPE) !== 'none') {
			$builders[] = new SurchargeBuilder($this->logger, $this);
		}
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
}
