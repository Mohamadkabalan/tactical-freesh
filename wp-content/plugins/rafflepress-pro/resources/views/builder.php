<?php

// translations
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/views/backend-translations.php';

//wp_enqueue_media();

global $wpdb;

$giveaway_id = '';
if ( ! empty( $_GET['id'] ) ) {
	$giveaway_id = $_GET['id'];
}

if ( ! empty( $_GET['rp-debug'] ) ) {
	$tablename = $wpdb->prefix . 'rafflepress_giveaways';
	$sql       = "SELECT meta FROM $tablename WHERE id = %d LIMIT 3";
	$safe_sql  = $wpdb->prepare( $sql, $giveaway_id );
	$meta      = $wpdb->get_var( $safe_sql );
	echo 'rp-debug';
	var_dump( $meta );
}

// Template Vars
$timezones            = rafflepress_pro_get_timezones();
$times                = rafflepress_pro_get_times();
$has_refer_a_friend   = false;
$has_automatic_entry  = false;
$entry_options        = rafflepress_pro_entry_options();
$temp_start_countdown = 0;
$temp_end_countdown   = 0;
$total_entires        = 0;

if ( empty( $giveaway_id ) ) {
	// create new giveaway and redirect
	$giveaway = array(
		'id'   => 0,
		'name' => '',
		'type' => '',
	);
	$settings = array();
} else {

	// update giveaway
	// $tablename = $wpdb->prefix . 'rafflepress_giveaways';
	// $sql = "SELECT * FROM $tablename WHERE id = %d";
	// $safe_sql = $wpdb->prepare($sql, $giveaway_id);
	// $giveaway = $wpdb->get_row($safe_sql);

	$tablename  = $wpdb->prefix . 'rafflepress_giveaways';
	$tablename2 = $wpdb->prefix . 'rafflepress_entries';
	$sql        = "SELECT *,(SELECT count(id) FROM $tablename2 WHERE giveaway_id = %d AND deleted_at IS NULL) as entries FROM $tablename WHERE id = %d";
	$safe_sql   = $wpdb->prepare( $sql, $giveaway_id, $giveaway_id );
	$giveaway   = $wpdb->get_row( $safe_sql );

	if ( ! empty( $giveaway->entries ) ) {
		$total_entires = $giveaway->entries;
	}

	// Set active bool
	if ( $giveaway->active == 1 ) {
		$giveaway->active = true;
	} else {
		$giveaway->active = false;
	}

	// if ($giveaway->show_winners == 1) {
	//     $giveaway->show_winners= true;
	// } else {
	//     $giveaway->show_winners= false;
	// }

	if ( empty( $giveaway->settings ) ) {
		$settings = array(
			'prizes'        => array(
				array(
					'name'        => 'My Awesome Prize',
					'description' => '',
					'image'       => '',
					'video'       => '',
				),
			),
			'entry_options' => array(),
		);
	} else {
		$settings = json_decode( $giveaway->settings, true );
		$settings = rafflepress_pro_array_add( $settings, 'entry_options', array() );
	}

	$temp_start_countdown = strtotime( $giveaway->starts . ' UTC' );

	$temp_end_countdown = strtotime( $giveaway->ends . ' UTC' );

	// Default Confirmation Email
	if ( empty( $settings['confirmation_email'] ) ) {
		$settings['confirmation_email'] = __( 'Please click the link below to confirm your email address.', 'rafflepress-pro' ) . PHP_EOL . '{confirmation-link}';
	}

	if ( empty( $settings['confirmation_subject'] ) ) {
		$settings['confirmation_subject'] = __( '[Action Required] Confirm your entry', 'rafflepress-pro' );
	}

	if ( empty( $settings['from_name'] ) ) {
		$settings['from_name'] = get_option( 'admin_email' );
	}

	if ( empty( $settings['from_email'] ) ) {
		$settings['from_email'] = get_option( 'admin_email' );
	}

	// has refer a friend
	foreach ( $settings['entry_options'] as $v ) {
		if ( $v['type'] == 'refer-a-friend' ) {
			$has_refer_a_friend = true;
		}
		if ( $v['type'] == 'automatic-entry' ) {
			$has_automatic_entry = true;
		}
	}
}

// Email integration logic
$rafflepress_api_token = get_option( 'rafflepress_api_token' );
$license_key           = get_option( 'rafflepress_api_key' );
$email_integration_url = '';

