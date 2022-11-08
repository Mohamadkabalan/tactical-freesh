<?php


function rafflepress_pro_process_email( $args ) {

	//error_log(print_r($args,true));
		
		global $wpdb;

		$contestant_id        = stripslashes( $args['contestant_id'] );
		$action_id            = stripslashes( $args['action_id'] );
		$email_integration_id = stripslashes( $args['email_integration_id'] );
		$giveaway_id          = stripslashes( $args['giveaway_id'] );

		// get contestant data
		$tablename     = $wpdb->prefix . 'rafflepress_contestants';
		$sql           = "SELECT * FROM $tablename WHERE id = %d";
		$safe_sql      = $wpdb->prepare( $sql, $contestant_id );
		$jn_contestant = $wpdb->get_row( $safe_sql );

		$data = array(
			'action_id'            => $action_id,
			'giveaway_id'          => $giveaway_id,
			'email_integration_id' => $email_integration_id,
			'api_token'            => get_option( 'rafflepress_api_token' ),
			'contestant'           => $jn_contestant,
		);

		// Build the headers of the request.
		$headers = array(
			'Accept' => 'application/json',
		);

		$url = RAFFLEPRESS_PRO_CALLBACK_URL . 'join-newsletter-callback';
		//error_log($url);

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'timeout' => 15,
				'headers' => $headers,
			)
		);

		// if(!empty(get_option('rafflepress_enable_log'))){
		//     $log = get_option('rafflepress_log');
		//     $log = $log. '||queue-email-log:'.print_r($response,true);
		//     update_option('rafflepress_log',$log);
		// }

		// error_log(print_r($data, true));
		// error_log(print_r($headers, true));
		// error_log(print_r($response, true));

	if ( is_wp_error( $response ) ) {
		// handle error
		error_log( print_r( $response, true ) );
	} else {
	}

		// Send zapier request if any exists
	if ( rafflepress_pro_cu( 'zp' ) ) {
		$tablename = $wpdb->prefix . 'rafflepress_giveaways';
		$sql       = "SELECT meta FROM $tablename WHERE id = %d LIMIT 3";
		$safe_sql  = $wpdb->prepare( $sql, $giveaway_id );
		$meta      = $wpdb->get_var( $safe_sql );
		$meta      = json_decode( $meta, true );

		if ( ! empty( $meta['zapier_hooks'] ) ) {
			$data = $jn_contestant;
			foreach ( $meta['zapier_hooks'] as $z ) {
				$response      = wp_remote_post(
					$z,
					array( 'body' => wp_json_encode( $data ) )
				);
				$response_code = wp_remote_retrieve_response_code( $response );
				if ( $response_code == '410' ) {
					$target_url = $z;
					// remove meta zapier hook array
					global $wpdb;
					$tablename = $wpdb->prefix . 'rafflepress_giveaways';
					$sql       = "SELECT meta FROM $tablename WHERE id = %d LIMIT 3";
					$safe_sql  = $wpdb->prepare( $sql, $giveaway_id );
					$meta      = $wpdb->get_var( $safe_sql );
					$meta      = json_decode( $meta, true );

					if ( ! empty( $meta['zapier_hooks'] ) ) {
						if ( ( $key = array_search( $target_url, $meta['zapier_hooks'] ) ) !== false ) {
							unset( $meta['zapier_hooks'][ $key ] );
						}
						$wpdb->update(
							$tablename,
							array(
								'meta' => wp_json_encode( $meta ),
							),
							array( 'id' => $giveaway_id ),
							array(
								'%s',
							),
							array( '%d' )
						);
					}
				}
			}
		}
	}
		
}
