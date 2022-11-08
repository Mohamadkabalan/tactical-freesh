<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\Command;

use CustomPaymentGateway\Gateway\Client\AbstractClient;
use CustomPaymentGateway\Gateway\RequestBuilder\BuilderInterface;
use CustomPaymentGateway\Gateway\ResponseHandler\HandlerInterface;
use WC_Order;

/**
 * Class GatewayCommand
 */
class GatewayCommand implements CommandInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\RequestBuilder\BuilderInterface $builder
	 */
	private $builder;

	/**
	 * @var \CustomPaymentGateway\Gateway\Client\AbstractClient $client
	 */
	private $client;

	/**
	 * @var \CustomPaymentGateway\Gateway\ResponseHandler\HandlerInterface $handler
	 */
	private $handler;

	/**
	 * @var array $response
	 */
	private $response = null;

	/**
	 * GatewayCommand constructor.
	 *
	 * @param BuilderInterface      $builder
	 * @param AbstractClient        $client
	 * @param HandlerInterface|null $handler
	 */
	public function __construct(BuilderInterface $builder, AbstractClient $client, HandlerInterface $handler = null) {
		$this->builder = $builder;
		$this->client = $client;
		$this->handler = $handler;
	}

	/**
	 * @inheritDoc
	 */
	public function execute($context) {
		$context_id = $context->get_id();
		$request_data = $this->builder->build($context);
		if (is_wp_error($request_data)) {
			return $request_data;
		}
		$response = $this->client->placeRequest($context_id, $request_data);
		if (is_wp_error($response)) {
			return $response;
		}
		$this->response = $response;
		if ($this->handler) {
			return $this->handler->handle($context, $this->response);
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getResponse(): array {
		return $this->response;
	}
}
