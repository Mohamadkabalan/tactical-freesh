<?php

/*
 * Entry Datatable
 */
function rafflepress_pro_entries_datatable() {
	if ( check_ajax_referer( 'rafflepress_pro_entries_datatable' ) ) {
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
		$sql           = "SELECT name FROM $giveaway_tablename WHERE id = %d";
		$safe_sql      = $wpdb->prepare( $sql, absint( $_GET['id'] ) );
		$giveaway_name = $wpdb->get_var( $safe_sql );

		// Get entries
		$sql  = ' SELECT
        e.id,contestant_id,e.giveaway_id,email,fname, lname, e.action_id,e.meta,e.created_at,e.deleted_at,winning_entry_id, COUNT(*) worth
        ';
		$sql .= " FROM  $entries_tablename as e LEFT JOIN $tablename c ON c.id = e.contestant_id ";
		$sql .= ' WHERE e.giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
		if ( ! empty( $_GET['s'] ) && $_GET['s'] == 'image' ) {
			$sql .= " AND e.meta LIKE '%image_url%'";
		} elseif ( ! empty( $_GET['s'] ) ) {
			$sql .= " AND c.email LIKE '%" . esc_sql( trim( sanitize_text_field( $_GET['s'] ) ) ) . "%'";
		}
		$sql .= ' GROUP BY contestant_id,action_id,meta,created_at ';
		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = esc_sql( sanitize_text_field($_GET['orderby']));
			if ( $orderby == 'action' ) {
				$sql .= ' ORDER BY action_id';
			}
			if ( sanitize_text_field( $_GET['order'] ) === 'desc' ) {
				$order = 'DESC';
			} else {
				$order = 'ASC';
			}
			$sql .= ' ' . $order;
		} else {
			$sql .= ' ORDER BY e.created_at DESC';
		}
		$sql .= " LIMIT $per_page";
		if ( empty( $_GET['s'] ) || ( ! empty( $_GET['s'] ) && $_GET['s'] == 'image' ) ) {
			$sql .= ' OFFSET ' . ( $current_page - 1 ) * $per_page;
		}

		$results = $wpdb->get_results( $sql );
		//var_dump($results);
		$data = array();
		foreach ( $results as $v ) {

			 // Format Date
			$created_at = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $v->created_at ) );

			$class = '';
			if ( ! empty( $v->deleted_at ) ) {
				$class = 'rafflepress-invalid-entry';
			}

			if ( ! empty( $v->winning_entry_id == $v->id ) ) {
				$class = 'rafflepress-winner';
			}

			$action  = '';
			$details = 'N/A';
			if ( ! empty( $v->meta ) ) {
				$meta   = json_decode( $v->meta );
				$action = stripslashes( $meta->action );
				if ( ! empty( $meta->question ) ) {
					$answer   = stripslashes( $meta->answer );
					$question = stripslashes( $meta->question );
					$details  = 'Q: ' . $question . '<br>A: ' . $answer;
				} else {
					if ( ! empty( $meta->answer ) ) {
						$answer  = stripslashes( $meta->answer );
						$details = 'A: ' . $answer;
					}
				}

				if ( ! empty( $meta->username ) ) {
					$details = __( 'Username: ', 'rafflepress-pro' ) . $meta->username;
				}
				if ( ! empty( $meta->ref_email ) ) {
					$details = __( 'Signed Up: ', 'rafflepress-pro' ) . $meta->ref_email;
				}
				if ( ! empty( $meta->url ) ) {
					$details = '<a href="' . $meta->url . '" target="_blank">' . $meta->url . '</a>';
				}
				if ( ! empty( $meta->confirm ) ) {
					$details = 'Confirmed Double Optin';
				}

				if ( ! empty( $meta->image_url ) ) {
					$details = '<img src="' . $meta->image_url . '" style="max-width:100%;heigh:auto">';
				}
			}

			// Load Data
			$data[] = array(
				'id'          => $v->id,
				'email'       => $v->email,
				'name'        => $v->fname . ' ' . $v->lname,
				'action'      => $action,
				'details'     => $details,
				'worth'       => $v->worth,
				'created_at'  => $created_at,
				'giveaway_id' => $v->giveaway_id,
				'class'       => $class,
			);
		}

		$totalitems = rafflepress_pro_entries_get_data_total( $filter );
		$views      = rafflepress_pro_entries_get_views( $filter );

		$response = array(
			'rows'          => $data,
			'giveaway_name' => $giveaway_name,
			'totalitems'    => $totalitems,
			'totalpages'    => ceil( $totalitems / $per_page ),
			'currentpage'   => $current_page,
			'views'         => $views,
		);

		wp_send_json( $response );
	}
}

