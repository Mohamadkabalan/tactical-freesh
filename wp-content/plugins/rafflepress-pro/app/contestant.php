<?php

/*
 * contestants Datatable
 */
function rafflepress_pro_contestants_datatable() {
	if ( check_ajax_referer( 'rafflepress_pro_contestants_datatable' ) ) {
		$data         = array( '' );
		$current_page = 1;
		if ( ! empty( absint( $_GET['current_page'] ) ) ) {
			$current_page = absint( $_GET['current_page'] );
		}
		$per_page = 20;

		$filter = null;
		if ( ! empty( $_GET['filter'] ) ) {
			$filter = sanitize_text_field( $_GET['filter'] );
			if ( $filter == 'all' ) {
				$filter = null;
			}
		}

		if ( ! empty( $_GET['s'] ) ) {
			$filter = null;
		}

		global $wpdb;

		$tablename          = $wpdb->prefix . 'rafflepress_contestants';
		$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
		$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

		// Get name
		$sql           = "SELECT * FROM $giveaway_tablename WHERE id = %d";
		$safe_sql      = $wpdb->prepare( $sql, absint( $_GET['id'] ) );
		$giveaway_data = $wpdb->get_row( $safe_sql );

		$giveaway_name     = $giveaway_data->name;
		$giveaway_settings = json_decode( $giveaway_data->settings );

		$enable_confirmation_email = false;
		if ( ! empty( $giveaway_settings->enable_confirmation_email ) ) {
			$enable_confirmation_email = true;
		}

		// Get records
		$sql = "SELECT *,
             (select count(*) from $entries_tablename  where
             $tablename.`id` = $entries_tablename.`contestant_id` AND deleted_at IS NULL) as `entries_count`
             FROM $tablename 
             ";

		$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );

		if ( ! empty( $filter ) ) {
			if ( esc_sql( $filter ) == 'confirmed' ) {
				$sql .= " AND  status = 'confirmed' ";
			}
			if ( esc_sql( $filter ) == 'unconfirmed' ) {
				$sql .= " AND  status = 'unconfirmed' ";
			}
			if ( esc_sql( $filter ) == 'invalid' ) {
				$sql .= " AND  status = 'invalid' ";
			}
			if ( esc_sql( $filter ) == 'winners' ) {
				$sql .= ' AND  winner = 1 ';
			}
			if ( esc_sql( $filter ) == 'all' ) {
				$sql .= " AND  status != 'invalid' ";
			}
		}

		if ( ! empty( $_GET['s'] ) ) {
			$sql .= " AND email LIKE '%" . esc_sql( trim( sanitize_text_field( $_GET['s'] ) ) ) . "%'";
		}

		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = esc_sql( sanitize_text_field($_GET['orderby']));
			if ( $orderby == 'entries' ) {
				$sql .= ' ORDER BY entries_count';
			}
			if ( $orderby == 'email' ) {
				$sql .= ' ORDER BY email';
			}
			if ( $orderby == 'created_at' ) {
				$sql .= ' ORDER BY created_at';
			}
			if ( $orderby == 'status' ) {
				$sql .= ' ORDER BY status';
			}

			if ( esc_sql(sanitize_text_field( $_GET['order'] )) === 'desc' ) {
				$order = 'DESC';
			} else {
				$order = 'ASC';
			}
			$sql .= ' ' . $order;
		} else {
			$sql .= ' ORDER BY winner DESC,created_at DESC';
		}

		$sql .= " LIMIT $per_page";
		if ( empty( $_GET['s'] ) ) {
			$sql .= ' OFFSET ' . ( $current_page - 1 ) * $per_page;
		}

		$results = $wpdb->get_results( $sql );
		//var_dump($results);
		$data = array();
		foreach ( $results as $v ) {

			   // Format Date
			$created_at = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $v->created_at ) );

			$class  = '';
			$status = '';
			if ( $v->status == 'confirmed' ) {
				$status = __( 'Yes', 'rafflepress-pro' );
			} elseif ( $v->status == 'unconfirmed' ) {
				$status = __( 'No', 'rafflepress-pro' );
			}

			if ( $v->winner ) {
				$class = 'rafflepress-winner';
			}

			// Load Data
			$data[] = array(
				'id'          => $v->id,
				'email'       => $v->email,
				'name'        => $v->fname . ' ' . $v->lname,
				'status'      => $status,
				'status_raw'  => $v->status,
				'entries'     => $v->entries_count,
				'created_at'  => $created_at,
				'giveaway_id' => $v->giveaway_id,
				'class'       => $class,
				'winner'      => $v->winner,
			);
		}

		$totalitems = rafflepress_pro_contestants_get_data_total( $filter );
		$views      = rafflepress_pro_contestants_get_views( $filter );

		$response = array(
			'rows'                      => $data,
			'giveaway_name'             => $giveaway_name,
			'enable_confirmation_email' => $enable_confirmation_email,
			'totalitems'                => $totalitems,
			'totalpages'                => ceil( $totalitems / $per_page ),
			'currentpage'               => $current_page,
			'views'                     => $views,
		);

		wp_send_json( $response );
	}
}

