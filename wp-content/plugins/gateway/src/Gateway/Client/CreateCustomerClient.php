<?php

namespace CustomPaymentGateway\Gateway\Client;

class CreateCustomerClient extends AbstractClient {

	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->createCustomerRecord($data['fields']);
	}
}