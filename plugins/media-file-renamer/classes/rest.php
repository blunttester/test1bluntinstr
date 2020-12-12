<?php

class Meow_MFRH_Rest
{
	private $core = null;
	private $admin = null;
	private $namespace = 'media-file-renamer/v1';

	public function __construct( $core ) {
		if ( !current_user_can( 'administrator' ) ) {
			return;
		} 
		$this->core = $core;
		$this->admin = $core->admin;

		// FOR DEBUG
		// For experiencing the UI behavior on a slower install.
		// sleep(1);
		// For experiencing the UI behavior on a buggy install.
		// trigger_error( "Error", E_USER_ERROR);
		// trigger_error( "Warning", E_USER_WARNING);
		// trigger_error( "Notice", E_USER_NOTICE);
		// trigger_error( "Deprecated", E_USER_DEPRECATED);

		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		// SETTINGS
		register_rest_route( $this->namespace, '/update_option', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_update_option' )
		) );
		register_rest_route( $this->namespace, '/all_settings', array(
			'methods' => 'GET',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_all_settings' )
		) );

		// STATS & LISTING
		register_rest_route( $this->namespace, '/stats', array(
			'methods' => 'GET',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_get_stats' )
		) );
		register_rest_route( $this->namespace, '/media', array(
			'methods' => 'GET',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_media' ),
			'args' => array(
				'limit' => array( 'required' => false, 'default' => 10 ),
				'skip' => array( 'required' => false, 'default' => 20 ),
				'filterBy' => array( 'required' => false, 'default' => 'all' ),
				'orderBy' => array( 'required' => false, 'default' => 'id' ),
				'order' => array( 'required' => false, 'default' => 'desc' ),
				'search' => array( 'required' => false ),
				'offset' => array( 'required' => false ),
				'order' => array( 'required' => false ),
			)
		) );
		register_rest_route( $this->namespace, '/analyze', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_analyze' )
		) );
		register_rest_route( $this->namespace, '/get_all_ids', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_get_all_ids' )
		) );

		// ACTIONS
		register_rest_route( $this->namespace, '/set_lock', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_set_lock' )
		) );
		register_rest_route( $this->namespace, '/rename', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_rename' )
		) );
		register_rest_route( $this->namespace, '/undo', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_undo' )
		) );
		register_rest_route( $this->namespace, '/status', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_status' )
		) );

		// LOGS
		register_rest_route( $this->namespace, '/refresh_logs', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'refresh_logs' )
		) );
		register_rest_route( $this->namespace, '/clear_logs', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'clear_logs' )
		) );
	}

	function refresh_logs() {
		$data = "No data.";
		if ( file_exists( MFRH_PATH . '/logs/media-file-renamer.log' ) ) {
			$data = file_get_contents( MFRH_PATH . '/logs/media-file-renamer.log' );
		}
		return new WP_REST_Response( [ 'success' => true, 'data' => $data ], 200 );
	}

	function clear_logs() {
		unlink( MFRH_PATH . '/logs/media-file-renamer.log' );
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	function rest_analyze( $request ) {
		$params = $request->get_json_params();
		$mediaIds = isset( $params['mediaIds'] ) ? (array)$params['mediaIds'] : null;
		$mediaId = isset( $params['mediaId'] ) ? (int)$params['mediaId'] : null;
		$data = array();
		if ( !empty( $mediaIds ) ) {
			foreach ( $mediaIds as $mediaId ) {
				$entry = $this->get_media_status_one( $mediaId );
				array_push( $data, $entry );
			}
		}
		else if ( !empty( $mediaId ) ) {
			$data = $this->get_media_status_one( $mediaId );
		}
		return new WP_REST_Response( [ 'success' => true, 'data' => $data ], 200 );
	}

	function rest_get_all_ids( $request ) {
		global $wpdb;
		$params = $request->get_json_params();
		$unlockedOnly = isset( $params['unlockedOnly'] ) ? (bool)$params['unlockedOnly'] : false;
		if ( $unlockedOnly ) {
			$ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts p 
				LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
				AND pm.meta_key='_manual_file_renaming'
				WHERE post_type='attachment'
				AND post_status='inherit'
				AND pm.meta_value IS NULL"
			);
			error_log(print_r( $ids, 1 ));
		}
		else {
			$ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts p 
				WHERE post_type='attachment'
				AND post_status='inherit'"
			);
		}
		return new WP_REST_Response( [ 'success' => true, 'data' => $ids ], 200 );
	}

	function rest_status( $request ) {
		$params = $request->get_json_params();
		$mediaId = (int)$params['mediaId'];
		$entry = $this->get_media_status_one( $mediaId );
		return new WP_REST_Response( [ 'success' => true, 'data' => $entry ], 200 );
	}

	function rest_rename( $request ) {
		$params = $request->get_json_params();
		$mediaId = (int)$params['mediaId'];
		$filename = isset( $params['filename'] ) ? (string)$params['filename'] : null;
		$res = $this->core->rename( $mediaId, $filename );
		$entry = $this->get_media_status_one( $mediaId );
		return new WP_REST_Response( [ 'success' => !!$res, 'data' => $entry ], 200 );
	}

	function rest_undo( $request ) {
		$params = $request->get_json_params();
		$mediaId = (int)$params['mediaId'];
		$res = $this->core->undo( $mediaId );
		$entry = $this->get_media_status_one( $mediaId );
		return new WP_REST_Response( [ 'success' => !!$res, 'data' => $entry ], 200 );
	}

	function rest_set_lock( $request ) {
		$params = $request->get_json_params();
		$lock = (boolean)$params['lock'];
		$mediaIds = isset( $params['mediaIds'] ) ? (array)$params['mediaIds'] : null;
		$mediaId = isset( $params['mediaId'] ) ? (int)$params['mediaId'] : null;
		$data = null;
		if ( !empty( $mediaIds ) ) {
			foreach ( $mediaIds as $mediaId ) {
				$lock ? $this->core->lock( $mediaId ) : $this->core->unlock( $mediaId );
			}
			$data = 'N/A';
		}
		else if ( !empty( $mediaId ) ) {
			$lock ? $this->core->lock( $mediaId ) : $this->core->unlock( $mediaId );
			$data = $this->get_media_status_one( $mediaId );
		}
		return new WP_REST_Response( [ 'success' => true, 'data' => $data ], 200 );
	}

	/**
	 * Organize the data of the entry.
	 * It is used by get_media_status and get_media_status_one.
	 *
	 * @param [type] $entry
	 * @return void
	 */
	function consolidate_media_status( &$entry ) {
		$entry->ID = (int)$entry->ID;
		$entry->post_parent = !empty( $entry->post_parent ) ? (int)$entry->post_parent : null;
		$entry->post_parent_title = !empty( $entry->post_parent ) ? get_the_title( $entry->post_parent ) : null;
		$entry->metadata = unserialize( $entry->metadata );
		$entry->thumbnail_url = wp_get_attachment_thumb_url( $entry->ID );
		$entry->current_filename = pathinfo( $entry->current_filename, PATHINFO_BASENAME );
		$entry->locked = $entry->locked === '1';
		$entry->pending = $entry->pending === '1';

		$entry->proposed_filename = null;
		if ( !$entry->locked ) {
			$output = null;
			// TODO: We should optimize this check_attachment function one day.
			$this->core->check_attachment( get_post( $entry->ID, ARRAY_A ), $output );
			if ( isset( $output['ideal_filename'] ) ) {
				$entry->ideal_filename = $output['ideal_filename'];
			}
			if ( isset( $output['proposed_filename'] ) ) {
				$entry->proposed_filename = $output['proposed_filename'];
				$entry->proposed_filename_exists = $output['proposed_filename_exists'];
			}
			//error_log( print_r( $output, 1 ) );
		}
		return $entry;
	}

	function count_pending() {
		global $wpdb;
		return (int)$wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts p 
			INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID 
			WHERE pm.meta_key = '_require_file_renaming'"
		);
	}

	function count_renamed() {
		global $wpdb;
		return (int)$wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts p 
			INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID 
			WHERE pm.meta_key = '_original_filename'"
		);
	}

	function count_all() {
		global $wpdb;
		return (int)$wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts p 
			WHERE post_type='attachment'
			AND post_status='inherit'"
		);
	}

	function rest_get_stats() {
		return new WP_REST_Response( [ 'success' => true, 'data' => array(
			'pending' => $this->count_pending(),
			'renamed' => $this->count_renamed(),
			'all' => $this->count_all()
		) ], 200 );
	}

	/**
	 * Get the status for many Media IDs.
	 *
	 * @param integer $skip
	 * @param integer $limit
	 * @return void
	 */
	function get_media_status( $skip = 0, $limit = 10, $filterBy, $orderBy, $order ) {
		global $wpdb;
		// I used this before to gather the metadata in a json object
		// JSON_OBJECTAGG(pm.meta_key, pm.meta_value) as meta
		// That was cool, but I prefer the MAX technique in order to apply filters
		$havingSql = '';
		if ( $filterBy === 'pending' ) {
			$havingSql = 'HAVING pending IS NOT NULL';
		}
		else if ( $filterBy === 'renamed' ) {
			$havingSql = 'HAVING original_filename IS NOT NULL';
		}
		$orderSql = 'ORDER BY p.ID DESC';
		if ($orderBy === 'post_title') {
			$orderSql = 'ORDER BY post_title ' . ( $order === 'asc' ? 'ASC' : 'DESC' );
		}
		else if ($orderBy === 'post_parent') {
			$orderSql = 'ORDER BY post_parent ' . ( $order === 'asc' ? 'ASC' : 'DESC' );
		}
		else if ($orderBy === 'current_filename') {
			$orderSql = 'ORDER BY current_filename ' . ( $order === 'asc' ? 'ASC' : 'DESC' );
		}
		$entries = $wpdb->get_results( 
			$wpdb->prepare( "SELECT p.ID, p.post_title, p.post_parent, 
				MAX(CASE WHEN pm.meta_key = '_wp_attached_file' THEN pm.meta_value END) AS current_filename,
				MAX(CASE WHEN pm.meta_key = '_original_filename' THEN pm.meta_value END) AS original_filename,
				MAX(CASE WHEN pm.meta_key = '_wp_attachment_metadata' THEN pm.meta_value END) AS metadata,
				MAX(CASE WHEN pm.meta_key = '_wp_attachment_image_alt' THEN pm.meta_value END) AS image_alt,
				MAX(CASE WHEN pm.meta_key = '_require_file_renaming' THEN pm.meta_value END) AS pending,
				MAX(CASE WHEN pm.meta_key = '_manual_file_renaming' THEN pm.meta_value END) AS locked
				FROM $wpdb->posts p
				INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
				WHERE post_type='attachment'
					AND (pm.meta_key = '_wp_attached_file' 
						OR pm.meta_key = '_original_filename'
						OR pm.meta_key = '_wp_attachment_metadata'
						OR pm.meta_key = '_wp_attachment_image_alt'
						OR pm.meta_key = '_require_file_renaming'
						OR pm.meta_key = '_manual_file_renaming'
					)
				GROUP BY p.ID
				$havingSql
				$orderSql
				LIMIT %d, %d", $skip, $limit 
			)
		);
		foreach ( $entries as $entry ) {
			$this->consolidate_media_status( $entry );
		}
		return $entries;
	}

	/**
	 * Get the status for many Media IDs.
	 *
	 * @param integer $mediaId
	 * @return void
	 */
	function get_media_status_one( $mediaId ) {
		global $wpdb;
		$entry = $wpdb->get_row( 
			$wpdb->prepare( "SELECT p.ID, p.post_title, p.post_parent,
				MAX(CASE WHEN pm.meta_key = '_wp_attached_file' THEN pm.meta_value END) AS current_filename,
				MAX(CASE WHEN pm.meta_key = '_original_filename' THEN pm.meta_value END) AS original_filename,
				MAX(CASE WHEN pm.meta_key = '_wp_attachment_metadata' THEN pm.meta_value END) AS metadata,
				MAX(CASE WHEN pm.meta_key = '_wp_attachment_image_alt' THEN pm.meta_value END) AS image_alt,
				MAX(CASE WHEN pm.meta_key = '_require_file_renaming' THEN pm.meta_value END) AS pending,
				MAX(CASE WHEN pm.meta_key = '_manual_file_renaming' THEN pm.meta_value END) AS locked
				FROM $wpdb->posts p
				INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
				WHERE p.ID = %d
					AND post_type='attachment'
					AND (pm.meta_key = '_wp_attached_file' 
						OR pm.meta_key = '_original_filename'
						OR pm.meta_key = '_wp_attachment_metadata'
						OR pm.meta_key = '_wp_attachment_image_alt'
						OR pm.meta_key = '_require_file_renaming'
						OR pm.meta_key = '_manual_file_renaming'
					)
				GROUP BY p.ID", $mediaId 
			)
		);
		return $this->consolidate_media_status( $entry );
	}

	function rest_media( $request ) {
		$limit = trim( $request->get_param('limit') );
		$skip = trim( $request->get_param('skip') );
		$filterBy = trim( $request->get_param('filterBy') );
		$orderBy = trim( $request->get_param('orderBy') );
		$order = trim( $request->get_param('order') );
		$entries = $this->get_media_status( $skip, $limit, $filterBy, $orderBy, $order );
		$total = 0;
		if ( $filterBy == 'pending' ) {
			$total = $this->count_pending();
		}
		else if ( $filterBy == 'renamed' ) {
			$total = $this->count_renamed();
		}
		else if ( $filterBy == 'all' ) {
			$total = $this->count_all();
		}
		return new WP_REST_Response( [ 'success' => true, 'data' => $entries, 'total' => $total ], 200 );
	}

	function rest_all_settings() {
		return new WP_REST_Response( [ 'success' => true, 'data' => $this->admin->get_all_options() ], 200 );
	}

	function rest_update_option( $request ) {
		$params = $request->get_json_params();
		try {
			$name = $params['name'];
			$value = is_bool( $params['value'] ) ? ( $params['value'] ? '1' : '' ) : $params['value'];
			$success = update_option( $name, $value );
			if ( !$success ) {
				return new WP_REST_Response([ 'success' => false, 'message' => 'Could not update option.' ], 200 );
			}
			$res = $this->validate_updated_option( $name );
			return new WP_REST_Response([ 'success' => $res['result'], 'message' => $res['message'], 'data' => $value ], 200 );
		} 
		catch (Exception $e) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

	function validate_updated_option( $option_name ) {
		$needsCheckingOptions = [
			'mfrh_auto_rename',
			'mfrh_sync_alt',
			'mfrh_sync_media_title',
			'mfrh_force_rename',
			'mfrh_numbered_files'
		];
		if ( !in_array( $option_name, $needsCheckingOptions ) ) {
			return $this->createValidationResult();
		}

		if ( $option_name === 'mfrh_force_rename' || $option_name === 'mfrh_numbered_files' ) {
			$force_rename = get_option( 'mfrh_force_rename', false );
			$numbered_files = get_option( 'mfrh_numbered_files', false );

			if ( !$force_rename || !$numbered_files ) {
				return $this->createValidationResult();
			}

			update_option( 'mfrh_force_rename', false, false );
			return $this->createValidationResult( false, __( 'Force Rename and Numbered Files cannot be used at the same time. Please use Force Rename only when you are trying to repair a broken install. For now, Force Rename has been disabled.', 'media-file-renamer' ));

		} 
		else if ( $option_name === 'mfrh_auto_rename' || $option_name === 'mfrh_sync_alt' || 
			$option_name ==='mfrh_sync_media_title' ) {
			if ( $this->core->method !== 'alt_text' && $this->core->method !== 'media_title' ) {
				return $this->createValidationResult();
			}

			$sync_alt = get_option( 'mfrh_sync_alt' );
			if ( $sync_alt && $this->core->method === 'alt_text' ) {
				update_option( 'mfrh_sync_alt', false, false );
				return $this->createValidationResult( false, __( 'The option Sync ALT was turned off since it does not make sense to have it with this Auto-Rename mode.', 'media-file-renamer' ));
			}

			$sync_meta_title = get_option( 'mfrh_sync_media_title' );
			if ( $sync_meta_title && $this->core->method === 'media_title' ) {
				update_option( 'mfrh_sync_media_title', false, false );
				return $this->createValidationResult( false, __( 'The option Sync Media Title was turned off since it does not make sense to have it with this Auto-Rename mode.', 'media-file-renamer' ));
			}
		}
		return $this->createValidationResult();
	}

	function createValidationResult( $result = true, $message = null) {
		$message = $message ? $message : __( 'Option updated.', 'media-file-renamer' );
		return ['result' => $result, 'message' => $message];
	}
}

?>