function rafflepress_pro_entries_report_datatable() {
	
    if (check_ajax_referer('rafflepress_pro_entries_report_datatable')) {
        
        global $wpdb;
		// get entry counts
		$tablename     = $wpdb->prefix . 'rafflepress_entries';
		$sql         = "select count(id) c, action_id from $tablename where giveaway_id = %d GROUP BY action_id";
		$safe_sql    = $wpdb->prepare( $sql, $_REQUEST['giveaway_id'] );
		$entries = $wpdb->get_results( $safe_sql );

		// get entry options and map id to name
		$tablename     = $wpdb->prefix . 'rafflepress_giveaways';
		$sql           = "SELECT * FROM $tablename WHERE id = %d";
		$safe_sql      = $wpdb->prepare( $sql, absint( $_REQUEST['giveaway_id'] ) );
		$giveaway      = $wpdb->get_row( $safe_sql );
		$settings      = json_decode( $giveaway->settings );
		$entry_options = $settings->entry_options;

		$entry_action_id = array();
		foreach ( $entry_options as $k => $v ) {
			$entry_action_id[$v->id] = $v->name;
		}


		$return_data = array(array(__( 'Action', 'rafflepress-pro' ),__( 'Entries', 'rafflepress-pro' )));

		foreach($entries as $k => $v){
			$return_data[] = array($entry_action_id[$v->action_id],absint($v->c));
		}

        wp_send_json($return_data);
        
    }
	
}

function rafflepress_pro_ps_results_datatable() {
	
	global $wpdb;

	$ps_ids = array();

	// get poll survey action ids
	$tablename = $wpdb->prefix . 'rafflepress_giveaways';
	$sql       = ' SELECT settings';
	$sql      .= " FROM  $tablename ";
	$sql      .= ' WHERE id = ' . esc_sql( absint( $_GET['giveaway_id'] ) );

	$results  = $wpdb->get_var( $sql );
	$settings = json_decode( $results );
	if ( ! empty( $settings->entry_options ) ) {
		$entry_options = $settings->entry_options;
		foreach ( $entry_options as $k => $v ) {
			if ( ! empty( $v->type ) && $v->type == 'polls-surveys' ) {

				$ps_ids[ $v->id ] = array(
					'q'    => $v->question,
					'data' => null,
				);
			}
		}
	}
	//wp_send_json($ps_ids);

	// get poll survey results by action id

	$tablename = $wpdb->prefix . 'rafflepress_polls';

	foreach ( $ps_ids as $k => $v ) {
		$sql  = ' SELECT count(*) c, meta';
		$sql .= " FROM  $tablename ";
		$sql .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['giveaway_id'] ) );
		$sql .= " AND action_id = '" . esc_sql( $k ) . "'";
		$sql .= ' GROUP BY meta';

		$results              = $wpdb->get_results( $sql );
		$ps_ids[ $k ]['data'] = $results;
	}

	// transform data
	$ps_results = array();
	foreach ( $ps_ids as $k => $v ) {
		$ps_results[] = array(
			'chartData'    => array(
				array( 'Key', 'Value' ),
			),
			'chartOptions' => array(
				'title' => $v['q'],
			),
		);
		foreach ( $v['data'] as $k2 => $v2 ) {
			array_push( $ps_results [ count( $ps_results ) - 1 ]['chartData'], array( $v2->meta, (int) $v2->c ) );
		}
	}

	$results = array(
		array(
			'chartData'    => array(
				array( 'Task', 'other' ),
				array( 'Work', 2 ),
				array( 'Play', 1 ),
			),
			'chartOptions' => array(
				'title' => 'my 1',
			),
		),
		array(
			'chartData'    => array(
				array( 'Task', 'other' ),
				array( 'Work', 2 ),
				array( 'Play', 4 ),
			),
			'chartOptions' => array(
				'title' => 'my 2',
			),
		),
	);
	wp_send_json( $ps_results );
	
}

