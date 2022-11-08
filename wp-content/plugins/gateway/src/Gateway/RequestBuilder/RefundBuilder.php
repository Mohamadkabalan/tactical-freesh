<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class RefundBuilder
 */
class RefundBuilder implements BuilderInterface {

	/**
	 * @var float|string
	 */
	private $amount;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * RefundBuilder constructor.
	 *
	 * @param LoggerInterface $logger
	 * @param float|string    $amount The amount to be refunded
	 */
	public function __construct(LoggerInterface $logger, $amount) {
		$this->logger = $logger;
		$this->amount = $amount;
	}

	/**
	 * @inheritDoc
	 */
	public function build($context) {
		if (!($context instanceof \WC_Order)) {
			$this->logger->error('Not in order context for this gateway command.');
			return new WP_Error('500', __('Gateway error. Please contact the store owner.', Plugin::ID));
		}
		$data = [];
		$data['transaction_id'] = $context->get_transaction_id();
		$data['fields'] = [
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'amount' => Formatter::formatAmount($this->amount),
		];
		return $data;
	}
}
