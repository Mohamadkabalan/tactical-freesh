<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\Client;

/**
 * Class RefundClient
 */
class RefundClient extends AbstractClient {
	/**
	 * @inheritDoc
	 */
	protected function process(array $data): array {
		return $this->connector->refundTransaction($data['transaction_id'], $data['fields']);
	}
}
