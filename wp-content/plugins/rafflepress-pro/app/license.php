<?php


/**
 * Welcome Page On Activation
 */
add_action( 'admin_init', 'rafflepress_pro_welcome_screen_do_activation_redirect' );

function rafflepress_pro_welcome_screen_do_activation_redirect() {
	// Check PHP Version
	if ( version_compare( phpversion(), '5.3.3', '<=' ) ) {
		wp_die( __( "The minimum required version of PHP to run this plugin is PHP Version 5.3.3<br>Please contact your hosting company and ask them to upgrade this site's php verison.", 'rafflepress-pro' ), __( 'Upgrade PHP', 'rafflepress-pro' ), 200 );
	}

	// Bail if no activation redirect
	if ( ! get_transient( '_rafflepress_welcome_screen_activation_redirect' ) ) {
		return;
	}

	// Delete the redirect transient
	delete_transient( '_rafflepress_welcome_screen_activation_redirect' );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Redirect to our page
	wp_safe_redirect( add_query_arg( array( 'page' => 'rafflepress_pro' ), admin_url( 'admin.php' ) ) . '#/welcome' );
}



/**
 * Save API Key
 */
function rafflepress_pro_save_api_key( $api_key = null ) {
	if ( check_ajax_referer( 'rafflepress_pro_save_api_key', '_wpnonce', false ) || ! empty( $api_key ) ) {
		if ( empty( $api_key ) ) {
			$api_key = $_POST['api_key'];
		}

		// Validate the api key
		$data = array(
			'action'            => 'get-plugin-info',
			'api_key'           => $api_key,
			'token'             => get_option( 'rafflepress_token' ),
			'wp_version'        => get_bloginfo( 'version' ),
			'domain'            => home_url(),
			'installed_version' => RAFFLEPRESS_PRO_VERSION,
			'slug'              => 'rafflepress-pro',
		);

		if ( empty( $data['api_key'] ) ) {
			$response = array(
				'status' => 'false',
				'msg'    => 'License Key is Required.',
			);
			wp_send_json( $response );
			exit;
		}

		$headers = array();

		// Build the headers of the request.
		$headers = wp_parse_args(
			$headers,
			array(
				'Accept' => 'application/json',
			)
		);

		$url      = RAFFLEPRESS_PRO_API_URL . 'plugin-info';
		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$response = array(
				'status' => 'false',
				'msg'    => $response->get_error_message(),
			);
			wp_send_json( $response );
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! empty( $body ) ) {
			$body = json_decode( $body );
		}

		if ( ! empty( $body->valid ) && $body->valid == 'true' ) {
			// Store API key
			update_option( 'rafflepress_license_name', $body->plugin_name );
			update_option( 'rafflepress_api_token', $body->api_token );
			update_option( 'rafflepress_api_key', $data['api_key'] );
			update_option( 'rafflepress_api_message', $body->message );
			update_option( 'rafflepress_a', true );
			update_option( 'rafflepress_per', $body->per );
			$response = array(
				'status' => 'true',
				'msg'    => $body->message,
				'body'   => $body,
			);
		} else {
			$api_msg = __( 'Invalid License Key.', 'rafflepress-pro' );
			if ( $body->message != 'Unauthenticated.' ) {
				$api_msg = $body->message;
			}
			update_option( 'rafflepress_license_name', '' );
			update_option( 'rafflepress_api_token', '' );
			update_option( 'rafflepress_api_key', '' );
			update_option( 'rafflepress_api_message', $api_msg );
			update_option( 'rafflepress_a', false );
			update_option( 'rafflepress_per', '' );
			$response = array(
				'status' => 'false',
				'msg'    => $api_msg,
				'body'   => $body,
			);
		}

		// Send Response
		if ( ! empty( $_POST['api_key'] ) ) {
			wp_send_json( $response );
			exit;
		} else {
			return $response;
		}
	}
}


