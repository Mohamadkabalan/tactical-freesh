<?php
// translations
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/views/backend-translations.php';


$rafflepress_api_token = get_option( 'rafflepress_api_token' );
$license_key           = get_option( 'rafflepress_api_key' );

$current_user = wp_get_current_user();
$name         = ',';
if ( ! empty( $current_user->user_firstname ) ) {
	$name = $current_user->user_firstname . ',';
}
$email = $current_user->user_email;

$timezones = rafflepress_pro_get_timezones();

// Pers
$per = array();
$active_license = false;

$per = get_option( 'rafflepress_per' );
$per = explode( ',', $per );

$rafflepress_a = get_option( 'rafflepress_a' );
if(!empty($rafflepress_a)){
	$active_license = true;
}



$license_name = '';


$license_name = get_option( 'rafflepress_license_name' );


// Get notifications
$notifications = new RafflePress_Notifications();
$notifications = $notifications->get();

?>


<div id="rafflepress-vue-app"></div>
<script>

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_run_one_click_upgrade', 'rafflepress_pro_run_one_click_upgrade' ) ); ?>
var rafflepress_run_one_click_upgrade_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_upgrade_license', 'rafflepress_pro_upgrade_license' ) ); ?>
var rafflepress_upgrade_license_url = "<?php echo $ajax_url; ?>";


<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_plugin_nonce', 'rafflepress_pro_plugin_nonce' ) ); ?>
var rafflepress_plugin_nonce_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_get_plugins_list', 'rafflepress_pro_get_plugins_list' ) ); ?>
var rafflepress_get_plugins_list_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_install_addon', 'rafflepress_pro_install_addon' ) ); ?>
var rafflepress_get_install_addon_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_activate_addon', 'rafflepress_pro_activate_addon' ) ); ?>
var rafflepress_activate_addon_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_deactivate_addon', 'rafflepress_pro_deactivate_addon' ) ); ?>
var rafflepress_deactivate_addon_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_giveaway_datatable', 'rafflepress_pro_giveaway_datatable' ) ); ?>
var rafflepress_giveaway_datatable_url = "<?php echo $ajax_url; ?>";


<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_get_giveaway_list', 'rafflepress_pro_get_giveaway_list' ) ); ?>
var rafflepress_get_giveaway_list = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_duplicate_giveaway', 'rafflepress_pro_duplicate_giveaway' ) ); ?>
var rafflepress_duplicate_giveaway_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_archive_selected_giveaways', 'rafflepress_pro_archive_selected_giveaways' ) ); ?>
var rafflepress_archive_selected_giveaways = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_notification_dismiss', 'rafflepress_pro_notification_dismiss' ) ); ?>
var rafflepress_notification_dismiss = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_unarchive_selected_giveaways', 'rafflepress_pro_unarchive_selected_giveaways' ) ); ?>
var rafflepress_unarchive_selected_giveaways = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_delete_archived_giveaways', 'rafflepress_pro_delete_archived_giveaways' ) ); ?>
var rafflepress_delete_archived_giveaways = "<?php echo $ajax_url; ?>";

<?php
$save_settings_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_settings', 'rafflepress_pro_save_settings' ) );
?>
var rafflepress_save_settings_ajax_url = "<?php echo $save_settings_ajax_url; ?>";

<?php
$dismiss_settings_lite_cta_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_dismiss_settings_lite_cta', 'rafflepress_pro_dismiss_settings_lite_cta' ) );
?>
var rafflepress_dismiss_settings_lite_cta_url = "<?php echo $dismiss_settings_lite_cta_ajax_url; ?>";

<?php
$save_api_key_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_api_key', 'rafflepress_pro_save_api_key' ) );
?>
var rafflepress_save_api_key_ajax_url = "<?php echo $save_api_key_ajax_url; ?>";

<?php
$deactivate_api_key_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_deactivate_api_key', 'rafflepress_pro_deactivate_api_key' ) );
?>
var rafflepress_deactivate_api_key_ajax_url = "<?php echo $deactivate_api_key_ajax_url; ?>";

<?php
$end_giveaway_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_end_giveaway', 'rafflepress_pro_end_giveaway' ) );
?>
var rafflepress_end_giveaway_ajax_url = "<?php echo $end_giveaway_ajax_url; ?>";

<?php
$start_giveaway_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_start_giveaway', 'rafflepress_pro_start_giveaway' ) );
?>
var rafflepress_start_giveaway_ajax_url = "<?php echo $start_giveaway_ajax_url; ?>";

<?php
$start_giveaway_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_start_giveaway', 'rafflepress_pro_start_giveaway' ) );
?>
var rafflepress_start_giveaway_ajax_url = "<?php echo $start_giveaway_ajax_url; ?>";


