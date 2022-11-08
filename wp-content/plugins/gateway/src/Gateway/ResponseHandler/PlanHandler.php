<?php

namespace CustomPaymentGateway\Gateway\ResponseHandler;

use CustomPaymentGateway\Plugin;
use WC_Order;
use WC_Product;

class PlanHandler implements HandlerInterface {

	/**
	 * The product that can be purchased as a subscription
	 *
	 * @var \WC_Product
	 */
	private $product;

	/**
	 * PlanBuilder constructor.
	 *
	 * @param \WC_Product $product
	 */
	public function __construct(WC_Product $product) {
		$this->product = $product;
	}

	/**
	 * @inheritDoc
	 */
	public function handle($context, array $response) {
		if ($response['status'] === 'success') {
			$this->product->update_meta_data(Plugin::ID . '_plan_id', $response['data']['id']);
			$this->product->save();
			return true;
		} else {
			return false;
		}
	}
}