<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod;
use CustomPaymentGateway\Plugin;
use WC_Payment_Token_CC;

/**
 * Class CcPaymentHandler
 */
class SavePaymentMethodHandler implements HandlerInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod
	 */
	private $method;

	/**
	 * CcPaymentHandler constructor.
	 *
	 * @param \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod $method Payment method
	 */
	public function __construct(AbstractPaymentMethod $method) {
		$this->method = $method;
	}

	/**
	 * @inheritDoc
	 *
	 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	 */
	public function handle($context, array $response) {
		// phpcs:enable
		if ($this->method->shouldSavePaymentMethod() === false) {
			return true;
		}
		$payment_method = null;
		$payment_method_type = $this->method::PAYMENT_METHOD_TYPE;
		// Unfortunately the list of available payments for cards is in plural, the naming is not consistent.
		$payments_key = $payment_method_type === 'card' ? 'cards' : 'ach';
		if (isset($response['data']['data']['customer']['payments'][$payments_key]) &&
			!empty($response['data']['data']['customer']['payments'][$payments_key])) {
			$payment_method = end($response['data']['data']['customer']['payments'][$payments_key]);
		}
		if ($payment_method) {
			$token = null;
			switch ($payment_method_type) {
				case 'card':
					$token = new WC_Payment_Token_CC();
					$token->set_last4(substr($payment_method['masked_number'], -4));
					$token->set_expiry_year('20' . substr($payment_method['expiration_date'], -2));
					$token->set_expiry_month(substr($payment_method['expiration_date'], 0, 2));
					$token->set_card_type($payment_method['card_type']);
					break;
				case 'ach':
					$token = new \WC_Payment_Token_ECheck();
					$token->set_last4(substr($payment_method['routing_number'], -4));
					break;
			}
			if ($token) {
				$token->set_token($payment_method['id']);
				$token->set_gateway_id($this->method->config::METHOD_ID);
				$token->set_user_id(get_current_user_id());
				// Save the new token to the database.
				$token->save();
				return true;
			}
		}
		wc_add_notice(
			__('Could not save payment method.', Plugin::ID),
			'error'
		);
		return false;
	}
}
