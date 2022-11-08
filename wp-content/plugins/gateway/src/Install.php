<?php

namespace CustomPaymentGateway;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Gateway\Config\GatewayConfig;

class Install {
	public const DB_VERSION_KEY = Plugin::ID . '_db_version';

	public static function runUpdates() {
		$updates = self::getUpdates();
		foreach ($updates as $key => $update) {
			call_user_func([__CLASS__, $update]);
			update_option(self::DB_VERSION_KEY, $key);
		}
	}

	public static function getUpdates(): array {
		$updates = [];
		foreach (preg_grep('/_\\d+$/', get_class_methods(__CLASS__)) as $update) {
			$updates[substr($update, strrpos($update, '_') + 1)] = $update;
		}
		$db_version = get_option(self::DB_VERSION_KEY);
		// When someone runs the updates via the button and the db_version doesn't exist in the DB yet, that means
		// we want to run all available updates.
		if ($db_version === false) {
			add_option(self::DB_VERSION_KEY, 0);
			$db_version = 0;
		}
		$updates = array_filter($updates, function ($key) use ($db_version) {
			return $key > $db_version;
		}, ARRAY_FILTER_USE_KEY);
		ksort($updates);
		return $updates;
	}

	/**
	 * Migrate settings.
	 */
	public static function update_1() {
		$card_options = get_option('woocommerce_paymentgateway_settings');
		$ach_options = get_option('woocommerce_paymentgateway_echeck_settings');
		$gateway_options = [];
		$gateway_options[GatewayConfig::KEY_API_KEY] = $card_options['api_key'] ?: '';
		$gateway_options[GatewayConfig::KEY_API_URL] = $card_options['url'] ?: '';
		$gateway_options[GatewayConfig::KEY_DEBUG] = $card_options['debug'] ?: 'no';
		$new_card_options = [];
		$card_defaults = CcConfig::getDefaults();
		$new_card_options[CcConfig::KEY_TITLE] = $card_options['title'] ?: $card_defaults[CcConfig::KEY_TITLE];
		$new_card_options[CcConfig::KEY_DESCRIPTION] = $card_options['description'] ?: $card_defaults[CcConfig::KEY_DESCRIPTION];
		$new_card_options[CcConfig::KEY_TRANSACTION_TYPE] = $card_options['transactiontype'] ?: $card_defaults[CcConfig::KEY_TRANSACTION_TYPE];
		$new_card_options[CcConfig::KEY_SAVE_METHOD] = $card_options['save_cards'] ?: $card_defaults[CcConfig::KEY_SAVE_METHOD];
		$new_card_options[CcConfig::KEY_SURCHARGE] = $card_options['surcharge'] ?: $card_defaults[CcConfig::KEY_SURCHARGE];
		$new_card_options[CcConfig::KEY_SURCHARGE_TITLE] = $card_options['surcharge_title'] ?: $card_defaults[CcConfig::KEY_SURCHARGE_TITLE];
		$new_card_options[CcConfig::KEY_SURCHARGE_TYPE] = $card_options['surcharge_type'] ?: $card_defaults[CcConfig::KEY_SURCHARGE_TYPE];
		$new_ach_options = [];
		$ach_defaults = ECheckConfig::getDefaults();
		$new_ach_options[ECheckConfig::KEY_TITLE] = $ach_options['title'] ?: $ach_defaults[ECheckConfig::KEY_TITLE];
		$new_ach_options[ECheckConfig::KEY_DESCRIPTION] = $ach_options['description'] ?: $ach_defaults[ECheckConfig::KEY_DESCRIPTION];
		$new_ach_options[ECheckConfig::KEY_TRANSACTION_TYPE] = $ach_options['transactiontype'] ?: $ach_defaults[ECheckConfig::KEY_TRANSACTION_TYPE];
		$new_ach_options[ECheckConfig::KEY_SAVE_METHOD] = $ach_options['save_cards'] ?: $ach_defaults[ECheckConfig::KEY_SAVE_METHOD];
		update_option('woocommerce_' . CcConfig::getOptionId() . '_settings', ['enabled' => $card_options['enabled'] ?: 'no']);
		update_option('woocommerce_' . ECheckConfig::getOptionId() . '_settings', ['enabled' => $ach_options['enabled'] ?: 'no']);
		GatewayConfig::updateOptions($gateway_options);
		CcConfig::updateOptions($new_card_options);
		ECheckConfig::updateOptions($new_ach_options);
	}

	/**
	 * Namespace user metadata.
	 */
	public static function update_2() {
		$users = get_users(['meta_key' => 'customer_id']);
		// Nothing to do if no users have the `customer_id` meta field.
		if (empty($users)) return;
		foreach ($users as $user) {
			$customer_id = get_user_meta($user->ID, 'customer_id', true);
			if ($customer_id) {
				update_user_meta($user->ID, Plugin::ID . '_customer_id', $customer_id);
				delete_user_meta($user->ID, 'customer_id');
			}
		}
	}

	/**
	 * Namespace subscription metadata.
	 */
	public static function update_3() {
		// Nothing to do if subscriptions is not enabled.
		if (!class_exists('\WC_Subscriptions')) return;
		$subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
		// Nothing to do if there are no subscriptions.
		if (empty($subscriptions)) return;
		// Update subscription_id meta field for available subscriptions.
		foreach ($subscriptions as $subscription) {
			$subscription_id = $subscription->get_meta('subscription_id');
			if ($subscription_id) {
				$subscription->update_meta_data(Plugin::ID . '_subscription_id', $subscription_id);
				$subscription->delete_meta_data('subscription_id');
				if (is_callable([$subscription, 'save'])) {
					$subscription->save();
				}
			}
		}
	}

	/**
	 * Namespace subscription product metadata.
	 */
	public static function update_4() {
		// Nothing to do if subscriptions is not enabled.
		if (!class_exists('\WC_Subscriptions')) return;
		$products = wc_get_products([
			'limit' => -1,
			'type' => 'subscription',
		]);
		// Nothing to do if there are no subscription products.
		if (empty($products)) return;
		// Update the plan_id meta field for the available products.
		foreach ($products as $product) {
			$plan_id = $product->get_meta('plan_id');
			$product->update_meta_data(Plugin::ID . '_plan_id', $plan_id);
			$product->delete_meta_data('plan_id');
			if (is_callable([$product, 'save'])) {
				$product->save();
			}
		}
	}
}