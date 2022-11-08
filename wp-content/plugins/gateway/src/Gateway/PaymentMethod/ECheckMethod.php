<?php declare(strict_types = 1);

namespace CustomPaymentGateway\Gateway\PaymentMethod;

use CustomPaymentGateway\Gateway\Config\ECheckConfig;
use CustomPaymentGateway\Plugin;

/**
 * Class ECheckMethod
 */
final class ECheckMethod extends AbstractPaymentMethod {
	public const PAYMENT_METHOD_TYPE = 'ach';
	public const FORM_ID = ECheckConfig::METHOD_ID . '_echeck_form';
	public const FORM_ECHECK_ROUTING_NUMBER_ID = ECheckConfig::METHOD_ID . '_routing_number';
	public const FORM_ECHECK_ACCOUNT_NUMBER_ID = ECheckConfig::METHOD_ID . '_account_number';
	public const FORM_ECHECK_ACCOUNT_SEC_CODE_ID = ECheckConfig::METHOD_ID . '_sec_code';
	public const FORM_ECHECK_ACCOUNT_TYPE_ID = ECheckConfig::METHOD_ID . '_account_type';

	/**
	 * @var \CustomPaymentGateway\Gateway\Config\ECheckConfig
	 */
	public $config;

	/**
	 * Constructor method
	 */
	public function __construct() {
		parent::__construct(new ECheckConfig());
		$this->method_title = __('Payment Gateway - eCheck', Plugin::ID);
		$supports = [
			'products',
			'refunds',
			'subscriptions',
			'gateway_scheduled_payments',
			'subscription_cancellation',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
		];
		if ($this->config->getOption(ECheckConfig::KEY_SAVE_METHOD) === 'yes') {
			$supports[] = 'tokenization';
		}
		$this->supports = $supports;
	}

	/**
	 * Validate the ECheck payment fields.
	 */
	public function validate_fields(): bool {
		$all_green = parent::validate_fields();
		$token_id = $this->getTokenIdFromPost();
		if (!$token_id) {
			$post_data = self::getPostData();
			foreach ($post_data as $key => $data) {
				switch ($key) {
					case self::FORM_ECHECK_ACCOUNT_NUMBER_ID:
						if ($data === '') {
							wc_add_notice(__('Please provide an account number.', Plugin::ID), 'error');
							$all_green = false;
						}
						break;
					case self::FORM_ECHECK_ROUTING_NUMBER_ID:
						if ($data === '') {
							wc_add_notice(__('Please provide a routing number.', Plugin::ID), 'error');
							$all_green = false;
						}
						break;
				}
			}
		}
		return $all_green;
	}

	/**
	 * @inheritDoc
	 */
	public static function getPostData(): array {
		if (isset($_POST['woocommerce-process-checkout-nonce']) && !wp_verify_nonce(sanitize_key(wp_unslash($_POST['woocommerce-process-checkout-nonce'])), 'woocommerce-process_checkout')) {
			wp_nonce_ays('');
		}
		return [
			self::FORM_ECHECK_ROUTING_NUMBER_ID => isset($_POST[self::FORM_ECHECK_ROUTING_NUMBER_ID]) ? sanitize_text_field(wp_unslash($_POST[self::FORM_ECHECK_ROUTING_NUMBER_ID])) : null,
			self::FORM_ECHECK_ACCOUNT_NUMBER_ID => isset($_POST[self::FORM_ECHECK_ACCOUNT_NUMBER_ID]) ? sanitize_text_field(wp_unslash($_POST[self::FORM_ECHECK_ACCOUNT_NUMBER_ID])) : null,
			self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID => isset($_POST[self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID]) ? sanitize_text_field(wp_unslash($_POST[self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID])) : null,
			self::FORM_ECHECK_ACCOUNT_TYPE_ID => isset($_POST[self::FORM_ECHECK_ACCOUNT_TYPE_ID]) ? sanitize_text_field(wp_unslash($_POST[self::FORM_ECHECK_ACCOUNT_TYPE_ID])) : null,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function renderPaymentForm(): void { ?>
		<fieldset id="<?php echo esc_attr(self::FORM_ID); ?>" class="wc-echeck-form wc-payment-form">
			<?php do_action('woocommerce_echeck_form_start', ECheckConfig::METHOD_ID); ?>
			<p class="form-row form-row-first">
				<label
					for="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_TYPE_ID); ?>"><?php echo esc_html__('Account Type', Plugin::ID); ?>
					<span class="required">*</span></label>
				<select id="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_TYPE_ID); ?>"
					name="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_TYPE_ID); ?>" class="wc-enhanced-select">
					<option value="checking">Checking</option>
					<option value="savings">Saving</option>
				</select>
			</p>
			<p class="form-row form-row-last">
				<label
					for="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID); ?>"><?php echo esc_html__('Account SEC Code', Plugin::ID); ?>
					<span
						class="required">*</span></label>
				<select id="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID); ?>"
					name="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID); ?>"
					class="wc-enhanced-select">
					<option value="ppd">Personal</option>
					<option value="ccd">Corporate</option>
					<option value="web">Web</option>
				</select>
			</p>
			<p class="form-row form-row-first">
				<label
					for="<?php echo esc_attr(self::FORM_ECHECK_ROUTING_NUMBER_ID); ?>"><?php echo esc_html__('Routing number', Plugin::ID); ?>
					<span
						class="required">*</span></label>
				<input id="<?php echo esc_attr(self::FORM_ECHECK_ROUTING_NUMBER_ID); ?>"
					class="input-text"
					type="text"
					maxlength="9" autocomplete="off"
					name="<?php echo esc_attr(self::FORM_ECHECK_ROUTING_NUMBER_ID); ?>"/>
			</p>
			<p class="form-row form-row-last">
				<label
					for="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_NUMBER_ID); ?>"><?php echo esc_html__('Account number', Plugin::ID); ?>
					<span class="required">*</span></label>
				<input id="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_NUMBER_ID); ?>"
					class="input-text"
					type="text"
					autocomplete="off" name="<?php echo esc_attr(self::FORM_ECHECK_ACCOUNT_NUMBER_ID); ?>"
					maxlength="17"/>
			</p>
			<?php do_action('woocommerce_echeck_form_end', ECheckConfig::METHOD_ID); ?>
			<div class="clear"></div>
		</fieldset>
		<?php
	}

	/**
	 * @inheritDoc
	 */
	public function getFormattedPaymentData(): array {
		$post_data = self::getPostData();
		return [
			'routing_number' => $post_data[self::FORM_ECHECK_ROUTING_NUMBER_ID],
			'account_number' => $post_data[self::FORM_ECHECK_ACCOUNT_NUMBER_ID],
			'account_type' => $post_data[self::FORM_ECHECK_ACCOUNT_TYPE_ID],
			'sec_code' => $post_data[self::FORM_ECHECK_ACCOUNT_SEC_CODE_ID],
		];
	}
}
