<?php

namespace CustomPaymentGateway\Gateway\Config;

use CustomPaymentGateway\Cron\SubscriptionCron;
use CustomPaymentGateway\Install;
use CustomPaymentGateway\Plugin;

final class Settings {

	public static function init() {
		if (!class_exists('WC_Settings_API')) {
			return;
		}
		$configs = [new GatewayConfig(), new CcConfig(), new ECheckConfig()];
		foreach ($configs as $config) {
			$config->registerSettings();
		}
	}

	public static function registerMenu() {
		add_submenu_page(
			'woocommerce',
			__('Custom Payment Gateway settings', Plugin::ID),
			__('Custom Payment Gateway', Plugin::ID),
			'manage_options',
			Plugin::ID,
			[__CLASS__, 'settingsPage']
		);
	}

	public static function settingsPage() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permission to access this page.', Plugin::ID));
		}
		if (isset($_POST[Plugin::ID . '_updates_nonce']) && wp_verify_nonce($_POST[Plugin::ID . '_updates_nonce'], Plugin::ID . '_updates')) {
			Install::runUpdates();
			echo '<div class="notice notice-success"><p>' . __('Database updates successful.') . '</p></div>';
			wp_redirect(wp_get_referer());
		}
		if (isset($_GET['settings-updated'])) {
			echo '<div class="notice notice-success"><p>' . __('Settings updated.') . '</p></div>';
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<form action="options.php" method="post">
				<input type="hidden" name="action" value="process_form">
				<?php
				echo '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')) . '">' . __('Back to payments', 'woocommerce') . ' &#x2934;</a>';
				settings_fields(Plugin::ID);
				do_settings_sections(Plugin::ID);
				submit_button();
				?>
			</form>
			<hr>
			<form action="" method="post">
				<?php
				$other_attributes = [];
				if (empty(Install::getUpdates())) {
					$other_attributes['disabled'] = TRUE;
				}
				wp_nonce_field(Plugin::ID . '_updates', Plugin::ID . '_updates_nonce');
				submit_button('Run database updates', 'primary', 'submit', TRUE, $other_attributes);
				echo '<p class="description">' . __('This button becomes active when the updated plugin has available database updates.', Plugin::ID) . '</p>';
				?>
			</form>
		</div>
		<?php
	}
}