<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Plugin;

final class ECheckConfig extends AbstractPaymentMethodConfig {
	public const METHOD_ID = Plugin::ID . '_ach';

	public static function getDefaults(): array {
		$fields = parent::getDefaults();
		$fields[self::KEY_TITLE] = __('eCheck Payment', Plugin::ID);
		return $fields;
	}

	protected function getSettingsTitle(): string {
		return __('eCheck Payment', Plugin::ID);
	}

	public function sectionHeader(array $args): void {
		echo '<p id="' . self::METHOD_ID . '">' . __('Configure the eCheck payment method.', Plugin::ID) . '</p>';
	}

	public function sanitize(array $options): array {
		return $options;
	}
}