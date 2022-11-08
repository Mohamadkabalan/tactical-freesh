<?php

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class CreatePaymentMethodClient
 */
class CreatePaymentMethodClient extends AbstractClient {

	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->createCustomerPaymentToken($data['customer_id'], $data['payment_method_type'], $data['fields']);
	}
}