function rafflepress_pro_force_license_recheck( $api_key ) {
	if ( ! empty( $_GET['token'] ) && ! empty( $_GET['license-key'] ) && ! empty( $_GET['license-key'] ) && $_GET['action'] == 'recheck-license' ) {
		$token   = get_option( 'rafflepress_token' );
		$api_key = get_option( 'rafflepress_api_key' );
		$r       = false;
		if ( $_GET['token'] == $token && $_GET['license-key'] == $api_key ) {
			$r = rafflepress_pro_save_api_key( sanitize_text_field( $_GET['license-key'] ) );
		}
		wp_send_json( $r );
	}
}


function rafflepress_pro_deactivate_api_key( $api_key = null ) {
	if ( check_ajax_referer( 'rafflepress_pro_deactivate_api_key', '_wpnonce', false ) || ! empty( $api_key ) ) {
		if ( empty( $api_key ) ) {
			$api_key = $_POST['api_key'];
		}

		// Validate the api key
		$data = array(
			'api_token' => get_option( 'rafflepress_api_token' ),
			'api_key'   => $api_key,
			'token'     => get_option( 'rafflepress_token' ),
		);

		if ( empty( $data['api_key'] ) ) {
			$response = array(
				'status' => 'false',
				'msg'    => 'License Key is Required.',
			);
			wp_send_json( $response );
			exit;
		}

		$headers = array();

		// Build the headers of the request.
		$headers = wp_parse_args(
			$headers,
			array(
				'Accept' => 'application/json',
			)
		);

		$url      = RAFFLEPRESS_PRO_API_URL . 'plugin-deactivate';
		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$response = array(
				'status' => 'false',
				'msg'    => $response->get_error_message(),
			);
			wp_send_json( $response );
		} else {
			if ( $response['body'] == 'true' ) {
				update_option( 'rafflepress_api_key', '' );
				update_option( 'rafflepress_license_name', '' );
				update_option( 'rafflepress_per', '' );
				update_option( 'rafflepress_api_token', '' );
				update_option( 'rafflepress_a', false );
				$response = array(
					'status' => 'true',
					'msg'    => 'Deactivated',
				);
			} else {
				$response = array(
					'status' => 'false',
					'msg'    => __( 'There was an issue deactivating this license. Please log into the members area to deactivate.', 'rafflepress-pro' ),
				);
			}
			wp_send_json( $response );
		}
	}
}


	/**
	 *  Deactivate License
	 */
function rafflepress_pro_deactivate_license() {
	$token = get_option( 'rafflepress_token' );
	// $rafflepress_api_key = '';
	// //   if(defined('SEED_CSP_API_KEY')){
	// //       $rafflepress_api_key = SEED_CSP_API_KEY;
	// //   }
	//   if(empty($rafflepress_api_key)){
	//       $rafflepress_api_key = get_option('rafflepress_license_key');
	//   }

	if ( ( isset( $_REQUEST['rafflepress_token'] ) && $_REQUEST['rafflepress_token'] == $token ) && ( isset( $_REQUEST['rafflepress_action'] ) && $_REQUEST['rafflepress_action'] == 'deactivate' ) ) {
				$rafflepress_per = '';
				// if(!empty($_REQUEST['rafflepress_per'])){
				//     $rafflepress_per = $_REQUEST['rafflepress_per'];
				// }

				$rafflepress_api_nag = 'Site Deactivated';
		if ( ! empty( $_REQUEST['rafflepress_api_nag'] ) ) {
			$rafflepress_api_nag = $_REQUEST['rafflepress_api_nag'];
		}

				update_option( 'rafflepress_api_nag', $rafflepress_api_nag );
				update_option( 'rafflepress_api_message', $rafflepress_api_nag );
				update_option( 'rafflepress_api_key', '' );
				update_option( 'rafflepress_license_name', '' );
				update_option( 'rafflepress_per', '' );
				update_option( 'rafflepress_api_token', '' );
				update_option( 'rafflepress_a', false );

				echo 'true';

			exit();
	}
}



