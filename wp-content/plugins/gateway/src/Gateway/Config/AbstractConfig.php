<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Form\Checkbox;
use CustomPaymentGateway\Form\Password;
use CustomPaymentGateway\Form\Select;
use CustomPaymentGateway\Form\Text;
use CustomPaymentGateway\Form\Textarea;
use CustomPaymentGateway\Plugin;

abstract class AbstractConfig {
	public function registerSettings(): void {
		$option_id = static::getOptionId();
		register_setting(
			Plugin::ID,
			$option_id,
			[
				'type' => 'array',
				'default' => static::getDefaults(),
				'sanitize_callback' => [$this, 'sanitize'],
			]
		);
		add_settings_section(
			$option_id . '_section',
			$this->getSettingsTitle(),
			[$this, 'sectionHeader'],
			Plugin::ID
		);
		foreach ($this->getFormFields() as $key => $field) {
			add_settings_field(
				$option_id . '_' . $key,
				$field['title'],
				$field['callback'],
				Plugin::ID,
				$option_id . '_section',
				$field['args']
			);
		}
	}

	public static function getOptions() {
		return get_option(static::getOptionId(), static::getDefaults());
	}

	public static function updateOptions(array $options): bool {
		return update_option(static::getOptionId(), $options);
	}

	/**
	 * @param string $element_type 'text', 'textarea', 'password', 'checkbox', 'select', 'password'
	 * @param string $title
	 * @param string $option_key
	 * @param string $value
	 * @param string $description
	 * @param array  $args (optional) [
	 *                             'description' => string,
	 *                             'placeholder' => string,
	 *                             'options' => array
	 *                             ]
	 *
	 * @return array
	 */
	protected function createWpSettingsFormField(
		string $element_type,
		string $title,
		string $option_key,
		string $value,
		string $description = '',
		array $args = []
	): array {
		$id = static::getOptionId() . '[' . $option_key . ']';
		$field = [
			'title' => $title,
			'args' => $args,
		];
		$field['args']['label_for'] = $id;
		switch ($element_type) {
			case 'text':
				$field['callback'] = [new Text($id, $value, $description, $args), 'render'];
				break;
			case 'checkbox':
				$field['callback'] = [new Checkbox($id, $value, $description, $args), 'render'];
				break;
			case 'textarea':
				$field['callback'] = [new Textarea($id, $value, $description, $args), 'render'];
				break;
			case 'password':
				$field['callback'] = [new Password($id, $value, $description, $args), 'render'];
				break;
			case 'select':
				$field['callback'] = [new Select($id, $value, $description, $args), 'render'];
				break;
		}
		return $field;
	}

	abstract public function sanitize(array $options): array;

	abstract public static function getDefaults(): array;

	abstract public static function getOptionId(): string;

	abstract protected function getSettingsTitle(): string;

	abstract protected function getFormFields(): array;

	abstract public function sectionHeader(array $args): void;
}