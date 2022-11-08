<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

/**
 * Class AddressBuilder
 */
class AddressBuilder implements BuilderInterface {
	/**
	 * @var bool
	 */
	private $for_new_customer_record;

	/**
	 * AddressBuilder constructor.
	 *
	 * @param bool $default_prefix An additional flag for creating new customer records
	 */
	public function __construct(bool $for_new_customer_record = false) {
		$this->for_new_customer_record = $for_new_customer_record;
	}

	/**
	 * @inheritDoc
	 */
	public function build($context) {
		$data = [];
		$data['fields'] = [
			($this->for_new_customer_record ? 'default_' : '') . 'billing_address' => [
				'first_name' => $context->get_billing_first_name(),
				'last_name' => $context->get_billing_last_name(),
				'company' => $context->get_billing_company(),
				'address_line_1' => $context->get_billing_address_1(),
				'address_line_2' => $context->get_billing_address_2(),
				'city' => $context->get_billing_city(),
				'state' => $context->get_billing_state(),
				'postal_code' => $context->get_billing_postcode(),
				'country' => $context->get_billing_country(),
				'phone' => $context->get_billing_phone(),
				'email' => $context->get_billing_email(),
			],
			($this->for_new_customer_record ? 'default_' : '') . 'shipping_address' => [
				'first_name' => $context->get_shipping_first_name(),
				'last_name' => $context->get_shipping_last_name(),
				'company' => $context->get_shipping_company(),
				'address_line_1' => $context->get_shipping_address_1(),
				'address_line_2' => $context->get_shipping_address_2(),
				'city' => $context->get_shipping_city(),
				'state' => $context->get_shipping_state(),
				'postal_code' => $context->get_shipping_postcode(),
				'country' => $context->get_shipping_country(),
				'phone' => $context->get_billing_phone(),
				'email' => $context->get_billing_email(),
			],
		];
		return $data;
	}
}
