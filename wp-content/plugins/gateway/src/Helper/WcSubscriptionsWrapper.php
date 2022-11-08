<?php


namespace CustomPaymentGateway\Helper;


use WC_Subscriptions_Cart;
use WC_Subscriptions_Product;

class WcSubscriptionsWrapper {
	public static function orderContainsSubscription(int $order_id) {
		if (!class_exists('\WC_Subscription')) {
			return FALSE;
		}
		return wcs_order_contains_subscription($order_id);
	}

	public static function orderIsSubscription(int $order_id) {
		if (!class_exists('\WC_Subscription')) {
			return FALSE;
		}
		return wcs_is_subscription($order_id);
	}

	public static function isChangePaymentMethodRequest() {
		return isset($_GET['change_payment_method']);
	}

	public static function isMixedCheckout($cart): bool {
		if (!WC_Subscriptions_Cart::cart_contains_subscription() && !wcs_cart_contains_renewal()) {
			return FALSE;
		}
		foreach ($cart->cart_contents as $item) {
			// If two different subscription products are in the cart
			// or a non-subscription product is found in the cart containing subscriptions
			// ( maybe because of carts merge while logging in )
			if (!WC_Subscriptions_Product::is_subscription($item['data']) ||
				WC_Subscriptions_Cart::cart_contains_other_subscription_products(wcs_get_canonical_product_id($item['data']))) {
				return TRUE;
			}
		}
		return FALSE;
	}
}