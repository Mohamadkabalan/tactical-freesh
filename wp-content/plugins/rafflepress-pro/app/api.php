<?php


function rafflepress_pro_get_available_integrations() {
	if ( false === get_transient( 'rafflepress_available_integratons' ) ) {

		// get api key
		$api_token = get_option( 'rafflepress_api_token' );
		if ( empty( $api_token ) ) {
			return false;
		}
		$url = RAFFLEPRESS_PRO_API_URL . 'available_integrations' . '?api_token=' . $api_token;
		// get list and store in cache
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		} else {
			$available_integrations = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $available_integrations ) ) {
				set_transient( 'rafflepress_available_integratons', $available_integrations, 432000 );
				return $available_integrations;
			} else {
				return false;
			}
		}
	} else {
		$available_integrations = get_transient( 'rafflepress_available_integratons' );
	}

	return $available_integrations;
}

function rafflepress_pro_zapier_callback() {
	$data = array();

	// RafflePress Zapier API key is required.
	if ( empty( $_GET['rafflepress_action'] ) ) {
		return;
	}

	// Callback action is required.
	if ( empty( $_GET['rafflepress_api_token'] ) ) {
		return;
	}

	// Validate provided API Key.
	$apikey = get_option( 'rafflepress_api_token' );
	if ( empty( $apikey ) || trim( $_GET['rafflepress_api_token'] ) !== $apikey ) {
		// Key is incorrect or missing.
		nocache_headers();
		header( 'HTTP/1.1 401 Unauthorized' );
		echo wp_json_encode(
			array(
				'error' => esc_html__( 'Invalid RafflePress Zapier API key', 'rafflepress-pro' ),
			)
		);
		exit;
	}

	// Provide available giveaways
	if ( 'giveaways' === $_GET['rafflepress_action'] ) {
		global $wpdb;
		$tablename = $wpdb->prefix . 'rafflepress_giveaways';
		$sql       = "SELECT * FROM $tablename WHERE deleted_at IS NULL";
		$giveaways = $wpdb->get_results( $sql );

		if ( ! empty( $giveaways ) ) {
			foreach ( $giveaways as $v ) {
				$data['giveaways'][] = array(
					'id'   => $v->id,
					'name' => sanitize_text_field( $v->name ),
				);
			}
		}
	}

	// Provide available fields from a recent form entry.
	if ( 'contestants' === $_GET['rafflepress_action'] && ! empty( $_REQUEST['giveaway_id'] ) ) {
		global $wpdb;
		$tablename = $wpdb->prefix . 'rafflepress_contestants';

		// Get name
		$sql         = "SELECT * FROM $tablename WHERE giveaway_id = %d LIMIT 3";
		$safe_sql    = $wpdb->prepare( $sql, $_REQUEST['giveaway_id'] );
		$contestants = $wpdb->get_results( $safe_sql );

		if ( ! empty( $contestants ) ) {
			foreach ( $contestants as $v ) {
				$data[] = array(
					'id'    => $v->id,
					'fname' => sanitize_text_field( $v->fname ),
					'lname' => sanitize_text_field( $v->lname ),
					'email' => sanitize_text_field( $v->email ),
				);
			}
		}
	}

	// Subscribe/Add Zap.
	if ( 'subscribe' === $_GET['rafflepress_action'] ) {
		$postdata = file_get_contents( 'php://input' );
		if ( ! empty( $postdata ) ) {
			$postdata = json_decode( $postdata );
		}
		$giveaway_id = absint( $_GET['rafflepress_giveaway_id'] );
		$target_url  = ! empty( $postdata->target_url ) ? esc_url_raw( $postdata->target_url ) : '';

		// add meta zapier hook array
		global $wpdb;
		$tablename = $wpdb->prefix . 'rafflepress_giveaways';
		$sql       = "SELECT meta FROM $tablename WHERE id = %d LIMIT 3";
		$safe_sql  = $wpdb->prepare( $sql, $giveaway_id );
		$meta      = $wpdb->get_var( $safe_sql );
		$meta      = json_decode( $meta, true );

		if ( empty( $meta['zapier_hooks'] ) ) {
			$meta = array(
				'zapier_hooks' => array( $target_url ),
			);
		} else {
			$meta['zapier_hooks'][] = $target_url;
			$meta['zapier_hooks']   = array_unique( $meta['zapier_hooks'] );
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

		$data = array(
			'status' => 'subscribe',
		);
	}

	// Unsubscribe/Delete Zap.
	if ( 'unsubscribe' === $_GET['rafflepress_action'] ) {
		$postdata = file_get_contents( 'php://input' );
		if ( ! empty( $postdata ) ) {
			$postdata = json_decode( $postdata );
		}
		$giveaway_id = absint( $_GET['rafflepress_giveaway_id'] );
		$target_url  = ! empty( $postdata->target_url ) ? esc_url_raw( $postdata->target_url ) : '';

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

		$data = array(
			'status' => 'unsubscribe',
		);
	}

	// If data is empty something went wrong, so we stop.
	if ( empty( $data ) ) {
		$data = array(
			'error' => esc_html__( 'No data', 'rafflepress-pro' ),
		);
	}

	nocache_headers();
	wp_send_json( $data );
}