if ( empty( rafflepress_pro_cu() ) || empty( $rafflepress_api_token ) ) {
	$email_integration_url = '';
} else {
	$token                 = get_option( 'rafflepress_token' );
	$email_integration_url = RAFFLEPRESS_PRO_WEB_API_URL . 'email_integrations?api_token=' . $rafflepress_api_token . '&token=' . $token . '&license_key=' . $license_key . '&giveaway_uuid=' . $giveaway->uuid . '&giveaway_id=' . $giveaway_id . '&action_id=';
}


// Pers
$per = array();
$active_license = false;


$per = get_option( 'rafflepress_per' );
$per = explode( ',', $per );

$rafflepress_a = get_option( 'rafflepress_a' );
if(!empty($rafflepress_a)){
	$active_license = true;
}

if(empty($active_license)){
	if ( !headers_sent()) {
		wp_redirect( "admin.php?page=rafflepress_pro#/settings" );
		exit;
	}else{
		echo '<script>window.location.replace("admin.php?page=rafflepress_pro#/settings");</script>';
	}
}



// set design
if ( ! empty( $settings['page_background_color'] ) ) {
	echo "
    <style>
#rafflepress-preview-wrapper {
        background-color: {$settings['page_background_color']};
    }
    </style>
    ";
}

if ( ! empty( $settings['page_background_image'] ) ) {
	echo "
    <style>
    #rafflepress-preview-wrapper{
        background-image: url({$settings['page_background_image']});
    }
    </style>
    ";
}

if ( ! empty( $settings['font'] ) ) {
	$font = rafflepress_pro_generate_font_output( $settings['font'] );
	echo $font;
}




?>


<div id="rafflepress-vue-app-builder"></div>
<div id="rafflepress-temp-font"></div>

<?php require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/giveaway-templates/rules-template.php'; ?>

<?php require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/giveaway-templates/google-fonts.php'; ?>
<?php require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/giveaway-templates/color-schemes.php'; ?>

<script>
<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_template', 'rafflepress_pro_save_template' ) ); ?>
var rafflepress_template_save_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_publish', 'rafflepress_pro_save_publish' ) ); ?>
var rafflepress_publish_save_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_giveaway', 'rafflepress_pro_save_giveaway' ) ); ?>
var rafflepress_save_giveaway_url = "<?php echo $ajax_url; ?>";

<?php $ajax_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_save_slug', 'rafflepress_pro_save_slug' ) ); ?>
var rafflepress_save_slug_url = "<?php echo $ajax_url; ?>";

<?php $utc_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_get_utc_offset', 'rafflepress_pro_get_utc_offset' ) ); ?>
var rafflepress_utc_url = "<?php echo $utc_url; ?>";

<?php $get_font_url = html_entity_decode( wp_nonce_url( 'admin-ajax.php?action=rafflepress_pro_get_font', 'rafflepress_pro_get_font' ) ); ?>
var rafflepress_get_font_url = "<?php echo $get_font_url; ?>";

<?php $rafflepress_upgrade_link = rafflepress_pro_upgrade_link( '' ); ?>

var rafflepress_data =
	<?php
	echo json_encode(
		array(
			'api_token'             => $rafflepress_api_token,
			'license_key'           => $license_key,
			'page_path'             => 'rafflepress_pro',
			'plugin_path'           => RAFFLEPRESS_PRO_PLUGIN_URL,
			'total_entries'         => $total_entires,
			'home_url'              => home_url(),
			'upgrade_link'          => $rafflepress_upgrade_link,
			'slug'                  => rafflepress_pro_get_slug(),
			'giveaway'              => $giveaway,
			'settings'              => $settings,
			'entry_options'         => $entry_options,
			'fonts'                 => $rafflepress_fonts,
			'color_schemes'         => $rafflepress_color_schemes,
			'rules_template'        => $rafflepress_rules,
			'timezones'             => $timezones,
			'times'                 => $times,
			'has_refer_a_friend'    => $has_refer_a_friend,
			'has_automatic_entry'   => $has_automatic_entry,
			'email_integration_url' => $email_integration_url,
			'temp_start_countdown'  => $temp_start_countdown,
			'temp_end_countdown'    => $temp_end_countdown,
			'per'                   => $per,
		)
	);
	?>
		;

var rafflepress_backend_translation_data = 
		<?php echo json_encode( $rp_backend_translations ); ?>;

</script>
