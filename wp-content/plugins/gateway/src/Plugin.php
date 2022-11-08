<?php

namespace CustomPaymentGateway;

use CustomPaymentGateway\Actions\ApiTestAction;
use CustomPaymentGateway\Actions\PaymentMethodAction;
use CustomPaymentGateway\Actions\RedirectAction;
use CustomPaymentGateway\Actions\SubscriptionAction;
use CustomPaymentGateway\Actions\SurchargeAction;
use CustomPaymentGateway\Gateway\Config\Settings;
use CustomPaymentGateway\Gateway\PaymentMethod\CcMethod;
use CustomPaymentGateway\Gateway\PaymentMethod\ECheckMethod;

class Plugin {
	public const ID = 'custom_payment_gateway';

	public static function init() {
		RedirectAction::register();
		add_action('plugins_loaded', [__CLASS__, 'pluginsLoadedHooks']);
	}

	public static function pluginsLoadedHooks() {
		load_plugin_textdomain(
			self::ID,
			FALSE,
			plugins_url('/gateway/l10n/')
		);

		add_filter('woocommerce_payment_gateways', [__CLASS__, 'addGateway']);
		add_action('admin_init', [Settings::class, 'init']);
		add_action('admin_menu', [Settings::class, 'registerMenu'], 100);
		SubscriptionAction::register();
		SurchargeAction::register();
		PaymentMethodAction::register();
		ApiTestAction::register();
	}

	/**
	 * Add gateway implementations
	 *
	 * @param array $methods List of available methods
	 *
	 * @return array
	 */
	public static function addGateway($methods) {
		$methods[] = CcMethod::class;
		$methods[] = ECheckMethod::class;
		return $methods;
	}
}