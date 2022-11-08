<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\ResponseHandler;

/**
 * Class HandlerChain
 */
class HandlerChain implements HandlerInterface {
	/**
	 * @var \CustomPaymentGateway\Gateway\ResponseHandler\HandlerInterface[] Then handlers to be chained
	 */
	private $handlers;

	/**
	 * HandlerChain constructor.
	 *
	 * @param \CustomPaymentGateway\Gateway\ResponseHandler\HandlerInterface[] $handlers The handlers to be chained
	 */
	public function __construct(array $handlers) {
		$this->handlers = $handlers;
	}

	/**
	 * @inheritDoc
	 */
	public function handle($context, array $response) {
		foreach ($this->handlers as $handler) {
			$handled = $handler->handle($context, $response);
			if ($handled === false) {
				return false;
			}
		}
		return true;
	}
}
