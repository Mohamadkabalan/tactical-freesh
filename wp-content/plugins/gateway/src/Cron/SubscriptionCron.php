<?php

namespace CustomPaymentGateway\Cron;

use CustomPaymentGateway\Gateway\Config\CcConfig;
use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Gateway\Config\GatewayConfig;
use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Helper\Formatter;
use CustomPaymentGateway\Log\Logger;
use CustomPaymentGateway\Plugin;
use WC_Subscriptions_Manager;

class SubscriptionCron implements CronInterface {
	public static function cronJob() {
		$connector = new SDK();
		$logger = new Logger();
		$options = GatewayConfig::getOptions();
		$connector->apiKey = $options[GatewayConfig::KEY_API_KEY];
		$connector->url = $options[GatewayConfig::KEY_API_URL];
		$subscriptions = wcs_get_subscriptions(['subscriptions_per_page' => -1]);
		/** @var \WC_Subscription $subscription */
		foreach ($subscriptions as $subscription) {
			if (!in_array($subscription->get_payment_method(), [CcConfig::METHOD_ID, ECheckConfig::METHOD_ID])) {
				continue;
			}
			$subscription_id = $subscription->get_meta(Plugin::ID . '_subscription_id');
			// If the subscription doesn't have an ID it probably belongs to a different GW
			if (empty($subscription_id)) {
				$subscription->add_order_note(__('Subscription ID for the corresponding subscription in the Gateway is missing.', Plugin::ID));
				continue;
			}
			$subscription_response = $connector->getSubscription($subscription_id);
			// If the connection couldn't be established log it
			if (!$subscription_response) {
				$logger->setDebug(true);
				$logger->info(__("Couldn't connect to gateway. The API key or the URL might be wrong.", Plugin::ID));
				continue;
			}
			// If the subscription doesn't have a status it doesn't exist
			if (!isset($subscription_response['result']['data']['status'])) {
				$subscription->add_order_note(__('Subscription not found in Gateway.', Plugin::ID));
				$subscription->payment_failed();
				continue;
			}
			// WC Sub Status can be: 'active', 'on-hold', 'pending-cancel', 'cancelled', 'expired', 'switched', 'pending', 'processing', 'completed', 'refunded', 'failed'
			// GW Sub Status can be: 'active','failing','failed','completed','paused','past_due','cancelled','stopped'
			$subscription_status = $subscription_response['result']['data']['status'];
			$skip_status_map = [
				'cancelled' => 'cancelled',
				'pending-cancel' => 'cancelled',
				'expired' => 'completed',
			];
			// Skip updating the subscription if it is cancelled, pending-cancel or expired
			if (isset($skip_status_map[$subscription->get_status()]) && ($skip_status_map[$subscription->get_status()] === $subscription_status)) {
				continue;
			}
			// Cancel the subscription if the GW status is failed
			if ($subscription_status === 'failed') {
				WC_Subscriptions_Manager::cancel_subscriptions_for_order($subscription);
				continue;
			}
			$transactions_response = $connector->searchTransaction([
				'subscription_id' => [
					'operator' => '=',
					'value' => $subscription_id,
				],
			]);
			// If there are no transactions there's nothing to do
			if ($transactions_response['result']['total_count'] === 0) {
				continue;
			}
			$last_transaction = $transactions_response['result']['data'][0];
			$last_transaction_id = $last_transaction['id'];
			// GW Transaction status can be 'unknown','declined','authorized','pending_settlement','settled','voided','refunded','returned','late_return','pending','partially_refunded'
			$last_transaction_successful = in_array(
				$last_transaction['status'],
				[
					'pending_settlement',
					'settled',
				],
				true
			);
			$wc_last_order = wc_get_order($subscription->get_last_order());
			// WC Transaction status can be 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'
			$wc_last_order_status = $wc_last_order->get_status();
			$next_bill_date = $subscription_response['result']['data']['next_bill_date'];
			// If this is a new subscription, a renewal order or a failing subscription try to resolve the payment ASAP
			if (
				in_array($wc_last_order_status, ['pending', 'on-hold', 'failed'], true) &&
				$last_transaction_id !== $wc_last_order->get_transaction_id() &&
				$last_transaction_successful
			) {
				$wc_last_order->payment_complete($last_transaction_id);
				$subscription->update_dates(['next_payment' => date('Y-m-d H:i:s', strtotime($next_bill_date))]);
			}

			// When it's time to pay and there are no unresolved orders, create a renewal order
			if (
				$subscription->get_time('next_payment') <= current_time('timestamp') &&
				!in_array($wc_last_order_status, ['pending', 'on-hold', 'failed', 'cancelled'], true) &&
				$last_transaction_id !== $wc_last_order->get_transaction_id()
			) {
				$renewal_order = wcs_create_renewal_order($subscription);
				if (is_wp_error($renewal_order)) {
					$logger->setDebug(true);
					$logger->error('subscription_id: ' . $subscription->get_meta('subscription_id') . '. Message: ' . $renewal_order->get_error_message());
					continue;
				}

				if (Formatter::formatAmount($renewal_order->get_total()) === 0) {
					$renewal_order->payment_complete();
					continue;
				} else {
					$renewal_order->set_payment_method(wc_get_payment_gateway_by_order($subscription));
					if (is_callable([$renewal_order, 'save'])) {
						$renewal_order->save();
					}
				}
				$wc_last_order = wc_get_order($subscription->get_last_order());
				if ($last_transaction_successful) {
					$wc_last_order->payment_complete($last_transaction_id);
					$subscription->update_dates(['next_payment' => date('Y-m-d H:i:s', strtotime($next_bill_date))]);
				} else {
					$wc_last_order->update_status('failed', __('Transaction for renewal order was unsuccessful', Plugin::ID));
				}
			}
		}
	}
}