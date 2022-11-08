<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\Client;

use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;

/**
 * Class AbstractClient
 */
abstract class AbstractClient {

	/**
	 * @var \CustomPaymentGateway\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \CustomPaymentGateway\Gateway\SDK\SDK
	 */
	protected $connector;

	/**
	 * AbstractClient constructor.
	 */
	public function __construct(LoggerInterface $logger, SDK $connector) {
		$this->logger = $logger;
		$this->connector = $connector;
	}

	/**
	 * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
	 *
	 * Place the request and return the response
	 *
	 * @param int   $order_id The ID of the order
	 * @param array $data     The request data
	 *
	 * @return array
	 *
	 * phpcs:enable
	 */
	public function placeRequest(int $order_id, array $data): array {
		$this->logger->info(
			'gateway request [orderID:' . $order_id . ']: ' . json_encode(Formatter::maskSensitiveData($data))
		);
		$response = $this->process($data);
		$this->logger->info(
			'gateway response [orderID:' . $order_id . ']: ' . json_encode(Formatter::maskSensitiveData($response))
		);
		if (is_wp_error($response)) {
			wc_add_notice(
				__(
					'Gateway error. Please notify the store owner.',
					Plugin::ID
				),
				'error'
			);
			return $response;
		}
		$result = $response['result'];
		if ($result['status'] !== 'success') {
			wc_add_notice(
				__(
					'Gateway error. Please notify the store owner.',
					Plugin::ID
				),
				'error'
			);
		}
		return $result;
	}

	/**
	 * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
	 *
	 * Process http request
	 *
	 * @param array $data The request data
	 *
	 * @return array The response data
	 *
	 * phpcs:enable
	 */
	abstract protected function process(array $data): array;
}
