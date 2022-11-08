<?php

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WC_Product;
use WC_Subscriptions_Product;
use WP_Error;

/**
 * Class SubscriptionBuilder
 */
class SubscriptionBuilder implements BuilderInterface {

	/**
	 * The product that can be purchased as a subscription
	 *
	 * @var \WC_Product
	 */
	private $product;

	/**
	 * The token for the payment method
	 *
	 * @var string
	 */
	private $token;

	/**
	 * @var string
	 */
	private $payment_method_type;

	/**
	 * @var bool
	 */
	private $update;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * PlanBuilder constructor.
	 *
	 * @param string      $token
	 * @param string      $payment_method_type
	 * @param \WC_Product $product
	 * @param bool        $update
	 */
	public function __construct(LoggerInterface $logger, string $token, string $payment_method_type, WC_Product $product, bool $update = false) {
		$this->token = $token;
		$this->payment_method_type = $payment_method_type;
		$this->product = $product;
		$this->update = $update;
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
		$data = [
			'fields' => [],
		];
		$subscription = wcs_get_subscriptions_for_order($context);
		$subscription = reset($subscription);
		if ($this->update) {
			$data['subscription_id'] = $context->get_meta(Plugin::ID . '_subscription_id');
		} else {
			$data['fields']['plan_id'] = $this->product->get_meta(Plugin::ID . '_plan_id');
			$data['fields']['next_bill_date'] = date('Y-m-d', strtotime(WC_Subscriptions_Product::get_trial_expiration_date($this->product)) ?: current_time('timestamp'));
		}
		$data['fields']['customer'] = [
			'id' => get_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', true),
			'payment_method_id' => $this->token,
			'payment_method_type' => $this->payment_method_type,
		];
		return $data;
	}
}