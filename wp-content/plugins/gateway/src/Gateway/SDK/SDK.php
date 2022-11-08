<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\SDK;

/**
 * PaymentGateway Connector for the gateway
 *
 * @category Class
 * @package  Custom
 * @author   None <none@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.none.com/
 */
final class SDK {

	public const INTERVAL_KEY = 'billing_cycle_interval';
	public const FREQUENCY_KEY = 'billing_frequency';
	public const BILLING_DAYS_KEY = 'billing_days';
	// Subscriptions
	public const SUB_STATUS_CANCELLED = 'cancelled';
	public const SUB_STATUS_ACTIVE = 'active';
	public const SUB_STATUS_COMPLETED = 'completed';
	public const SUB_STATUS_FAILING = 'failing';
	public const SUB_STATUS_FAILED = 'failed';
	public const SUB_STATUS_PAUSED = 'paused';
	public const SUB_STATUS_PAST_DUE = 'past_due';
	public const SUB_STATUS_STOPPED = 'stopped';

	/**
	 * The url of the environment
	 *
	 * @var string $url
	 */
	public $url;

	/**
	 * The merchant API key
	 *
	 * @var string $apiKey
	 */
	public $apiKey;

	/**
	 * Check API status
	 *
	 * @return array
	 */
	public function statusCheck() {
		return $this->_request(['url' => '/fphc']);
	}

