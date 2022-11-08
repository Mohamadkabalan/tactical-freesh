<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

/**
 * Interface BuilderInterface
 */
interface BuilderInterface {
	/**
	 * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification
	 *
	 * @param \WC_Order|\WC_Customer $context The context for building
	 *
	 * @return array|\WP_Error The request data or an error.
	 *
	 * phpcs:enable
	 */
	public function build($context);
}
