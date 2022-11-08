<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Plugin;

abstract class AbstractPaymentMethodConfig extends AbstractConfig {
	public const KEY_TITLE = 'title';
	public const KEY_DESCRIPTION = 'description';
	public const KEY_TRANSACTION_TYPE = 'transaction_type';
	public const KEY_SAVE_METHOD = 'save_method';

	/**
	 * @inheritDoc
	 */
	protected function getFormFields(): array {
		$options = static::getOptions();
		return [
			self::KEY_TITLE => $this->createWpSettingsFormField(
				'text',
				__('Title', Plugin::ID),
				self::KEY_TITLE,
				$options[self::KEY_TITLE],
				__(
					'Payment method title that the customer will see during checkout.',
					Plugin::ID
				)
			),
			self::KEY_DESCRIPTION => $this->createWpSettingsFormField(
				'textarea',
				__('Description', Plugin::ID),
				self::KEY_DESCRIPTION,
				$options[self::KEY_DESCRIPTION],
				__(
					'Payment method description that the customer will see during checkout.',
					Plugin::ID
				)
			),
			self::KEY_TRANSACTION_TYPE => $this->createWpSettingsFormField(
				'select',
				__('Transaction Type', Plugin::ID),
				self::KEY_TRANSACTION_TYPE,
				$options[self::KEY_TRANSACTION_TYPE],
				__('Select the transaction type for this method', Plugin::ID),
				[
					'options' => [
						'sale' => __('Sale(Authorize and Capture)', Plugin::ID),
						'authorize' => __('Authorize Only', Plugin::ID),
					],
				]
			),
			self::KEY_SAVE_METHOD => $this->createWpSettingsFormField(
				'checkbox',
				__('Enable saving of payment methods', Plugin::ID),
				self::KEY_SAVE_METHOD,
				$options[self::KEY_SAVE_METHOD],
				__(
					'If enabled, users will be able to pay with a saved payment method during checkout. Payment method details are saved on payment gateway servers, not on your store. This must be enabled for subscriptions.',
					Plugin::ID
				)
			),
		];
	}

	public static function getDefaults(): array {
		return [
			self::KEY_SAVE_METHOD => 'no',
			self::KEY_TITLE => __('Payment method title', Plugin::ID),
			self::KEY_DESCRIPTION => __(
				'Please remit payment to Store Name upon pickup or delivery.',
				Plugin::ID
			),
			self::KEY_TRANSACTION_TYPE => 'sale',
		];
	}

	public static function getOptionId(): string {
		return static::METHOD_ID;
	}

	public function getOption(string $key) {
		return get_option(self::getOptionId())[$key];
	}
}