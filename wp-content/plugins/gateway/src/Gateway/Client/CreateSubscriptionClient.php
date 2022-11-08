<?php

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class NewSubscriptionClient
 */
class CreateSubscriptionClient extends AbstractClient {

	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->createSubscription($data['fields']);
	}
}