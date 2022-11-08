<?php

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

class SubscriptionHandler implements HandlerInterface {
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * SubscriptionHandler constructor.
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
			$subscription = wcs_get_subscriptions_for_order($context) ?: wcs_get_subscription($context);
			if (is_array($subscription)) {
				$subscription = reset($subscription);
			}
			$subscription->update_meta_data(Plugin::ID . '_subscription_id', $response['data']['id']);
			$next_bill_date = strtotime($response['data']['next_bill_date']);
			if ($next_bill_date > current_time('timestamp')) {
				$context->payment_complete();
			} else {
				$subscription->update_dates(['next_payment' => date('Y-m-d H:i:s', current_time('timestamp') + 10)]);
			}
			if (is_callable([$subscription, 'save'])) {
				$subscription->save();
			}
			return true;
		} else {
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
		}
	}
}