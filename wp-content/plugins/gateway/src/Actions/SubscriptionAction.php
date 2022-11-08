<?php

namespace CustomPaymentGateway\Actions;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Plugin;
use DateTime;

/**
 * Class SubscriptionAction
 */
class SubscriptionAction implements ActionInterface {
	public static function register() {
		if (!class_exists('\WC_Subscriptions')) {
			return;
		}
		add_action('woocommerce_thankyou', [__CLASS__, 'addSubscriptionNotice'], 1);
		add_action(Plugin::ID . '_subscriptions_cron_hook', ['\CustomPaymentGateway\Cron\SubscriptionCron', 'cronjob']);
		if (!wp_next_scheduled(Plugin::ID . '_subscriptions_cron_hook')) {
			$cron_time = new DateTime('now', wp_timezone());
			$cron_time->setTime(27, 0);
			wp_schedule_event($cron_time->getTimestamp(), 'daily', Plugin::ID . '_subscriptions_cron_hook');
		}
		add_filter('woocommerce_my_account_my_orders_actions', [__CLASS__, 'filterMyAccountOrdersActions'], 10, 2);
	}

	public static function addSubscriptionNotice($order_id) {
		$subs = wcs_get_subscriptions_for_order($order_id);
		if (count($subs) > 0) {
			echo '<em>' . __('Subscription might take up to 24 hours to activate.', Plugin::ID) . '</em>';
		}
	}

	/**
	 * Unset pay action for an order if a more recent order exists
	 */
	public static function filterMyAccountOrdersActions($actions, $order) {
		$gw = wc_get_payment_gateway_by_order($order);
		if (!in_array($gw->id, [CcConfig::METHOD_ID, ECheckConfig::METHOD_ID])) {
			return $actions;
		}
		if (isset($actions['pay']) && wcs_order_contains_subscription($order, ['any'])) {
			$subscriptions = wcs_get_subscriptions_for_order(wcs_get_objects_property($order, 'id'), ['order_type' => 'any']);

			foreach ($subscriptions as $subscription) {
				if ($subscription->get_meta(Plugin::ID . '_subscription_id') && $subscription->get_status() === 'pending') {
					unset($actions['pay']);
					break;
				}
			}
		}

		return $actions;
	}

}