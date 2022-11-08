<?php

namespace CustomPaymentGateway\Actions;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Plugin;

class RedirectAction implements ActionInterface {

	public static function register() {
		add_action('admin_init', [__CLASS__, 'redirect']);
	}

	public static function redirect() {
		$url = wc_get_current_admin_url();
		if ($url === admin_url('admin.php?page=wc-settings&tab=checkout&section=' . CcConfig::METHOD_ID)) {
			wp_redirect(admin_url('admin.php?page=' . Plugin::ID), 301);
			exit();
		}
		if ($url === admin_url('admin.php?page=wc-settings&tab=checkout&section=' . ECheckConfig::METHOD_ID)) {
			wp_redirect(admin_url('admin.php?page=' . Plugin::ID), 301);
			exit();
		}
	}
}