function rafflepress_pro_entries_get_data_total( $filter = null ) {
	global $wpdb;

	$tablename          = $wpdb->prefix . 'rafflepress_contestants';
	$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
	$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

	$sql  = ' SELECT count(*)';
	$sql .= " FROM  $entries_tablename as e LEFT JOIN $tablename c ON c.id = e.contestant_id ";
	$sql .= ' WHERE e.giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );

	if ( ! empty( $_GET['s'] ) && $_GET['s'] == 'image' ) {
		$sql .= ' AND e.meta LIKE "%image_url%"';
	}

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
	}

	if ( ! empty( $_GET['s'] ) && $_GET['s'] != 'image' ) {
		$sql .= " AND email LIKE '%" . esc_sql( trim( sanitize_text_field( $_GET['s'] ) ) ) . "%'";
	}
	$sql .= ' GROUP BY contestant_id,action_id ';

	$results = $wpdb->get_var( $sql );

	return $wpdb->num_rows;
}

function rafflepress_pro_entries_get_views( $filter = null ) {
	$views   = array();
	$current = ( ! empty( $filter ) ? $filter : 'all' );

	global $wpdb;
	$tablename          = $wpdb->prefix . 'rafflepress_contestants';
	$entries_tablename  = $wpdb->prefix . 'rafflepress_entries';
	$giveaway_tablename = $wpdb->prefix . 'rafflepress_giveaways';

	//All link
	$sql     = ' SELECT count(*)';
	$sql    .= " FROM  $entries_tablename as e LEFT JOIN $tablename c ON c.id = e.contestant_id ";
	$sql    .= ' WHERE e.giveaway_id = ' . esc_sql( absint( $_GET['id'] ) );
	$sql    .= ' AND e.deleted_at IS NULL';
	$results = $wpdb->get_var( $sql );

	$views['all'] = $results;

	//Contestants link
	$sql                  = "SELECT count(id) FROM $tablename";
	$sql                 .= ' WHERE giveaway_id = ' . esc_sql( absint( $_GET['id'] ) ) . ' AND deleted_at is null ';
	$results              = $wpdb->get_var( $sql );
	$views['contestants'] = $results;

	return $views;
}


