<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class RefundHandler
 */
class RefundHandler implements HandlerInterface {
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * RefundHandler constructor.
	 */
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function handle($context, array $response) {
		if (!($context instanceof \WC_Order)) {
			$this->logger->error('Not in order context for this gateway command.');
			return new WP_Error('500', __('Gateway error. Please contact the store owner.', Plugin::ID));
		}
		if ($response['status'] === 'success') {
			return true;
		}
		wc_add_notice(
			__(
				'Gateway error. Please notify the store owner.',
				Plugin::ID
			),
			'error'
		);
		return false;
	}
}
