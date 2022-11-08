<?php

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class UpdateSubscriptionClient
 */
class UpdateSubscriptionClient extends AbstractClient {

	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->updateSubscription($data['subscription_id'], $data['fields']);
	}
}