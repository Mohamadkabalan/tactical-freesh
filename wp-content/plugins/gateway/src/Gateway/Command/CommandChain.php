<?php

namespace CustomPaymentGateway\Gateway\Command;

use WC_Order;

class CommandChain implements CommandInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\Command\CommandInterface[] Then handlers to be chained
	 */
	private $commands;

	/**
	 * HandlerChain constructor.
	 *
	 * @param \CustomPaymentGateway\Gateway\Command\CommandInterface[] $commands The handlers to be chained
	 */
	public function __construct(array $commands) {
		$this->commands = $commands;
	}

	/**
	 * @inheritDoc
	 */
	public function execute(WC_Order $order) {
		foreach ($this->commands as $command) {
			$executed = $command->execute($order);
			if ($executed === false) {
				return false;
			}
		}
		return true;
	}
}