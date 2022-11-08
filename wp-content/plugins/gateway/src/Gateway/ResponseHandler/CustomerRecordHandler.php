<?php

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;

class CustomerRecordHandler implements HandlerInterface {
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * CreateCustomerHandler constructor.
	 */
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function handle($context, array $response) {
		if (isset($response['data']['id'])) {
			$this->logger->info(
				'customer id stored for user: ' . $response['data']['id']
			);
			update_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', $response['data']['id']);
			return true;
		}
		wc_add_notice(
			__('Could not save customer id to user.', Plugin::ID),
			'error'
		);
		return false;
	}
}