	/**
	 * Check API status
	 *
	 * @return array
	 */
	public function getUser() {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'url' => '/user',
				'method' => 'GET',
			]
		);
	}

	/**
	 * Creates a new customer
	 *
	 * @param array $customer Definition of customer
	 *
	 * @return array
	 */
	public function createCustomerRecord(array $customer) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer',
				'fields' => $customer,
			]
		);
	}

	/**
	 * Get a specific customer
	 *
	 * @param string $customerID ID of the customer
	 *
	 * @return array
	 */
	public function getCustomer(string $customerID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/vault/' . $customerID,
			]
		);
	}

	/**
	 * Update the customer
	 *
	 * @param array $customer Definition of customer
	 *
	 * @return array
	 */
	public function updateCustomer(array $customer) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer/' . $customer['id'],
				'fields' => $customer,
			]
		);
	}

	/**
	 * Delete the customer
	 *
	 * @param string $customerID ID of the customer
	 *
	 * @return array
	 */
	public function deleteCustomer(string $customerID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/vault/' . $customerID,
			]
		);
	}

	/**
	 * Create address token for customer
	 *
	 * @param string $customerID ID of the customer
	 * @param array  $address    Definition of address
	 *
	 * @return array
	 */
	public function createCustomerAddressToken(string $customerID, array $address) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer/' . $customerID . '/address',
				'fields' => $address,
			]
		);
	}

	/**
	 * Get customer's address
	 *
	 * @param string $customerID ID of the customer
	 * @param array  $addressID  ID of the address
	 *
	 * @return array
	 */
	public function getCustomerAddress(string $customerID, array $addressID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/vault/customer/' . $customerID . '/address/' . $addressID,
			]
		);
	}

	/**
	 * Update customer address
	 *
	 * @param string $customerID ID of the customer
	 * @param array  $addressID  ID of the address
	 * @param array  $address    Definition of address
	 *
	 * @return array
	 */
	public function updateCustomerAddress(string $customerID, array $addressID, array $address) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer/' . $customerID . '/address/' . $addressID,
				'fields' => $address,
			]
		);
	}

	/**
	 * Delete the address token of the customer
	 *
	 * @param string $customerID ID of the customer
	 * @param array  $addressID  ID of the address
	 *
	 * @return array
	 */
	public function deleteCustomerAddress(string $customerID, array $addressID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/vault/customer/' . $customerID . '/address/' . $addressID,
			]
		);
	}

	/**
	 * Create payment token for customer
	 *
	 * @param string $customerID  ID of the customer
	 * @param string $paymentType Type of the payment ('card' or 'ach')
	 * @param array  $paymentData Data of the payment token
	 *
	 * @return array
	 */
	public function createCustomerPaymentToken(
		string $customerID,
		string $paymentType,
		array $paymentData
	) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer/' . $customerID . '/' . $paymentType,
				'fields' => $paymentData,
			]
		);
	}

	/**
	 * Update customer payment
	 *
	 * @param string $customerID        ID of the customer
	 * @param string $paymentType       Type of the payment
	 * @param string $payment_method_id ID of the payment type
	 * @param array  $data              Data of the payment token
	 *
	 * @return array
	 */
	public function updateCustomerPayment(
		string $customerID,
		string $paymentType,
		string $payment_method_id,
		array $data
	) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/vault/customer/' . $customerID . '/' . $paymentType . '/' . $payment_method_id,
				'fields' => $data,
			]
		);
	}

	/**
	 * Delete customer's payment
	 *
	 * @param string $customerID        ID of the customer
	 * @param string $paymentType       Type of the payment
	 * @param string $payment_method_id ID of the payment type
	 *
	 * @return array
	 */
	public function deleteCustomerPayment(string $customerID, string $paymentType, string $payment_method_id) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/vault/customer/' . $customerID . '/' . $paymentType . '/' . $payment_method_id,
			]
		);
	}

	/**
	 * Process transaction
	 *
	 * @param array $transaction Transaction details
	 *
	 * @return array
	 */
	public function processTransaction(array $transaction) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/transaction',
				'fields' => $transaction,
			]
		);
	}

	/**
	 * Get transaction
	 *
	 * @param string $transactionID ID of the transaction
	 *
	 * @return array
	 */
	public function getTransaction(string $transactionID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/transaction/' . $transactionID,
			]
		);
	}

	/**
	 * Search for transactions
	 *
	 * @param array $fields The parameters for searching
	 *
	 * @return array
	 */
	public function searchTransaction(array $fields) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/transaction/search',
				'fields' => $fields,
			]
		);
	}

	/**
	 * Capture of the transaction
	 *
	 * @param string $transactionID ID of the transaction
	 * @param array  $capture       Capture array
	 *
	 * @return array
	 */
	public function captureTransaction(string $transactionID, array $capture) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/transaction/' . $transactionID . '/capture',
				'fields' => $capture,
			]
		);
	}

	/**
	 * Void of the transaction
	 *
	 * @param string $transactionID ID of the transaction
	 *
	 * @return array
	 */
	public function voidTransaction(string $transactionID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/transaction/' . $transactionID . '/void',
			]
		);
	}

	/**
	 * Refund of the transaction
	 *
	 * @param string $transactionID ID of the transaction
	 * @param array  $refund        Refund array
	 *
	 * @return array
	 */
	public function refundTransaction(string $transactionID, array $refund) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/transaction/' . $transactionID . '/refund',
				'fields' => $refund,
			]
		);
	}

	/**
	 * Create AddOn
	 *
	 * @param array $addOn AddOn array
	 *
	 * @return array
	 */
	public function createAddOn(array $addOn) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/addon',
				'fields' => $addOn,
			]
		);
	}

	/**
	 * Get AddOn
	 *
	 * @param string $addOnID ID of the AddOn
	 *
	 * @return array
	 */
	public function getAddOn(string $addOnID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/addon/' . $addOnID,
			]
		);
	}

	/**
	 * Get all AddOns
	 *
	 * @return array
	 */
	public function getAllAddOns() {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/addons',
			]
		);
	}

	/**
	 * Delete AddOn
	 *
	 * @param string $addOnID ID of the AddOn
	 *
	 * @return array
	 */
	public function deleteAddOn(string $addOnID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/recurring/addon/' . $addOnID,
			]
		);
	}

	/**
	 * Create discount
	 *
	 * @param array $discount Discount array
	 *
	 * @return array
	 */
	public function createDiscount(array $discount) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/discount',
				'fields' => $discount,
			]
		);
	}

	/**
	 * Get Discount
	 *
	 * @param string $discountID ID of discount
	 *
	 * @return array
	 */
	public function getDiscount(string $discountID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/discount/' . $discountID,
			]
		);
	}

	/**
	 * Get all discount
	 *
	 * @return array
	 */
	public function getAllDiscounts() {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/discounts',
			]
		);
	}

	/**
	 * Delete discount
	 *
	 * @param string $discountID ID of discount
	 *
	 * @return array
	 */
	public function deleteDiscount(string $discountID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/recurring/discount/' . $discountID,
			]
		);
	}

	/**
	 * Create plan
	 *
	 * @param array $plan Plan array
	 *
	 * @return array
	 */
	public function createPlan(array $plan) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/plan',
				'fields' => $plan,
			]
		);
	}

	/**
	 * Update plan
	 *
	 * @param string $planID ID of the plan
	 * @param array  $plan   Plan array
	 *
	 * @return array
	 */
	public function updatePlan(string $planID, array $plan) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/plan/' . $planID,
				'fields' => $plan,
			]
		);
	}

	/**
	 * Get plan
	 *
	 * @param string $planID ID of the plan
	 *
	 * @return array
	 */
	public function getPlan(string $planID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/plan/' . $planID,
			]
		);
	}

	/**
	 * Get all plans
	 *
	 * @return array
	 */
	public function getAllPlans() {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/plans',
			]
		);
	}

	/**
	 * Delete plan
	 *
	 * @param string $planID ID of the plan
	 *
	 * @return array
	 */
	public function deletePlan(string $planID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/recurring/plan/' . $planID,
			]
		);
	}

	/**
	 * Create subscription
	 *
	 * @param array $subscription Subscription array
	 *
	 * @return array
	 */
	public function createSubscription(array $subscription) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/subscription',
				'fields' => $subscription,
			]
		);
	}

	/**
	 * Update subscription
	 *
	 * @param string $subscriptionID ID of subscription
	 * @param array  $subscription   Subscription array
	 *
	 * @return array
	 */
	public function updateSubscription(string $subscriptionID, array $subscription) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'url' => '/recurring/subscription/' . $subscriptionID,
				'fields' => $subscription,
			]
		);
	}

	/**
	 * Get subscription
	 *
	 * @param string $subscriptionID ID of subscription
	 *
	 * @return array
	 */
	public function getSubscription(string $subscriptionID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/subscription/' . $subscriptionID,
			]
		);
	}

	/**
	 * Delete subscription
	 *
	 * @param string $subscriptionID ID of subscription
	 *
	 * @return array
	 */
	public function deleteSubscription(string $subscriptionID) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'DELETE',
				'url' => '/recurring/subscription/' . $subscriptionID,
			]
		);
	}

	/**
	 * @param string $subscriptionID ID of subscription
	 * @param string $action         The status to update to
	 *                               Can be 'active','failing','failed','completed','paused','past_due','cancelled','stopped'
	 *
	 * @return array|bool|null
	 */
	public function updateSubscriptionStatus(string $subscriptionID, string $action) {
		return $this->_request(
			[
				'apiKey' => $this->apiKey,
				'method' => 'GET',
				'url' => '/recurring/subscription/' . $subscriptionID . '/status/' . $action,
			]
		);
	}

	/**
	 * Lookup for the bin to decide is it surchargeable or not
	 *
	 * @param string $cc_number The number of the credit card to decide
	 *
	 * @return bool|array
	 */
	function binLookup(string $cc_number) {
		return $this->_request(
			[
				'url' => '/lookup/bin',
				'apiKey' => $this->apiKey,
				'method' => 'POST',
				'fields' => [
					'type' => 'tokenizer',
					'type_id' => $this->genUUID(),
					'bin' => substr($cc_number, 0, 6),
				],
			]
		);
	}

	/**
	 * Main request handler
	 *
	 * @param array $options Set of fields for HTTP request
	 *
	 * @return bool|array
	 */
	private function _request(array $options) {
		$baseUrl = $this->url . '/api';

		$ch = curl_init();
		$header = ['Content-Type: application/json'];
		if (array_key_exists('apiKey', $options)) {
			array_push($header, 'Authorization: ' . $options['apiKey']);
		}
		$curlConfig = [
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_URL => $baseUrl . $options['url'],
			CURLOPT_RETURNTRANSFER => true,
		];
		if (array_key_exists('method', $options)) {
			if (strtolower($options['method']) == 'post') {
				$curlConfig[CURLOPT_POST] = true;
			}
			if (strtolower($options['method']) == 'delete') {
				$curlConfig[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			}
		}
		if (array_key_exists('fields', $options) && count($options['fields']) > 0) {
			$curlConfig[CURLOPT_POSTFIELDS] = json_encode($options['fields']);
		}
		curl_setopt_array($ch, $curlConfig);
		$result = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if (!$result) {
			return $result;
		}

		return [
			'http_code' => $http_code,
			'result' => json_decode($result, true),
		];
	}

	/**
	 * Generate UUID
	 *
	 * @return string
	 * */
	private function genUUID(): string {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}
}
