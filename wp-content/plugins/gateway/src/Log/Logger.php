<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Log;

use CustomPaymentGateway\Plugin;
use WC_Log_Levels;
use WC_Logger;

/**
 * Class Logger
 */
class Logger implements LoggerInterface {

	/**
	 * Debug flag
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * WC_Logger object
	 *
	 * @var \WC_Logger
	 */
	private $logger;

	/**
	 * Constructor of the Logger
	 */
	public function __construct() {
		$this->logger = new WC_Logger();
	}

	/**
	 * @inheritdoc
	 */
	public function setDebug(bool $debug): void {
		$this->debug = $debug;
	}

	/**
	 * @inheritdoc
	 */
	public function info(string $msg): void {
		if ($this->debug) {
			$this->logger->add(Plugin::ID, $msg, WC_Log_Levels::INFO);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function error(string $msg): void {
		if ($this->debug) {
			$this->logger->add(Plugin::ID, $msg, WC_Log_Levels::ERROR);
		}
	}
}
