<?php


/**
 * Enqueue Styles and Scripts
 */
function rafflepress_pro_admin_enqueue_scripts( $hook_suffix ) {
	$is_localhost = rafflepress_pro_is_localhost();

	// Load our admin styles and scripts only on our pages
	if ( strpos( $hook_suffix, 'rafflepress_pro' ) !== false ) {
		// remove conflicting scripts
		wp_dequeue_script( 'googlesitekit_admin' );

		$vue_app_folder = 'pro';
		if ( strpos( $hook_suffix, 'rafflepress_pro_builder' ) !== false ) {
			if ( $is_localhost ) {
				

				wp_register_script(
					'rafflepress_vue_builder_app',
					'http://localhost:8080/index.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_enqueue_script( 'rafflepress_vue_builder_app' );
				
			} else {
				wp_register_script(
					'rafflepress_vue_builder_app_1',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/js/index.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_register_script(
					'rafflepress_vue_builder_app_2',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/js/chunk-vendors.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_enqueue_script( 'rafflepress_vue_builder_app_1' );
				wp_enqueue_script( 'rafflepress_vue_builder_app_2' );
				wp_enqueue_style( 'rafflepress_vue_builder_app_css_1', RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/css/chunk-vendors.css', false, RAFFLEPRESS_PRO_VERSION );
			}
		} else {
			if ( $is_localhost ) {
				
				wp_register_script(
					'rafflepress_vue_admin_app',
					'http://localhost:8080/admin.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_enqueue_script( 'rafflepress_vue_admin_app' );
				
			} else {
				wp_register_script(
					'rafflepress_vue_admin_app_1',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/js/admin.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_register_script(
					'rafflepress_vue_admin_app_2',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/js/chunk-vendors.js',
					array(),
					RAFFLEPRESS_PRO_VERSION,
					true
				);
				wp_enqueue_script( 'rafflepress_vue_admin_app_1' );
				wp_enqueue_script( 'rafflepress_vue_admin_app_2' );
				wp_enqueue_style(
					'rafflepress_vue_admin_app_css_1',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/css/chunk-vendors.css',
					false,
					RAFFLEPRESS_PRO_VERSION
				);
				wp_enqueue_style(
					'rafflepress_vue_admin_app_css_2',
					RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-backend/css/admin.css',
					false,
					RAFFLEPRESS_PRO_VERSION
				);
			}
		}

		wp_enqueue_style(
			'rafflepress-css',
			RAFFLEPRESS_PRO_PLUGIN_URL . 'public/css/admin-style.min.css',
			false,
			RAFFLEPRESS_PRO_VERSION
		);

		wp_enqueue_style(
			'rafflepress-fontawesome',
			RAFFLEPRESS_PRO_PLUGIN_URL . 'public/fontawesome/css/all.min.css',
			false,
			RAFFLEPRESS_PRO_VERSION
		);

		wp_register_script(
			'rafflepress-iframeresizer',
			RAFFLEPRESS_PRO_PLUGIN_URL . 'public/js/iframeResizer.min.js',
			array(),
			RAFFLEPRESS_PRO_VERSION,
			false
		);
		wp_enqueue_script( 'rafflepress-iframeresizer' );

		wp_enqueue_media();
		wp_enqueue_script( 'wp-tinymce' );
		wp_enqueue_editor();
	}
}
add_action( 'admin_enqueue_scripts', 'rafflepress_pro_admin_enqueue_scripts' );


function rafflepress_pro_wp_enqueue_scripts() {
	wp_register_script(
		'rafflepress-if-shortcode',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/js/iframeResizer.min.js',
		array(),
		RAFFLEPRESS_PRO_VERSION,
		true
	);

	wp_register_script(
		'rafflepress-iframeresizer-frontend',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/js/iframeResizer.min.js',
		array(),
		RAFFLEPRESS_PRO_VERSION,
		true
	);

	wp_register_script(
		'rafflepress-iframeresizer-content',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/js/iframeResizer.contentWindow.min.js',
		array(),
		RAFFLEPRESS_PRO_VERSION,
		true
	);

	$vue_app_folder = RAFFLEPRESS_PRO_BUILD;
	wp_register_script(
		'rafflepress-app',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-frontend/js/app.js',
		false,
		RAFFLEPRESS_PRO_VERSION,
		false
	);

	wp_register_script(
		'rafflepress-vendors',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/' . $vue_app_folder . '/vue-frontend/js/chunk-vendors.js',
		false,
		RAFFLEPRESS_PRO_VERSION,
		false
	);

}
add_action( 'init', 'rafflepress_pro_wp_enqueue_scripts' );

function rafflepress_pro_scripts_mod( $tag, $handle, $src ) {
	// The handles of the enqueued scripts we want to defer
	$defer_scripts = array(
		'rafflepress-if-shortcode',
		'rafflepress-iframeresizer-frontend',
		'rafflepress-app',
	);

	if ( in_array( $handle, $defer_scripts ) ) {
		//return '<script src="' . $src . '&'.mt_rand(1, 99999).'" data-cfasync="false" type="text/javascript"></script>' . "\n";
		return '<script src="' . $src . '" data-cfasync="false" type="text/javascript"></script>' . "\n";
	}

	return $tag;
}

add_filter( 'script_loader_tag', 'rafflepress_pro_scripts_mod', 10, 3 );

function rafflepress_pro_wp_enqueue_styles() {
	wp_register_style(
		'rafflepress-style',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/css/rafflepress-style.min.css',
		false,
		RAFFLEPRESS_PRO_VERSION
	);
	//wp_enqueue_style('rafflepress-style');

	wp_register_style(
		'rafflepress-fontawesome',
		RAFFLEPRESS_PRO_PLUGIN_URL . 'public/fontawesome/css/all.min.css',
		false,
		RAFFLEPRESS_PRO_VERSION
	);

	//wp_enqueue_style('rafflepress-fontawesome');
}
add_action( 'init', 'rafflepress_pro_wp_enqueue_styles' );

/**
 * Add or Upgrade DB
 */
add_action( 'admin_init', 'rafflepress_pro_db', 0 );

function rafflepress_pro_db() {
	 // get current version
	$rafflepress_pro_current_version = get_option( 'rafflepress_version' );
	$upgrade_complete                = false;
	if ( empty( $rafflepress_pro_current_version ) ) {
		$rafflepress_pro_current_version = 0;
	}

	$rafflepress_run_activation = get_option( 'rafflepress_run_activation' );
	if ( version_compare( $rafflepress_pro_current_version, RAFFLEPRESS_PRO_VERSION ) === -1 || ! empty( $_GET['rafflepress_force_db_setup'] ) || $rafflepress_run_activation ) {
		// Upgrade db if new version
		rafflepress_pro_db_setup();
		$upgrade_complete = true;
		if ( $rafflepress_run_activation ) {
			update_option( 'rafflepress_run_activation', false );
		}
	}

	if ( $upgrade_complete ) {
		update_option( 'rafflepress_version', RAFFLEPRESS_PRO_VERSION );
	}
}

/**
 * Create Database Custom Tables
 */
function rafflepress_pro_db_setup() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Activations Table
	$tablename = $wpdb->prefix . 'rafflepress_giveaways';
	$sql       = "CREATE TABLE `$tablename` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(250) DEFAULT NULL,
        `slug` varchar(250) DEFAULT NULL,
        `parent_url` varchar(250) DEFAULT NULL,
        `uuid` varchar(250) DEFAULT NULL,
        `settings` longtext,
        `meta` longtext,
        `starts` datetime DEFAULT NULL,
        `ends` datetime DEFAULT NULL,
        `active` tinyint(4) NOT NULL DEFAULT '1',
        `show_leaderboard` tinyint(4) NOT NULL DEFAULT '0',
        `giveawaytemplate_id` varchar(250) NULL DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `deleted_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    );";
	dbDelta( $sql );

	$tablename = $wpdb->prefix . 'rafflepress_contestants';
	$sql       = "CREATE TABLE `$tablename` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `giveaway_id` int(10) unsigned NOT NULL,
        `fname` varchar(191) DEFAULT NULL,
        `lname` varchar(191) DEFAULT NULL,
        `email` varchar(191) NOT NULL,
        `meta` longtext,
        `ip` varchar(255) DEFAULT NULL,
        `referrer_id` int(10) unsigned DEFAULT NULL,
        `winner` tinyint(4) NULL DEFAULT 0,
        `terms_consent` tinyint(4) NULL DEFAULT 0,
        `winning_entry_id` int(10) unsigned NOT NULL,
        `token` varchar(16) NOT NULL,
        `status` enum('unconfirmed','confirmed','invalid') NOT NULL DEFAULT 'unconfirmed',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `deleted_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
      );";
	dbDelta( $sql );

	$tablename = $wpdb->prefix . 'rafflepress_entries';
	$sql       = "CREATE TABLE `$tablename` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `contestant_id` int(10) unsigned NOT NULL,
        `giveaway_id` int(10) unsigned NOT NULL,
        `action_id`  varchar(10) NULL DEFAULT NULL,
        `meta` longtext,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `deleted_at` timestamp NULL DEFAULT NULL,
        `referrer_id` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id`)
    );";
	dbDelta( $sql );
	
	$tablename = $wpdb->prefix . 'rafflepress_polls';
	$sql       = "CREATE TABLE `$tablename` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `contestant_id` int(10) unsigned NOT NULL,
        `giveaway_id` int(10) unsigned NOT NULL,
        `action_id`  varchar(10) NULL DEFAULT NULL,
        `meta` longtext,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `deleted_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    );";
	dbDelta( $sql );
	

}

/**
 * Display settings link on plugin page
 */
add_filter( 'plugin_action_links', 'rafflepress_pro_plugin_action_links', 10, 2 );

function rafflepress_pro_plugin_action_links( $links, $file ) {
	$plugin_file = RAFFLEPRESS_PRO_SLUG;

	if ( $file == $plugin_file ) {
		$settings_link = '<a href="admin.php?page=rafflepress_pro">Setup</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

/**
 * Remove other plugin's style from our page so they don't conflict
 */

add_action( 'admin_enqueue_scripts', 'rafflepress_pro_deregister_backend_styles', PHP_INT_MAX );

function rafflepress_pro_deregister_backend_styles() {
	// remove scripts registered ny the theme so they don't screw up our page's style
	if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'rafflepress_builder' ) !== false ) {
		global $wp_styles;
		// list of styles to keep else remove
		$keep_styles = 'admin-bar|ie|wp-auth-check|colors|query-monitor';
		$s           = explode( '|', $keep_styles );

		foreach ( $wp_styles->queue as $handle ) {
			//echo '<br> '.$handle;
			if ( ! in_array( $handle, $s ) ) {
				if ( strpos( $handle, 'rafflepress' ) === false ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
					//echo '<br>removed '.$handle;
				}
			}
		}
	}
}


add_filter( 'admin_body_class', 'rafflepress_pro_add_admin_body_classes' );
function rafflepress_pro_add_admin_body_classes( $classes ) {
	if ( ! empty( $_GET['page'] ) && strpos( $_GET['page'], 'rafflepress_pro' ) !== false ) {
		$classes .= ' rafflepress-body rafflepress-pro';
	}
	if ( ! empty( $_GET['page'] ) && ( strpos( $_GET['page'], 'rafflepress_pro_builder' ) !== false ) ) {
		$classes .= ' rafflepress-builder rafflepress-pro';
	}
	return $classes;
}


// Review Request
add_action( 'admin_footer_text', 'rafflepress_pro_admin_footer' );

function rafflepress_pro_admin_footer( $text ) {
	global $current_screen;

	if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'rafflepress' ) !== false ) {
		$url  = 'https://wordpress.org/support/plugin/rafflepress/reviews/?filter=5#new-post';
		$text = sprintf( __( 'Please rate <strong>RafflePress</strong> <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%2$s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the RafflePress team!', 'rafflepress-pro' ), $url, $url );
	}
	return $text;
}


