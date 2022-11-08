<?php

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WC_Product;
use WP_Error;

class PlanBuilder implements BuilderInterface {

	/**
	 * The product that can be purchased as a subscription
	 *
	 * @var \WC_Product
	 */
	private $product;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * PlanBuilder constructor.
	 *
	 * @param LoggerInterface $logger
	 * @param \WC_Product     $product
	 */
	public function __construct(LoggerInterface $logger, WC_Product $product) {
		$this->logger = $logger;
		$this->product = $product;
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
		$subscription = wcs_get_subscriptions_for_order($context) ?: wcs_get_subscription($context);
		if (is_array($subscription)) {
			$subscription = reset($subscription);
		}
		$billing = Formatter::formatBillingPeriod(
			$subscription->get_billing_period(),
			$subscription->get_billing_interval()
		);
		if (is_wp_error($billing)) {
			$this->logger->error($this->product->get_title() . ' product is incompatible with the gateway. When daily billing period is used, the interval must be greater than every 3 days.');
			return $billing;
		}
		$data['fields'] = [
			'name' => $this->product->get_title(),
			'description' => $this->product->get_description(),
			'amount' => Formatter::formatAmount(\WC_Subscriptions_Order::get_recurring_total($context)),
			SDK::INTERVAL_KEY => $billing[SDK::INTERVAL_KEY],
			SDK::FREQUENCY_KEY => $billing[SDK::FREQUENCY_KEY],
			SDK::BILLING_DAYS_KEY => $billing[SDK::BILLING_DAYS_KEY],
			'duration' => (int) wcs_estimate_periods_between($subscription->get_time('date_created'), $subscription->get_time('end'), $subscription->get_billing_period()),
			'add_ons' => [],
			'discounts' => [],
		];
		return $data;
	}
}