function rafflepress_pro_contestants_get_data_total( $filter = null ) {
	global $wpdb;

	$tablename = $wpdb->prefix . 'rafflepress_contestants';

	$sql = "SELECT count(id) FROM $tablename";

	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );

	if ( ! empty( $filter ) ) {
		if ( esc_sql( $filter ) == 'confirmed' ) {
			$sql .= " AND  status = 'confirmed' ";
		}
		if ( esc_sql( $filter ) == 'unconfirmed' ) {
			$sql .= " AND  status = 'unconfirmed' ";
		}
		if ( esc_sql( $filter ) == 'invalid' ) {
			$sql .= " AND  status = 'invalid' ";
		}
		if ( esc_sql( $filter ) == 'winners' ) {
			$sql .= ' AND  winner = 1 ';
		}
		if ( esc_sql( $filter ) == 'all' ) {
			$sql .= " AND  status != 'invalid' ";
		}
	} else {
		$sql .= " AND  status != 'invalid' ";
	}

	if ( ! empty( $_GET['s'] ) ) {
		$sql .= " AND email LIKE '%" . esc_sql( trim( sanitize_text_field( $_GET['s'] ) ) ) . "%'";
	}

	$results = $wpdb->get_var( $sql );
	return $results;
}

function rafflepress_pro_contestants_get_views( $filter = null ) {
	$views   = array();
	$current = ( ! empty( $filter ) ? $filter : 'all' );

	global $wpdb;
	$tablename         = $wpdb->prefix . 'rafflepress_contestants';
	$tablename_entries = $wpdb->prefix . 'rafflepress_entries';

	//All link
	$sql = "SELECT count(id) FROM $tablename";

	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) ) . " AND deleted_at is null AND status != 'invalid' ";

	$results      = $wpdb->get_var( $sql );
	$class        = ( $current == 'all' ? ' class="current"' : '' );
	$all_url      = remove_query_arg( 'filter' );
	$views['all'] = $results;

	//Confirmed link
	$sql  = "SELECT count(id) FROM $tablename";
	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql .= " AND  status = 'confirmed' ";

	$results            = $wpdb->get_var( $sql );
	$confirmed_url      = add_query_arg( 'filter', 'confirmed' );
	$class              = ( $current == 'confirmed' ? ' class="current"' : '' );
	$views['confirmed'] = $results;

	//Unconfirmed link
	$sql  = "SELECT count(id) FROM $tablename";
	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql .= " AND  status = 'unconfirmed' ";

	$results              = $wpdb->get_var( $sql );
	$unconfirmed_url      = add_query_arg( 'filter', 'unconfirmed' );
	$class                = ( $current == 'unconfirmed' ? ' class="current"' : '' );
	$views['unconfirmed'] = $results;

	//Invalid link
	$sql  = "SELECT count(id) FROM $tablename";
	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql .= " AND  status = 'invalid' ";

	$results          = $wpdb->get_var( $sql );
	$invalid_url      = add_query_arg( 'filter', 'invalid' );
	$class            = ( $current == 'invalid' ? ' class="current"' : '' );
	$views['invalid'] = $results;

	//Winners link
	$sql  = "SELECT count(id) FROM $tablename";
	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql .= ' AND  winner = 1 ';

	$results          = $wpdb->get_var( $sql );
	$winners_url      = add_query_arg( 'filter', 'winners' );
	$class            = ( $current == 'winners' ? ' class="current"' : '' );
	$views['winners'] = $results;

	//Entries link
	$sql  = "SELECT count(id) FROM $tablename_entries";
	$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql .= ' AND  deleted_at IS NULL ';

	$results          = $wpdb->get_var( $sql );
	$views['entries'] = $results;

	return $views;
}


