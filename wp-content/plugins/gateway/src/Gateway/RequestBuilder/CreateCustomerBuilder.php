<?php

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Gateway\Config\AbstractPaymentMethodConfig;
use CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod;

class CreateCustomerBuilder implements BuilderInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod
	 */
	private $method;

	/**
	 * CreateCustomerBuilder constructor.
	 *
	 * @param \CustomPaymentGateway\Gateway\PaymentMethod\AbstractPaymentMethod $method Payment method configuration
	 */
	public function __construct(AbstractPaymentMethod $method) {
		$this->method = $method;
	}

	/**
	 * @inheritDoc
	 */
	public function build($context) {
		$data = [];
		$data['fields']['default_payment'] = [
			$this->method::PAYMENT_METHOD_TYPE => $this->method->getFormattedPaymentData(),
		];
		return $data;
	}
}