<?php

namespace CustomPaymentGateway\Actions;

use CustomPaymentGateway\Gateway\Config\GatewayConfig;
use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Plugin;

class ApiTestAction implements ActionInterface {
	public const AJAX_HANDLE = Plugin::ID . '_ajax_test_api';

	public static function register() {
		add_action('admin_init', [__CLASS__, 'registerScript']);
		add_action('wp_ajax_' . self::AJAX_HANDLE . '_handle', [__CLASS__, 'handleApiTest']);
	}

	public static function registerScript() {
		$url = wc_get_current_admin_url();
		if ($url !== admin_url('admin.php?page=' . Plugin::ID)) {
			return;
		}
		wp_enqueue_script(
			self::AJAX_HANDLE,
			plugins_url('/gateway/frontend/src/js/api_test.js'),
			['jquery'],
			'1.0.0',
			true
		);
		wp_localize_script(
			self::AJAX_HANDLE,
			self::AJAX_HANDLE . '_data',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(self::AJAX_HANDLE . '_nonce'),
				'action' => self::AJAX_HANDLE . '_handle',
			]
		);
	}

	public static function handleApiTest() {
		check_ajax_referer(self::AJAX_HANDLE . '_nonce', 'nonce');
		$connector = new SDK();
		$data = [
			'status' => 'error',
			'message' => __('API connection could not be established. Please provide a valid API key and url.', Plugin::ID),
		];
		if (!isset($_POST['api_key']) || !isset($_POST['api_url'])) {
			$gateway_options = GatewayConfig::getOptions();
		}
		if (!isset($_POST['api_key'])) {
			$connector->apiKey = $gateway_options[GatewayConfig::KEY_API_KEY];
		} else {
			$connector->apiKey = sanitize_text_field($_POST['api_key']);
		}
		if (!isset($_POST['api_url'])) {
			$connector->url = $gateway_options[GatewayConfig::KEY_API_URL];
		} else {
			$connector->url = sanitize_text_field($_POST['api_url'] ?: '');
		}

		$response = $connector->getUser();
		if ($response && $response['http_code'] === 200) {
			$data['status'] = 'success';
			$data['message'] = __('API connection successful.', Plugin::ID);
		}
		echo json_encode($data);
		wp_die();
	}
}