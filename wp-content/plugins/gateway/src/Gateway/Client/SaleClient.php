<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class SaleClient
 */
class SaleClient extends AbstractClient {
	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->processTransaction($data['fields']);
	}
}
