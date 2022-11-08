<?php

namespace CustomPaymentGateway\Gateway\Command;

use WP_Error;

/**
 * Interface CommandInterface
 */
interface CommandInterface {
	/**
	 * Execute the command
	 *
	 * @param \WC_Order|\WC_Customer $context The current order
	 *
	 * @return bool|WP_Error
	 */
	public function execute($context);

	/**
	 * Get response after execution, returns null if called before.
	 */
	public function getResponse(): ?array;
}