<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\ResponseHandler;

interface HandlerInterface {
	/**
	 * @param \WC_Order|\WC_Customer $context  The context for handling
	 * @param mixed[]                $response Response body array
	 *
	 * @return \WP_Error|bool
	 */
	public function handle($context, array $response);
}