/*
* Confirm Selected Entries
*/
function rafflepress_pro_valid_selected_entries() {
	if ( check_ajax_referer( 'rafflepress_pro_valid_selected_entries' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			if ( ! empty( $_GET['ids'] ) ) {
				$ids          = array_map( 'intval', explode( ',', $_GET['ids'] ) );
				$how_many     = count( $ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format       = implode( ', ', $placeholders );

				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_entries';
				$sql       = 'UPDATE ' . $tablename . " SET deleted_at = NULL WHERE id IN ($format)";
				$safe_sql  = $wpdb->prepare( $sql, $ids );
				$result    = $wpdb->query( $safe_sql );

					// find related entries by action_id and contestant id
				foreach ( $ids as $id ) {
					global $wpdb;
					// Get entries

					$tablename = $wpdb->prefix . 'rafflepress_entries';
					$sql       = 'SELECT * FROM ' . $tablename . ' WHERE id = %d';
					$safe_sql  = $wpdb->prepare( $sql, $id );
					$result    = $wpdb->get_row( $safe_sql );

					$tablename = $wpdb->prefix . 'rafflepress_entries';
					$sql       = 'UPDATE ' . $tablename . ' SET deleted_at = NULL WHERE id = %d AND action_id = %s AND contestant_id = %d';
					$safe_sql  = $wpdb->prepare( $sql, $result->id, $result->action_id, $result->contestant_id );
					$result    = $wpdb->query( $safe_sql );
				}
			}
			wp_send_json( array( 'status' => true ) );
		}
	}
}


/*
* Invalid Selected Entries
*/
function rafflepress_pro_invalid_selected_entries() {
	if ( check_ajax_referer( 'rafflepress_pro_invalid_selected_entries' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			if ( ! empty( $_GET['ids'] ) ) {
				$ids          = array_map( 'intval', explode( ',', $_GET['ids'] ) );
				$how_many     = count( $ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format       = implode( ', ', $placeholders );

				global $wpdb;
				$tablename = $wpdb->prefix . 'rafflepress_entries';
				$sql       = 'UPDATE ' . $tablename . " SET deleted_at = now() WHERE id IN ( $format )";
				$safe_sql  = $wpdb->prepare( $sql, $ids );
				$result    = $wpdb->query( $safe_sql );

				// find related entries by action_id and contestant id
				foreach ( $ids as $id ) {
					global $wpdb;
					// Get entries

					$tablename = $wpdb->prefix . 'rafflepress_entries';
					$sql       = 'SELECT * FROM ' . $tablename . ' WHERE id = %d';
					$safe_sql  = $wpdb->prepare( $sql, $id );
					$result    = $wpdb->get_row( $safe_sql );

					$tablename = $wpdb->prefix . 'rafflepress_entries';
					$sql       = 'UPDATE ' . $tablename . ' SET deleted_at = now() WHERE id = %d AND action_id = %s AND contestant_id = %d';
					$safe_sql  = $wpdb->prepare( $sql, $result->id, $result->action_id, $result->contestant_id );
					$result    = $wpdb->query( $safe_sql );
				}
			}

			wp_send_json( array( 'status' => true ) );
		}
	}
}


/*
* Delete Invalid Entries
*/
function rafflepress_pro_delete_invalid_entries() {
	if ( check_ajax_referer( 'rafflepress_pro_delete_invalid_entries' ) ) {
		if ( current_user_can( apply_filters( 'rafflepress_list_users_capability', 'list_users' ) ) ) {
			global $wpdb;

			// Delete entries
			$tablename = $wpdb->prefix . 'rafflepress_entries';
			$sql       = 'DELETE FROM ' . $tablename . ' WHERE deleted_at IS NOT NULL';
			$result    = $wpdb->query( $sql );

			wp_send_json( array( 'status' => true ) );
		}
	}
}

/*
* Export Contestants
*/


function rafflepress_pro_export_subscribers_entry( $args = array(), $count = false ){

	global $wpdb;
	if ( true === $count ) {

		$entries_tablename     = $wpdb->prefix . 'rafflepress_entries';
		$contestants_tablename = $wpdb->prefix . 'rafflepress_contestants';
		$giveaway_tablename    = $wpdb->prefix . 'rafflepress_giveaways';

		$sql      = "SELECT count(*), e.id,contestant_id,email,fname, lname, e.action_id,e.meta,e.created_at,e.deleted_at from $entries_tablename as e LEFT JOIN $contestants_tablename as c ON c.id = e.contestant_id where c.giveaway_id = %d";
		$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );

		//$results = $wpdb->get_results( $safe_sql );
		return absint( $wpdb->get_var(
			$safe_sql
		));
		
		
	}else{
	
		$offset = $args['offset'];
		$limit_number = $args['number'];

		$entries_tablename     = $wpdb->prefix . 'rafflepress_entries';
		$contestants_tablename = $wpdb->prefix . 'rafflepress_contestants';
		$giveaway_tablename    = $wpdb->prefix . 'rafflepress_giveaways';

		$sql      = "SELECT  e.id,contestant_id,email,fname, lname, e.action_id,e.meta,e.created_at,e.deleted_at from $entries_tablename as e LEFT JOIN $contestants_tablename as c ON c.id = e.contestant_id 
		where c.giveaway_id = %d limit $offset , $limit_number ";
		$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );

		$results = $wpdb->get_results( $safe_sql );
		return $results;

	}


}