/*
* Confirm Selected contestants
*/
function rafflepress_pro_confirm_selected_contestants() {
	if ( check_ajax_referer( 'rafflepress_pro_confirm_selected_contestants' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			if ( ! empty( $_GET['ids'] ) && strpos( $_GET['ids'], ',' ) !== false ) {
				$ids          = array_map( 'intval', explode( ',', $_GET['ids'] ) );
				$how_many     = count( $ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format       = implode( ', ', $placeholders );

				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$sql       = 'UPDATE ' . $tablename . " SET status = 'confirmed' WHERE id IN ($format)";
				$safe_sql  = $wpdb->prepare( $sql, $ids );
				$result    = $wpdb->query( $safe_sql );

				// confirm any refer a friend entries if email confirmation enabled
				$tablename = $wpdb->prefix . 'rafflepress_entries';
				$sql       = 'UPDATE ' . $tablename . " SET deleted_at = NULL WHERE referrer_id IN ($format)";
				$safe_sql  = $wpdb->prepare( $sql, $$ids );
				$result    = $wpdb->query( $safe_sql );

			} else {
				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$result    = $wpdb->update(
					$tablename,
					array(
						'status' => 'confirmed',
					),
					array( 'id' => $_GET['ids'] ),
					array(
						'%s',
					),
					array( '%d' )
				);

				// confirm any refer a friend entries if email confirmation enabled
				$tablename = $wpdb->prefix . 'rafflepress_entries';
				$sql       = 'UPDATE ' . $tablename . ' SET deleted_at = NULL WHERE referrer_id = %d';
				$safe_sql  = $wpdb->prepare( $sql, $_GET['ids'] );
				$result    = $wpdb->query( $safe_sql );

			}

			wp_send_json( array( 'status' => true ) );
		}
	}
}

/*
* Delete Invalid Entries
*/
function rafflepress_pro_contestants_resend_email() {
	if ( check_ajax_referer( 'rafflepress_pro_contestants_resend_email' ) ) {

			$contestant_id = absint( $_GET['id'] );
			$giveaway_id   = absint( $_GET['giveaway_id'] );

			global $wpdb;

			$tablename = $wpdb->prefix . 'rafflepress_giveaways';
			$sql       = "SELECT * FROM $tablename WHERE id = %d";
			$safe_sql  = $wpdb->prepare( $sql, $giveaway_id );
			$giveaway  = $wpdb->get_row( $safe_sql );
			$settings  = json_decode( $giveaway->settings );

			$tablename  = $wpdb->prefix . 'rafflepress_contestants';
			$sql        = "SELECT * FROM $tablename WHERE id = %d";
			$safe_sql   = $wpdb->prepare( $sql, $contestant_id );
			$contestant = $wpdb->get_row( $safe_sql );

			// send confirmation email
		if ( ! empty( $settings->enable_confirmation_email ) && $settings->enable_confirmation_email == 'true' ) {
			$slug = rafflepress_pro_get_slug();

			if ( ! empty( $slug ) ) {
				$giveaway_url = home_url() . '?rafflepress_id=' . $giveaway_id;
			}

			$giveaway_url = $giveaway_url . '&confirm=' . $contestant->token . '&id=' . $contestant_id;

			$template_tags = array(
				'{confirmation-link}' => $giveaway_url,
			);
			$msg           = strtr( $settings->confirmation_email, $template_tags );

			$subject = __( '[Action Required] Confirm your entry', 'rafflepress-pro' );
			if ( $settings->confirmation_subject ) {
				$subject = $settings->confirmation_subject;
			}

			$from_email = get_option( 'admin_email' );
			if ( ! empty( $settings->from_email ) ) {
				$from_email = $settings->from_email;
			}

			$from_name = $from_email;
			if ( ! empty( $settings->from_name ) ) {
				$from_name = $settings->from_name;
			}

			$headers   = array();
			$headers[] = "From: $from_name <$from_email>";

			// Send confirmation email

			$mresult = wp_mail( $contestant->email, $subject, $msg, $headers );

			wp_send_json( array( 'status' => true ) );
		}
	}
	wp_send_json( array( 'status' => false ) );
}

/*
* Unconfirm Selected contestants
*/
function rafflepress_pro_unconfirm_selected_contestants() {
	if ( check_ajax_referer( 'rafflepress_pro_unconfirm_selected_contestants' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			if ( ! empty( $_GET['ids'] ) && strpos( $_GET['ids'], ',' ) !== false ) {
				$ids          = array_map( 'intval', explode( ',', $_GET['ids'] ) );
				$how_many     = count( $ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format       = implode( ', ', $placeholders );

				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$sql       = 'UPDATE ' . $tablename . " SET status = 'unconfirmed' WHERE id IN ($format)";
				$safe_sql  = $wpdb->prepare( $sql, $ids );
				$result    = $wpdb->query( $safe_sql );
			} else {
				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$result    = $wpdb->update(
					$tablename,
					array(
						'status' => 'unconfirmed',
					),
					array( 'id' => $_GET['ids'] ),
					array(
						'%s',
					),
					array( '%d' )
				);
			}
			wp_send_json( array( 'status' => true ) );
		}
	}
}

/*
* Invalid Selected contestants
*/
function rafflepress_pro_invalid_selected_contestants() {
	if ( check_ajax_referer( 'rafflepress_pro_invalid_selected_contestants' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			if ( ! empty( $_GET['ids'] ) && strpos( $_GET['ids'], ',' ) !== false ) {
				$ids          = array_map( 'intval', explode( ',', $_GET['ids'] ) );
				$how_many     = count( $ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format       = implode( ', ', $placeholders );

				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$sql       = 'UPDATE ' . $tablename . " SET status = 'invalid',winner = 0, winning_entry_id = 0 WHERE id IN ($format)";
				$safe_sql  = $wpdb->prepare( $sql, $ids );
				$result    = $wpdb->query( $safe_sql );
			} else {
				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_contestants';
				$result    = $wpdb->update(
					$tablename,
					array(
						'status'           => 'invalid',
						'winner'           => 0,
						'winning_entry_id' => 0,
					),
					array( 'id' => $_GET['ids'] ),
					array(
						'%s',
						'%d',
						'%d',
					),
					array( '%d' )
				);
			}

			wp_send_json( array( 'status' => true ) );
		}
	}
}


/*
* Delete Invalid Entries
*/
function rafflepress_pro_delete_invalid_contestants() {
	if ( check_ajax_referer( 'rafflepress_pro_delete_invalid_contestants' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			global $wpdb;
			$tablename = $wpdb->prefix . 'rafflepress_contestants';
			$sql       = "SELECT id FROM $tablename";
			$sql      .= " WHERE status = 'invalid'";
			$ids       = $wpdb->get_col( $sql );

			$how_many     = count( $ids );
			$placeholders = array_fill( 0, $how_many, '%d' );
			$format       = implode( ', ', $placeholders );

			// Deleted contestants
			$tablename = $wpdb->prefix . 'rafflepress_contestants';
			$sql       = 'DELETE FROM ' . $tablename . " WHERE id IN ($format)";
			$safe_sql  = $wpdb->prepare( $sql, $ids );
			$result    = $wpdb->query( $safe_sql );

			// Delete entries
			$tablename = $wpdb->prefix . 'rafflepress_entries';
			$sql       = 'DELETE FROM ' . $tablename . " WHERE contestant_id IN ($format)";
			$safe_sql  = $wpdb->prepare( $sql, $ids );
			$result    = $wpdb->query( $safe_sql );

			wp_send_json( array( 'status' => true ) );
		}
	}
}

/*
* Export Contestants
*/


function rafflepress_pro_export_subscribers_users( $args = array(), $count = false ){

	global $wpdb;
	if ( true === $count ) {

		$tablename          = $wpdb->prefix . 'rafflepress_contestants';
		$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
		$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

		$sql      = "SELECT * from $giveaway_tablename where id = %d";
		$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );
		$giveaway = $wpdb->get_row( $safe_sql );

		//echo $_REQUEST['id'];
		$sql      = "SELECT count(*), a.id,a.fname,a.lname,a.email, a.status, a.winner,a.created_at,a.ip,b.email as 'referred_by',a.terms_consent,
	(select count(*) from $entries_tablename where
	a.`id` = $entries_tablename.`contestant_id`) as `entries_count`
	FROM $tablename a
	LEFT JOIN $tablename b
	ON a.referrer_id = b.id
	WHERE a.giveaway_id = %d";

		//echo "--------------------";

		$safe_sql = $wpdb->prepare( $sql, $giveaway->id );

		//$results = $wpdb->get_results( $safe_sql );
		return absint( $wpdb->get_var(
			$safe_sql
		));
		
		
	}else{

			$tablename          = $wpdb->prefix . 'rafflepress_contestants';
			$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
			$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

			$offset = $args['offset'];
			$limit_number = $args['number'];


			$sql      = "SELECT * from $giveaway_tablename where id = %d";
			$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );
			$giveaway = $wpdb->get_row( $safe_sql );

			$sql      = "SELECT a.id,a.fname,a.lname,a.email, a.status, a.winner,a.created_at,a.ip,b.email as 'referred_by',a.terms_consent,
        (select count(*) from $entries_tablename where
        a.`id` = $entries_tablename.`contestant_id`) as `entries_count`
        FROM $tablename a
        LEFT JOIN $tablename b
        ON a.referrer_id = b.id
        WHERE a.giveaway_id = %d  limit $offset , $limit_number";
			$safe_sql = $wpdb->prepare( $sql, $giveaway->id );

			$results = $wpdb->get_results( $safe_sql );


			return $results;


	}

}


function rafflepress_pro_export_contestants() {
	if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'rafflepress_pro_export_contestants' && current_user_can('export')) {
		if ( ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'rafflepress_pro_export_contestants' ) !== false && ! empty( $_REQUEST['id'] ) ) {
			
			rafflepress_pro_set_time_limit();

			$out = ob_get_clean();
			global $wpdb;
			
			
			$tablename          = $wpdb->prefix . 'rafflepress_contestants';
			$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
			$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

			$sql      = "SELECT * from $giveaway_tablename where id = %d";
			$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );
			$giveaway = $wpdb->get_row( $safe_sql );
			
			$filename = sanitize_title( $giveaway->name ) . '_users_' . date( 'Y-m-d_H:i', time() );
			
			$entries_per_step = 5000;
			$db_args = [
				'offset' => 0,
				'number' => $entries_per_step
			];
			$count = rafflepress_pro_export_subscribers_users($db_args,true);

			$request_data = [
				'db_args' => $db_args,
				'count' => $count,
				'total_steps' => ceil( $count / $entries_per_step ),
			];

			$tmpname = md5(strtotime("now"));
			$export_file_data = rafflepress_pro_get_tmpfname( $tmpname );
			$export_file =  $export_file_data ;	
			if ( empty( $export_file ) ) {
				return;
			}

			$header = array(
				__( 'First Name', 'rafflepress-pro' ),
				__( 'Last Name', 'rafflepress-pro' ),
				__( 'Email', 'rafflepress-pro' ),
				__( 'Entries', 'rafflepress-pro' ),
				__( 'Status', 'rafflepress-pro' ),
				__( 'Winner', 'rafflepress-pro' ),
				__( 'Created', 'rafflepress-pro' ),
				__( 'IP', 'rafflepress-pro' ),
				__( 'Meta', 'rafflepress-pro' ),
				__( 'Referred By', 'rafflepress-pro' ),
				__( 'Terms Consent', 'rafflepress-pro' ),
			);
			
			$csv       = new SplFileObject( $export_file, 'a' );
			$enclosure = '"';

			$csv->fputcsv( $header,",",$enclosure);
			
			if($count>0){
				for ( $i = 1; $i <= $request_data['total_steps']; $i ++ ) {

					$data = rafflepress_pro_export_subscribers_users($request_data['db_args'],false);

					

					foreach ( $data as $j => $row ) {
						$arow = array();

						$print_meta = '';
						if ( ! empty( $row->meta ) ) {
							$meta = json_decode( $row->meta );
							foreach ( $meta as $k => $v ) {
								if ( substr( $k, 0, 6 ) === 'answer' ) {
									$print_meta .= __( 'Answer', 'rafflepress-pro' ) . ':' . $v . ',';
								}
							}
						}

						$arow['fname'] = $row->fname;
						$arow['lname'] = $row->lname;
						$arow['email'] = $row->email;
						$arow['entries_count'] = $row->entries_count;
						$arow['status'] = $row->status;
						$arow['winner'] = $row->winner;
						$arow['created_at'] = $row->created_at;
						$arow['ip'] = str_replace( ',', ':', $row->ip );
						$arow['meta'] = $print_meta;
						$arow['referred_by'] = $row->referred_by;
						$arow['terms_consent'] = $row->terms_consent;
						
						$csv->fputcsv( $arow,",",$enclosure);
					}
					$request_data['db_args']['offset'] = $i * $entries_per_step;
				}
			}

			clearstatcache( true, $export_file );
			$file_name = $filename.".csv";

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename=' . $file_name );
			header( 'Content-Transfer-Encoding: binary' );

			readfile( $export_file ); // phpcs:ignore
			exit;
			
		}
	}
}

