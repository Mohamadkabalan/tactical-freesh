<?php

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod;
use CustomPaymentGateway\Plugin;

class CreatePaymentMethodBuilder implements BuilderInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod
	 */
	private $method;

	/**
	 * CreatePaymentMethodBuilder constructor.
	 *
	 * @param \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod $method Payment method
	 */
	public function __construct(AbstractPaymentMethod $method) {
		$this->method = $method;
	}

	/**
	 * @inheritDoc
	 */
	public function build($context) {
		$data = [];
		$data['customer_id'] = get_user_meta(get_current_user_id(), Plugin::ID . '_customer_id', true);
		$data['payment_method_type'] = $this->method::PAYMENT_METHOD_TYPE;
		$data['fields'] = $this->method->getFormattedPaymentData();
		return $data;
	}
}