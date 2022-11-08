<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Helper;

use CustomPaymentGateway\Gateway\SDK\SDK;
use CustomPaymentGateway\Plugin;
use WP_Error;

class Formatter {
	/**
	 * Remove useless parts of the card like whitespaces
	 *
	 * @param string $cc_number Credit card number
	 */
	public static function formatCreditCardNumber(string $cc_number): string {
		return wc_clean(
			preg_replace(
				'/(?<=\d)\s+(?=\d)/',
				'',
				trim($cc_number)
			)
		);
	}

	/**
	 * Format the amount
	 *
	 * @param string|float $amount Amount to be formatted
	 */
	public static function formatAmount($amount): int {
		return intval(str_replace('.', '', number_format(floatval($amount), 2, '.', '')));
	}

	/**
	 * Format the expiry date
	 *
	 * @param string $date Date to be formatted
	 */
	public static function formatExpiryDate(string $date): string {
		$date = str_replace(
			' ',
			'',
			trim($date)
		);

		return $date;
	}

	/**
	 * Format the surcharge from the given values
	 *
	 * @param string $amount    The total amount, leave empty for flat rates, otherwise a percentage is calculated
	 * @param string $surcharge The surcharge amount
	 * @param string $type      'flat' or 'percentage'
	 */
	public static function formatSurcharge(string $amount, string $surcharge, string $type): float {
		$value = 0;
		switch ($type) {
			case 'flat':
				$value = floatval($surcharge);
				break;
			case 'percentage':
				$value = floatval($amount) / 100 * floatval($surcharge);
				break;
		}
		return $value;
	}

	/**
	 * @param string $period
	 * @param string $interval
	 *
	 * @return array|WP_Error
	 */
	public static function formatBillingPeriod(string $period, string $interval) {
		$converted = [];
		switch ($period) {
			case 'day':
				if ((int) $interval < 3) {
					return new WP_Error(500, __('This product cannot be purchased with the selected payment method.', Plugin::ID));
				}
				$converted[SDK::FREQUENCY_KEY] = 'daily';
				$converted[SDK::INTERVAL_KEY] = 1;
				$converted[SDK::BILLING_DAYS_KEY] = $interval;
				break;
			case 'week':
				$converted[SDK::FREQUENCY_KEY] = 'daily';
				$converted[SDK::INTERVAL_KEY] = (int) $interval;
				$converted[SDK::BILLING_DAYS_KEY] = '7';
				break;
			case 'month':
				$converted[SDK::FREQUENCY_KEY] = 'monthly';
				$converted[SDK::INTERVAL_KEY] = (int) $interval;
				$converted[SDK::BILLING_DAYS_KEY] = date('j');
				break;
			case 'year':
				$converted[SDK::FREQUENCY_KEY] = 'monthly';
				$converted[SDK::INTERVAL_KEY] = (int) $interval * 12;
				$converted[SDK::BILLING_DAYS_KEY] = date('j');
				break;
		}
		return $converted;
	}

	/**
	 * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingTraversableTypeHintSpecification, SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
	 *
	 * @param array $data The data to be masked for sensitive data.
	 *
	 * @return array
	 *
	 * phpcs:enable
	 */
	public static function maskSensitiveData(array $data): array {
		$masked_data = $data;
		if (isset($masked_data['fields'])) {
			$fields = &$masked_data['fields'];
			$payment_method = null;
			if (isset($fields['default_payment'])) {
				$payment_method = &$fields['default_payment'];
			} elseif (isset($fields['payment_method'])) {
				$payment_method = &$fields['payment_method'];
			}
			if ($payment_method) {
				if (isset($payment_method['card'])) {
					$card = &$payment_method['card'];
					$card['number'] = 'XXXX XXXX XXXX XXXX';
					$card['expiration_date'] = 'XX/XX';
					$card['cvc'] = 'XXX';
				}
				if (isset($payment_method['ach'])) {
					$ach = &$payment_method['ach'];
					$ach['routing_number'] = 'XXXX';
					$ach['account_number'] = 'XXXX';
					$ach['account_type'] = 'XXXX';
					$ach['sec_code'] = 'XXX';
				}
			}
			if (isset($fields['payment_method']['customer'])) {
				$fields['payment_method']['customer']['id'] = 'XXX';
			}
			if (isset($fields['customer'])) {
				$fields['customer']['id'] = 'XXX';
			}
		}
		if (isset($masked_data['result']['data'])) {
			if (isset($masked_data['result']['data']['customer'])) {
				$masked_data['result']['data']['customer']['id'] = 'XXX';
			}
		}
		return $masked_data;
	}
}
