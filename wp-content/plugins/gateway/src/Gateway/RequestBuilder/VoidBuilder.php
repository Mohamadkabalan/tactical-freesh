<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class VoidBuilder
 */
class VoidBuilder implements BuilderInterface {
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * VoidBuilder constructor.
	 */
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
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
		return $data;
	}
}
