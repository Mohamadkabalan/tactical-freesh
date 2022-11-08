<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Log;

/**
 * Interface LoggerInterface
 */
interface LoggerInterface {
	/**
	 * Set the debug flag
	 *
	 * @param bool $debug State of the debug
	 */
	public function setDebug(bool $debug): void;

	/**
	 * Perform an info log
	 *
	 * @param string $msg The message of the action
	 */
	public function info(string $msg): void;

	/**
	 * Perform an error log
	 *
	 * @param string $msg The message of the action
	 */
	public function error(string $msg): void;
}
