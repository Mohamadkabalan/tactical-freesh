<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class VoidClient
 */
class VoidClient extends AbstractClient {
	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->voidTransaction($data['transaction_id']);
	}
}
