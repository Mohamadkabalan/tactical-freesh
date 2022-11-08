<?php

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class CreatePlanClient
 */
class CreatePlanClient extends AbstractClient {

	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->createPlan($data['fields']);
	}
}