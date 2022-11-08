<?php

if ( is_admin() ) {
	// Admin Only
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/settings.php';
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/giveaway.php';
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/entry.php';
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/contestant.php';
	if ( RAFFLEPRESS_PRO_BUILD == 'lite' ) {
		require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/review.php';
	}
} else {
	// Public only
}


// Load on Public and Admin
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/license.php';
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/rafflepress.php';
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/functions-utils.php';

require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/api.php';

require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/cron.php';
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/notifications.php';

if ( function_exists( 'register_block_type' ) ) {
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/gblock.php';
}
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/classic-editor.php';
require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/standalone.php';
//if (RAFFLEPRESS_PRO_BUILD == 'lite') {
	require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/includes/upgrade.php';
//}


require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/remote-queue.php';

require_once RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/webhook-functions.php';
/**
* API Updates
*/
if ( ! class_exists( 'RafflePress_Updater' ) ) {
	// load our custom updater
	include RAFFLEPRESS_PRO_PLUGIN_PATH . 'app/class-updater.php';
}

function rafflepress_pro_updater() {
	$rafflepress_api_key = rafflepress_pro_get_api_key();

	$data = array();

	// Go ahead and initialize the updater.
	new RafflePress_Updater(
		array(
			'plugin_name' => 'RafflePress',
			'plugin_slug' => 'rafflepress-pro',
			'plugin_path' => plugin_basename( RAFFLEPRESS_PRO_SLUG ),
			'plugin_url'  => trailingslashit( home_url() ),
			'remote_url'  => RAFFLEPRESS_PRO_API_URL . 'plugin-info',
			'version'     => RAFFLEPRESS_PRO_VERSION,
			'key'         => $rafflepress_api_key,
			'data'        => $data,
		)
	);
}
add_action( 'admin_init', 'rafflepress_pro_updater', 0 );


