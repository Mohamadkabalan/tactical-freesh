<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Plugin;

final class CcConfig extends AbstractPaymentMethodConfig {
	public const METHOD_ID = Plugin::ID . '_card';
	public const KEY_SURCHARGE = 'surcharge';
	public const KEY_SURCHARGE_TYPE = 'surcharge_type';
	public const KEY_SURCHARGE_TITLE = 'surcharge_title';

	public static function getDefaults(): array {
		$fields = parent::getDefaults();
		$fields[self::KEY_TITLE] = __('Credit Card Payment', Plugin::ID);
		return array_merge($fields, [
			self::KEY_SURCHARGE => '',
			self::KEY_SURCHARGE_TYPE => 'none',
			self::KEY_SURCHARGE_TITLE => 'Surcharge',
		]);
	}

	protected function getFormFields(): array {
		$options = self::getOptions();
		return array_merge(parent::getFormFields(), [
			self::KEY_SURCHARGE => $this->createWpSettingsFormField(
				'text',
				__('Surcharge', Plugin::ID),
				self::KEY_SURCHARGE,
				$options[self::KEY_SURCHARGE],
				__(
					'Surcharge amount, for explanation please read the documentation.',
					Plugin::ID
				),
				['placeholder' => __('e.g. 1.5', Plugin::ID)]
			),
			self::KEY_SURCHARGE_TYPE => $this->createWpSettingsFormField(
				'select',
				__('Surcharge type', Plugin::ID),
				self::KEY_SURCHARGE_TYPE,
				$options[self::KEY_SURCHARGE_TYPE],
				__('Type of the surcharge', Plugin::ID),
				[
					'options' => [
						'none' => __('None', Plugin::ID),
						'percentage' => __('Percentage', Plugin::ID),
						'flat' => __('Flat', Plugin::ID),
					],
				]
			),
			self::KEY_SURCHARGE_TITLE => $this->createWpSettingsFormField(
				'text',
				__('Surcharge title', Plugin::ID),
				self::KEY_SURCHARGE_TITLE,
				$options[self::KEY_SURCHARGE_TITLE],
				__('Title of the surcharge fee that appears on checkout', Plugin::ID),
				['placeholder' => __('e.g. Surcharge', Plugin::ID)]
			),
		]);
	}

	public function sectionHeader(array $args): void {
		echo '<p id="' . self::METHOD_ID . '">' . __('Configure the Credit Card payment method.', Plugin::ID) . '</p>';
	}

	protected function getSettingsTitle(): string {
		return __('Credit Card Payment', Plugin::ID);
	}

	public function sanitize(array $options): array {
		return $options;
	}
}