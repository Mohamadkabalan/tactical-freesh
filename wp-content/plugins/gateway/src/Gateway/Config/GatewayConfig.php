<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Actions\ApiTestAction;
use CustomPaymentGateway\Plugin;
use WC_Log_Handler_File;

final class GatewayConfig extends AbstractConfig {
	public const KEY_API_KEY = 'api_key';
	public const KEY_API_URL = 'api_url';
	public const KEY_DEBUG = 'debug';

	public static function getOptionId(): string {
		return Plugin::ID;
	}

	public static function getDefaults(): array {
		return [
			self::KEY_API_KEY => '',
			self::KEY_API_URL => '',
			self::KEY_DEBUG => 'no',
		];
	}

	protected function getSettingsTitle(): string {
		return __('Gateway', Plugin::ID);
	}

	protected function getFormFields(): array {
		$options = self::getOptions();
		return [
			self::KEY_API_KEY => $this->createWpSettingsFormField(
				'password',
				__('API Key', Plugin::ID),
				self::KEY_API_KEY,
				$options[self::KEY_API_KEY],
				__(
					'Please enter your API Key; this is needed in order to take payment.',
					Plugin::ID
				),
				['placeholder' => __('Add new API key', Plugin::ID)]
			),
			self::KEY_API_URL => $this->createWpSettingsFormField(
				'text',
				__('URL', Plugin::ID),
				self::KEY_API_URL,
				$options[self::KEY_API_URL],
				__('URL of the payment gateway without trailing slashes and path components. (e.g. no "/" or "/api" at the end)', Plugin::ID),
				['placeholder' => 'https://www.example.com']
			),
			self::KEY_DEBUG => $this->createWpSettingsFormField(
				'checkbox',
				__('Debug log', Plugin::ID),
				self::KEY_DEBUG,
				$options[self::KEY_DEBUG],
				sprintf(
				// translators: %s: payment method id
					__('Log requests, inside %s', Plugin::ID),
					'"' . WC_Log_Handler_File::get_log_file_path(Plugin::ID) . '"'
				)
			),
		];
	}

	public function sectionHeader(array $args): void {
		echo '<p>' . __('General settings.', Plugin::ID) . '</p>';
		echo '<button type="button" class="button button-primary" id="' . ApiTestAction::AJAX_HANDLE . '_button">' . __('Test API connection', Plugin::ID) . '</button>';
	}

	public function sanitize(array $options): array {
		$url = &$options[self::KEY_API_URL];
		if (substr($url, -1) === '/') {
			$url = substr($url, 0, strlen($url) - 1);
		}
		if (substr($url, -4) === '/api') {
			$url = substr($url, 0, strlen($url) - 4);
		}
		return $options;
	}
}