<?php
$enable_disable_giveaway_ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_enable_disable_giveaway', 'rafflepress_pro_enable_disable_giveaway' ) );
?>
var rafflepress_enable_disable_giveaway_ajax_url = "<?php echo $enable_disable_giveaway_ajax_url; ?>";


<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_contestants_resend_email', 'rafflepress_pro_contestants_resend_email' ) ); ?>
var rafflepress_contestants_resend_email_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_contestants_datatable', 'rafflepress_pro_contestants_datatable' ) ); ?>
var rafflepress_contestants_datatable_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_confirm_selected_contestants', 'rafflepress_pro_confirm_selected_contestants' ) ); ?>
var rafflepress_confirm_selected_contestants_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_unconfirm_selected_contestants', 'rafflepress_pro_unconfirm_selected_contestants' ) ); ?>
var rafflepress_unconfirm_selected_contestants_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_invalid_selected_contestants', 'rafflepress_pro_invalid_selected_contestants' ) ); ?>
var rafflepress_invalid_selected_contestants_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_delete_invalid_contestants', 'rafflepress_pro_delete_invalid_contestants' ) ); ?>
var rafflepress_delete_invalid_contestants_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_entries_report_datatable', 'rafflepress_pro_entries_report_datatable' ) ); ?>
var rafflepress_entries_report_datatable_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_ps_results_datatable', 'rafflepress_pro_ps_results_datatable' ) ); ?>
var rafflepress_ps_results_datatable_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_entries_datatable', 'rafflepress_pro_entries_datatable' ) ); ?>
var rafflepress_entries_datatable_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_valid_selected_entries', 'rafflepress_pro_valid_selected_entries' ) ); ?>
var rafflepress_valid_selected_entries_url = "<?php echo $ajax_url; ?>";


<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_invalid_selected_entries', 'rafflepress_pro_invalid_selected_entries' ) ); ?>
var rafflepress_invalid_selected_entries_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_delete_invalid_entries', 'rafflepress_pro_delete_invalid_entries' ) ); ?>
var rafflepress_delete_invalid_entries_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_pick_winners', 'rafflepress_pro_pick_winners' ) ); ?>
var rafflepress_pick_winners_url = "<?php echo $ajax_url; ?>"; 


<?php
$admin_url = admin_url();
$ajax_url  = html_entity_decode( wp_nonce_url( 'admin.php?page=rafflepress_pro&action=rafflepress_pro_export_contestants', 'rafflepress_pro_export_contestants' ) );
?>
var rafflepress_export_contestants_url = "<?php echo $ajax_url; ?>";

<?php
$admin_url = admin_url();
$ajax_url  = html_entity_decode( wp_nonce_url( 'admin.php?page=rafflepress_pro&action=rafflepress_pro_export_entries', 'rafflepress_pro_export_entries' ) );
?>
var rafflepress_export_entries_url = "<?php echo $ajax_url; ?>";


<?php $rafflepress_settings = get_option( 'rafflepress_settings' ); ?>
<?php
$rafflepress_api_key = get_option( 'rafflepress_api_key' );
if ( $rafflepress_api_key === false ) {
	$rafflepress_api_key = '';
}
?>
<?php $rafflepress_upgrade_link = rafflepress_pro_upgrade_link( '' ); ?>

<?php
$lmsg = get_option( 'rafflepress_api_message' );
if ( empty( $lmsg ) ) {
	$lmsg = '';
}
$lclass = 'alert-danger';
if ( rafflepress_pro_cu() ) {
	$lclass = 'alert-success';
}
?>



// settings: {
// 				button: false,
// 				lmsg: "",
// 				lclass: "",
// 				api_key: "",
// 				default_timezone: "UTC",
// 				slug: "rafflepress",
// 				updates: "none",
// 				updates_to: ""
// 			},

var rafflepress_data_admin =
	<?php
	echo json_encode(
		array(
			'notifications'             => $notifications,
			'api_token'                 => $rafflepress_api_token,
			'license_key'               => $license_key,
			'license_name'              => $license_name,
			'per'                       => $per,
			'active_license'            => $active_license,
			'page_path'                 => 'rafflepress_pro',
			'plugin_path'               => RAFFLEPRESS_PRO_PLUGIN_URL,
			'home_url'                  => home_url(),
			'upgrade_link'              => $rafflepress_upgrade_link,
			'timezones'                 => $timezones,
			'api_key'                   => $rafflepress_api_key,
			'name'                      => $name,
			'email'                     => $email,
			'lmsg'                      => $lmsg,
			'lclass'                    => $lclass,
			'settings'                  => json_decode( $rafflepress_settings ),
			'dismiss_settings_lite_cta' => get_option( 'rafflepress_dismiss_settings_lite_cta' ),
		)
	);
	?>
						;

var rafflepress_backend_translation_data = 
		<?php echo json_encode( $rp_backend_translations ); ?>;

</script>
