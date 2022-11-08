<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod;
use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WC_Order;
use WP_Error;

/**
 * Class PaymentBuilder
 */
class PaymentBuilder implements BuilderInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod
	 */
	private $method;

	/**
	 * @var string|null
	 */
	private $token;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * PaymentBuilder constructor.
	 *
	 * @param LoggerInterface                                                   $logger
	 * @param \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod $method Payment method
	 * @param string|null                                                       $token
	 */
	public function __construct(LoggerInterface $logger, AbstractPaymentMethod $method, ?string $token) {
		$this->logger = $logger;
		$this->method = $method;
		$this->token = $token;
	}

	/**
	 * @inheritdoc
	 */
	public function build($context) {
		if (!($context instanceof \WC_Order)) {
			$this->logger->error('Not in order context for this gateway command.');
			return new WP_Error('500', __('Gateway error. Please contact the store owner.', Plugin::ID));
		}
		$data = [];
		$order_id = $context->get_id();

		$ip = '';
		// Whether ip is from the remote address
		if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} // Whether ip is from the proxy
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}// Whether ip is from the share internet
		elseif (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}

		$data['fields'] = [
			'type' => $this->method->config->getOption($this->method->config::KEY_TRANSACTION_TYPE),
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'ip_address' => $ip,
			'amount' => Formatter::formatAmount($context->get_total()),
			'tax_amount' => Formatter::formatAmount($context->get_total_tax()),
			'currency' => get_woocommerce_currency(),
			'order_id' => str_pad((string) $order_id, 17, '0', STR_PAD_LEFT),
			'po_number' => str_pad((string) $order_id, 17, '0', STR_PAD_LEFT),
			'email_receipt' => false,
			'create_vault_record' => false,
			'payment_method' => [],
		];

		if ($this->token) {
			$data['fields']['payment_method'] = [
				'customer' => [
					'id' => get_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', true),
					'payment_method_id' => $this->token,
					'payment_method_type' => $this->method::PAYMENT_METHOD_TYPE,
				],
			];
		} else {
			$data['fields']['payment_method'] = [
				$this->method::PAYMENT_METHOD_TYPE => $this->method->getFormattedPaymentData(),
			];
		}

		return $data;
	}
}
