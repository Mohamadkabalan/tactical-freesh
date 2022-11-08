<?php

namespace CustomPaymentGateway\Actions;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\GatewayConfig;
use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Gateway\PaymentMethod\CcMethod;
use CustomPaymentGateway\Log\Logger;
use CustomPaymentGateway\Plugin;
use WC_Cart;
use WC_Subscriptions_Cart;
use WP_Error;

class SurchargeAction implements ActionInterface {
	public const CC_NUMBER_KEY = 'cc_number';

	public static function register() {
		$handler = new self();
		$options = CcConfig::getOptions();

		if ($options && $options[CcConfig::KEY_SURCHARGE_TYPE] !== 'none') {
			add_action('woocommerce_checkout_init', [$handler, 'emptySessionData']);
			add_action('wp_ajax_' . CcConfig::METHOD_ID . '_handle_surcharge', [$handler, 'handleSurcharge']);
			add_action('wp_ajax_nopriv_' . CcConfig::METHOD_ID . '_handle_surcharge', [$handler, 'handleSurcharge']);
			add_action('wp', [$handler, 'registerScript']);
			add_action('woocommerce_cart_calculate_fees', [$handler, 'addSurcharge']);
		}
	}

	/**
	 * Enqueue surcharge JS
	 */
	public function registerScript() {
		if (!is_checkout() || (class_exists('\WC_Subscriptions_Order') && WC_Subscriptions_Cart::cart_contains_subscription())) {
			return;
		}
		$method_id = CcConfig::METHOD_ID;
		$handle = $method_id . '_ajax_handle_surcharge';
		wp_enqueue_script(
			$handle,
			plugins_url('/gateway/frontend/src/js/surcharge.js'),
			['jquery'],
			'1.0.0',
			true
		);
		wp_localize_script(
			$handle,
			$method_id . '_data',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('surcharge_nonce'),
				'id' => $method_id,
				'action' => $method_id . '_handle_surcharge',
			]
		);
		wp_enqueue_script('wp_ajax');
	}

	/**
	 * Unset the card number on page reload.
	 */
	public function emptySessionData(): void {
		if ('XMLHttpRequest' !== filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') && is_checkout()) {
			if (WC()->session->get(self::CC_NUMBER_KEY)) {
				WC()->session->__unset(self::CC_NUMBER_KEY);
			}
		}
	}

	/**
	 * Ajax handler for handling surcharge.
	 */
	public function handleSurcharge(): void {
		check_ajax_referer('surcharge_nonce', 'nonce');
		$options = CcConfig::getOptions();
		$cc_number = CcMethod::getPostData()[CcMethod::FORM_CC_NUMBER_ID];
		if (strlen($cc_number) > 5) {
			WC()->session->set(self::CC_NUMBER_KEY, $cc_number);
			// TODO: This request shouldn't be necessary but it's not possible to show a message in `addSurcharge`
			if ($this->isSurchargeable($cc_number)) {
				$dyn_surcharge_info = '';
				$surcharge = $options[CcConfig::KEY_SURCHARGE];
				switch ($options[CcConfig::KEY_SURCHARGE_TYPE]) {
					case 'percentage':
						$dyn_surcharge_info = "{$surcharge}%";
						break;
					case 'flat':
						$dyn_surcharge_info = "\${$surcharge}";
						break;
					default:
						break;
				}
				echo esc_html(
					sprintf(
					// translators: %s the flat value or percentage of surcharge
						__(
							'We impose a surcharge on credit cards that is not greater than our cost of acceptance. This surcharge rate is %s. This surcharge is not applied on debit card or ach transactions.',
							Plugin::ID
						),
						$dyn_surcharge_info
					)
				);
			}
		} else {
			if (WC()->session->get(self::CC_NUMBER_KEY)) {
				WC()->session->__unset(self::CC_NUMBER_KEY);
			}
		}
		wp_die();
	}

	/**
	 * Perform a binLookup and determine if the current payment method is surchargeable
	 *
	 * @param string $bin_lookup_number The number to check for surchargeability
	 *
	 * @return bool|WP_Error
	 */
	private function isSurchargeable(string $bin_lookup_number) {
		if (!is_checkout() || (class_exists('\WC_Subscriptions_Order') && WC_Subscriptions_Cart::cart_contains_subscription())) {
			return false;
		}
		$gateway_options = GatewayConfig::getOptions();
		$connector = new SDK();
		$connector->apiKey = $gateway_options[GatewayConfig::KEY_API_KEY];
		$connector->url = $gateway_options[GatewayConfig::KEY_API_URL];
		$logger = new Logger();
		$logger->setDebug($gateway_options['debug'] === 'yes');
		$response = $connector->binLookup($bin_lookup_number);
		if (!$response) {
			$logger->setDebug(true);
			$logger->error('No response from the gateway while checking surchargeability.');
			return false;
		}
		$loggable_response = $response;
		if (isset($loggable_response['result']['data']['bin'])) {
			$loggable_response['result']['data']['bin'] = 'XXX';
		}
		$logger->info('Surcharge response: ' . json_encode($loggable_response));
		$result = $response['result'];
		if (isset($result['data']['is_surchargeable'])) {
			return $result['data']['is_surchargeable'];
		}
		return false;
	}

	/**
	 * Add surcharge to the fees
	 */
	public function addSurcharge(WC_Cart $cart): void {
		if ((is_admin() && !defined('DOING_AJAX')) || !is_checkout()) {
			return;
		}

		$cc_number = WC()->session->get(self::CC_NUMBER_KEY);
		// CC number from post data should be the priority as AJAX can be tricked.
		$cc_number = CcMethod::getPostData()[CcMethod::FORM_CC_NUMBER_ID] ?? $cc_number;

		if ($cc_number && $this->isSurchargeable($cc_number)) {
			$options = CcConfig::getOptions();
			$surcharge = Formatter::formatSurcharge($cart->get_cart_contents_total(), $options[CcConfig::KEY_SURCHARGE], $options[CcConfig::KEY_SURCHARGE_TYPE]);
			$cart->add_fee(CcConfig::getOptions()[CcConfig::KEY_SURCHARGE_TITLE], $surcharge);
		}
	}
}