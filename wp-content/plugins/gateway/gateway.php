<?php
/**
 * Plugin Name: GW for WooCommerce
 * Description: WooCommerce Plugin for accepting payment through GW.
 * Version: 2.3.3
 * Author: GW
 * Contributors: GW
 * Requires PHP: 7.1
 * Tested up to: 5.3.2
 * WC requires at least: 3.0.0
 * WC tested up to: 5.0.0.
 *
 * Text Domain: custom_payment_gateway
 * Domain Path: /l10n/
 */

use CustomPaymentGateway\Install;
use CustomPaymentGateway\Plugin;

defined('ABSPATH') || exit;

require 'autoload.php';

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	register_activation_hook(__FILE__, 'custom_payment_gateway_activate');
	register_deactivation_hook(__FILE__, 'custom_payment_gateway_deactivate');
	Plugin::init();
}

function custom_payment_gateway_activate() {
	$updates = Install::getUpdates();
	// If there are no updates, begin at 0
	if (empty($updates)) {
		update_option(Install::DB_VERSION_KEY, 0);
		return;
	}
	update_option(Install::DB_VERSION_KEY, array_keys($updates)[count($updates) - 1]);
}

function custom_payment_gateway_deactivate() {
	delete_option(Install::DB_VERSION_KEY);
}