function rafflepress_pro_export_entries() {
	if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'rafflepress_pro_export_entries' && current_user_can('export') ) {
		if ( ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'rafflepress_pro_export_entries' ) !== false && ! empty( $_REQUEST['id'] ) ) {
			rafflepress_pro_set_time_limit();

			$out = ob_get_clean();
			
			global $wpdb;

			$entries_tablename     = $wpdb->prefix . 'rafflepress_entries';
			$contestants_tablename = $wpdb->prefix . 'rafflepress_contestants';
			$giveaway_tablename    = $wpdb->prefix . 'rafflepress_giveaways';


			$sql      = "SELECT * from $giveaway_tablename where id = %d";
			$safe_sql = $wpdb->prepare( $sql, absint( $_REQUEST['id'] ) );
			$giveaway = $wpdb->get_row( $safe_sql );

			$filename = sanitize_title( $giveaway->name ) . '_entries_' . date( 'Y-m-d_H:i', time() );
			
			$entries_per_step = 5000;
			$db_args = [
				'offset' => 0,
				'number' => $entries_per_step
			];
			$count = rafflepress_pro_export_subscribers_entry($db_args,true);

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
				__( 'ID', 'rafflepress-pro' ),
				__( 'Contestant ID', 'rafflepress-pro' ),
				__( 'First Name', 'rafflepress-pro' ),
				__( 'Last Name', 'rafflepress-pro' ),
				__( 'Email', 'rafflepress-pro' ),
				__( 'Action', 'rafflepress-pro' ),
				__( 'Details', 'rafflepress-pro' ),
				__( 'Created', 'rafflepress-pro' ),
				__( 'Invalid', 'rafflepress-pro' ),
			);
			
			$csv       = new SplFileObject( $export_file, 'a' );
			$enclosure = '"';
			$csv->fputcsv( $header,",",$enclosure);

			if($count>0){
				for ( $i = 1; $i <= $request_data['total_steps']; $i ++ ) {

					$data = rafflepress_pro_export_subscribers_entry($request_data['db_args'],false);

					foreach ( $data as $j => $row ) {
						$arow = array();
						
						$action  = '';
						$details = '';
						if ( ! empty( $row->meta ) ) {
							$meta   = json_decode( $row->meta );
							$action = stripslashes( $meta->action );
							if ( ! empty( $meta->question ) ) {
								$question = stripslashes( $meta->question );
								$answer   = '';
								if ( ! empty( $meta->answer ) ) {
									$answer = stripslashes( $meta->answer );
								}
								$details = 'Q: ' . $question . '- A: ' . $answer;
							} else {
								if ( ! empty( $meta->answer ) ) {
									$answer  = stripslashes( $meta->answer );
									$details = 'A: ' . $answer;
								}
							}

							if ( ! empty( $meta->ref_email ) ) {
								$details = __( 'Signed Up: ', 'rafflepress-pro' ) . $meta->ref_email;
							}

							if ( ! empty( $meta->username ) ) {
								$details = $meta->username;
							}
							if ( ! empty( $meta->url ) ) {
								$details = $meta->url;
							}
							if ( ! empty( $meta->confirm ) ) {
								$details = 'Confirmed Double Optin';
							}
						}

						$arow['id'] = $row->id;
						$arow['contestant_id'] = $row->contestant_id;
						$arow['fname'] = $row->fname;
						$arow['lname'] = $row->lname;
						$arow['email'] = $row->email;
						$arow['action'] = str_replace( '"', '""', $action );
						$arow['details'] = str_replace( '"', '""', $details );
						$arow['created_at'] = $row->created_at;
						$arow['deleted_at'] = $row->deleted_at;
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





/*
 * Pick Winners
 */

function rafflepress_pro_pick_winners() {
	if ( check_ajax_referer( 'rafflepress_pro_pick_winners' ) ) {
		$number_of_winners = 1;

		
		if ( ! empty( $_GET['number_of_winners'] ) ) {
			$number_of_winners = $_GET['number_of_winners'];
		}
		

		$qualified = 'unconfirmed';
		
		$qualified = 'confirmed';
		if ( ! empty( $_GET['qualified'] ) && $_GET['qualified'] == 'unconfirmed' ) {
			$qualified = 'unconfirmed';
		}
		

		global $wpdb;
		$winner = array();
		for ( $k = 0; $k < $number_of_winners; $k++ ) {
			$tablename_e = $wpdb->prefix . 'rafflepress_entries';
			$tablename_c = $wpdb->prefix . 'rafflepress_contestants';

			$sql  = 'SELECT id as entry_id ,contestant_id';
			$sql .= " FROM $tablename_e";
			$sql .= ' WHERE giveaway_id = %d ';
			$sql .= ' AND deleted_at IS NULL ';
			if ( $qualified == 'confirmed' ) {
				$sql .= " AND contestant_id IN (SELECT id FROM $tablename_c WHERE giveaway_id = %d AND status IN ('confirmed') AND winner = 0)";
			} else {
				$sql .= " AND contestant_id IN (SELECT id FROM $tablename_c WHERE giveaway_id = %d AND status IN ('confirmed','unconfirmed') AND winner = 0)";
			}
			if ( function_exists( 'is_wpe' ) ) {
				$sql_2   = 'SELECT count(id) as count';
				 $sql_2 .= " FROM $tablename_e";
				 $sql_2 .= ' WHERE giveaway_id = %d ';
				 $sql_2 .= ' AND deleted_at IS NULL ';
				if ( $qualified == 'confirmed' ) {
					 $sql_2 .= " AND contestant_id IN (SELECT id FROM $tablename_c WHERE giveaway_id = %d AND status IN ('confirmed') AND winner = 0)";
				} else {
					 $sql_2 .= " AND contestant_id IN (SELECT id FROM $tablename_c WHERE giveaway_id = %d AND status IN ('confirmed','unconfirmed') AND winner = 0)";
				}
				$safe_sql_1         = $wpdb->prepare( $sql_2, $_GET['id'], $_GET['id'] );
				$num_of_contestants = $wpdb->get_var( $safe_sql_1 );
				// Get random int
				$random_int = mt_rand( 0, $num_of_contestants - 1 );
				$sql       .= ' LIMIT %d,1';
				$safe_sql   = $wpdb->prepare( $sql, $_GET['id'], $_GET['id'], $random_int );
			} else {
				$sql     .= ' ORDER BY RAND() ';
				$sql     .= ' LIMIT %d';
				$safe_sql = $wpdb->prepare( $sql, $_GET['id'], $_GET['id'], 1 );
			}

			$winner = $wpdb->get_row( $safe_sql );
			if ( ! empty( $winner ) ) {
				$sql    = 'UPDATE ' . $tablename_c . ' SET winner = 1, winning_entry_id = ' . $winner->entry_id . ' WHERE id = ' . $winner->contestant_id;
				$result = $wpdb->query( $sql );
			}
		}

		if ( ! empty( $winner ) ) {
			wp_send_json( array( 'status' => true ) );
		} else {
			wp_send_json( array( 'status' => false ) );
		}
	}
}
