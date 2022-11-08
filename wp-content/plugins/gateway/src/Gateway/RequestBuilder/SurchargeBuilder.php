<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

use CustomPaymentGateway\Gateway\PaymentMethod\CcMethod;
use CustomPaymentGateway\Log\LoggerInterface;
use CustomPaymentGateway\Plugin;
use WP_Error;

/**
 * Class SurchargeBuilder
 */
class SurchargeBuilder implements BuilderInterface {
	/**
	 * @var \CustomPaymentGateway\Gateway\PaymentMethod\CcMethod $method
	 */
	private $method;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * SurchargeBuilder constructor.
	 */
	public function __construct(LoggerInterface $logger, CcMethod $method) {
		$this->logger = $logger;
		$this->method = $method;
	}

	/**
	 * @inheritDoc
	 */
	public function build($context) {
		if (!($context instanceof \WC_Order)) {
			$this->logger->error('Not in order context for this gateway command.');
			return new WP_Error('500', __('Gateway error. Please contact the store owner.', Plugin::ID));
		}
		$data = [];
		$fees = $context->get_fees();
		/**
		 * @var \WC_Order_Item_Fee $fee
		 */
		foreach ($fees as $fee) {
			if ($fee->get_name() === $this->method->config->getOption($this->method->config::KEY_SURCHARGE_TITLE)) {
				$data['fields'] = [
					'surcharge' => intval(round($fee->get_amount() * 100)),
				];
			}
		}
		return $data;
	}
}
