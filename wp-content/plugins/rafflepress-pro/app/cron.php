<?php

add_action( 'rafflepress_notifications', 'rafflepress_pro_do_notifications' );
function rafflepress_pro_do_notifications() {
	// get settings
	$settings = get_option( 'rafflepress_settings' );

	if ( empty( $settings ) ) {
		return false;
	}

	$settings = json_decode( $settings );

	if ( $settings === null ) {
		return false;
	}

	if ( $settings->updates == 'none' ) {
		return false;
	}

	global $wpdb;

	$from_email = get_bloginfo( 'admin_email' );

	$from_name = get_bloginfo( 'name' );

	$to      = $settings->updates_to;
	$subject = 'RafflePress ' . __( 'Updates', 'rafflepress-pro' );

	$headers = array(
		'From: ' . $from_name . ' <' . $from_email . '>',
		'Content-Type: text/html; charset=UTF-8',
	);

	$daily_results_running  = null;
	$weekly_results_running = null;
	$daily_results_ended    = null;
	$weekly_results_ended   = null;
	$tablename              = $wpdb->prefix . 'rafflepress_giveaways';
	$entries_tablename      = $wpdb->prefix . 'rafflepress_entries';
	$constestants_tablename = $wpdb->prefix . 'rafflepress_contestants';
	if ( $settings->updates == 'daily' ) {
		$sql                   = "SELECT *,
        (select count(*) from  $entries_tablename where
        $tablename.`id` =  $entries_tablename.`giveaway_id` AND $entries_tablename.`created_at` >= DATE_ADD(CURDATE(), INTERVAL -1 DAY) ) as `entries_count`,
        (select count(*) from $constestants_tablename where
        $tablename.`id` = $constestants_tablename.`giveaway_id` AND $constestants_tablename.`created_at` >= DATE_ADD(CURDATE(), INTERVAL -1 DAY) ) as `contestants_count` 
        FROM $tablename WHERE 1 = 1  AND  UTC_TIMESTAMP() > starts AND deleted_at is null AND  UTC_TIMESTAMP() < ends  AND  active = 1  ORDER BY created_at DESC";
		$daily_results_running = $wpdb->get_results( $sql );

		$sql                 = "SELECT *,
        (select count(*) from  $entries_tablename where
        $tablename.`id` =  $entries_tablename.`giveaway_id`) as `entries_count`,
        (select count(*) from $constestants_tablename where
        $tablename.`id` = $constestants_tablename.`giveaway_id`) as `contestants_count` 
        FROM $tablename WHERE 1 = 1  AND deleted_at is null AND  ends BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND UTC_TIMESTAMP()  AND  active = 1  ORDER BY created_at DESC";
		$daily_results_ended = $wpdb->get_results( $sql );

		ob_start();
		include RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/emails/notification.php';
		$message                   = ob_get_clean();
		$rafflepress_daily_lastrun = get_option( '$rafflepress_daily_lastrun' );
		if ( empty( $rafflepress_daily_lastrun ) || time() - $rafflepress_daily_lastrun >= 86400 ) {
			add_action( 'phpmailer_init', 'rafflepress_pro_multi_part_email' );
			wp_mail( $to, $subject, $message, $headers );
			remove_filter( 'phpmailer_init', 'rafflepress_pro_multi_part_email' );
			update_option( '$rafflepress_daily_lastrun', time() );
		}
	} elseif ( $settings->updates == 'weekly' ) {
		if ( date( 'w' ) == 1 ) {
			$sql                    = "SELECT *,
        (select count(*) from  $entries_tablename where
        $tablename.`id` =  $entries_tablename.`giveaway_id` AND $entries_tablename.`created_at` >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)) as `entries_count`,
        (select count(*) from $constestants_tablename where
        $tablename.`id` = $constestants_tablename.`giveaway_id` AND $constestants_tablename.`created_at` >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)) as `contestants_count` 
        FROM $tablename WHERE 1 = 1  AND  UTC_TIMESTAMP() > starts AND deleted_at is null AND  UTC_TIMESTAMP() < ends  AND  active = 1  ORDER BY created_at DESC";
			$weekly_results_running = $wpdb->get_results( $sql );

			$sql                  = "SELECT *,
        (select count(*) from  $entries_tablename where
        $tablename.`id` =  $entries_tablename.`giveaway_id`) as `entries_count`,
        (select count(*) from $constestants_tablename where
        $tablename.`id` = $constestants_tablename.`giveaway_id`) as `contestants_count` 
        FROM $tablename WHERE 1 = 1  AND deleted_at is null AND  ends BETWEEN DATE_ADD(CURDATE(), INTERVAL -7 DAY) AND UTC_TIMESTAMP()  AND  active = 1  ORDER BY created_at DESC";
			$weekly_results_ended = $wpdb->get_results( $sql );

			ob_start();
			include RAFFLEPRESS_PRO_PLUGIN_PATH . 'resources/emails/notification.php';
			$message = ob_get_clean();

			$rafflepress_weekly_lastrun = get_option( '$rafflepress_weekly_lastrun' );
			if ( empty( $rafflepress_weekly_lastrun ) || time() - $rafflepress_weekly_lastrun >= 604800 ) {
				add_action( 'phpmailer_init', 'rafflepress_pro_multi_part_email' );
				wp_mail( $to, $subject, $message, $headers );
				remove_filter( 'phpmailer_init', 'rafflepress_pro_multi_part_email' );
				update_option( '$rafflepress_weekly_lastrun', time() );
			}
		}
	}
}

function rafflepress_pro_multi_part_email( $phpmailer ) {
	$text_message       = wp_strip_all_tags(
		preg_replace(
			'/^([ \t]*\n){2,}/m',
			"\n",
			preg_replace(
				'/\s\s+/m',
				' ',
				preg_replace(
					'/^\s+/m',
					'',
					preg_replace(
						'/<br(\s+)?\/?>/i',
						"\n",
						$phpmailer->Body
					)
				)
			)
		)
	);
	$phpmailer->AltBody = $text_message;
}

