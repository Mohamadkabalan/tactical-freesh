<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\RequestBuilder;

/**
 * Class BuilderComposite
 */
class BuilderComposite implements BuilderInterface {

	/**
	 * @var \CustomPaymentGateway\Gateway\RequestBuilder\BuilderInterface[]
	 */
	private $builders;

	/**
	 * @param \CustomPaymentGateway\Gateway\RequestBuilder\BuilderInterface[] $builders Request data builders
	 */
	public function __construct(
		array $builders
	) {
		$this->builders = $builders;
	}

	/**
	 * @inheritdoc
	 */
	public function build($context) {
		$result = [];
		foreach ($this->builders as $builder) {
			$result = $this->merge($result, $builder->build($context));
		}

		return $result;
	}

	/**
	 * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
	 *
	 * Merge function for builders
	 *
	 * @param array $result Merged request data
	 * @param array $request_data Request data built
	 *
	 * @return array
	 *
	 * phpcs:enable
	 */
	private function merge(array $result, array $request_data): array {
		return array_replace_recursive($result, $request_data);
	}
}
