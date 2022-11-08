<?php

namespace CustomPaymentGateway\Actions;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Gateway\Config\GatewayConfig;
use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Log\Logger;
use CustomPaymentGateway\Plugin;
use WC_Payment_Token;

class PaymentMethodAction implements ActionInterface {

	public static function register() {
		add_action('woocommerce_payment_token_deleted', [__CLASS__, 'deleteToken'], 10, 2);
	}

	/**
	 * Delete the payment token.
	 */
	public static function deleteToken(int $token_id, WC_Payment_Token $token) {
		if (!in_array($token->get_gateway_id(), [CcConfig::METHOD_ID, ECheckConfig::METHOD_ID])) {
			return;
		}
		$connector = new SDK();
		$gateway_options = GatewayConfig::getOptions();
		$connector->apiKey = $gateway_options[GatewayConfig::KEY_API_KEY];
		$connector->url = $gateway_options[GatewayConfig::KEY_API_URL];
		$payment_type = $token->get_gateway_id() === CcConfig::METHOD_ID ? 'card' : 'ach';
		$response = $connector->deleteCustomerPayment(get_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', true), $payment_type, $token->get_token());
		$result = $response['result'];
		$logger = new Logger();
		$logger->setDebug($gateway_options['debug'] === 'yes');
		$logger->info(
			sprintf(
			// translators: %1$s: token id, %2$s: response message
				__(
					'Deleting payment method. Payment Method ID: %1$s / Message: %2$s',
					Plugin::ID
				),
				$token_id,
				$result['msg']
			)
		);
	}
}