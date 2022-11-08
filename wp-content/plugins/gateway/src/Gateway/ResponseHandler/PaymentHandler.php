<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class PaymentHandler
 */
class PaymentHandler implements HandlerInterface {
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * PaymentHandler constructor.
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

		if (!isset($response['data']) || (isset($response['data']) && $response['data']['response'] !== 'approved')) {
			$order_id = $context->get_id();
			wc_add_notice(
				sprintf(
				// translators: %s order id
					__('Transaction has been declined, please contact the support. Refer to this Order ID: %s.', Plugin::ID),
					$order_id
				),
				'error'
			);
			return false;
		} else {
			$context->payment_complete($response['data']['id']);
			return true;
		}
	}
}
