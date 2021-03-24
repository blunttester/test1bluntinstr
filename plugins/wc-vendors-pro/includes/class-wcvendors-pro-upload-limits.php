<?php
/**
 * The upload limits class
 *
 * This is used to define upload limits functions
 *
 * @since      1.6.0
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes
 * @author     Lindeni Mahlalela, WC Vendors
 */

class WCVendors_Pro_Upload_Limits {

	/**
	 * Holds the instance of the class
	 *
	 * @var     object
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	private static $instance = null;

	/**
	 * The user id
	 *
	 * @var     int
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	protected static $user_id = 0;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.6.0
	 * @version  1.6.0
	 * @param    string $wcvendors_pro The name of this plugin.
	 * @param    string $version       The version of this plugin.
	 * @param    bool   $debug         Plugin in debug mode
	 */
	public function __construct( $user_id ) {
		self::$user_id = $user_id;
	}

	/**
	 * Get instance of this class
	 *
	 * @param   object $wcvendors_pro
	 * @param   string $version
	 * @param   bool   $debug
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function get_instance( $user_id ) {
		if ( self::$instance == null ) {
			self::$instance = new self( $user_id );
		} else {
			self::set_user_id( $user_id );
		}

		return self::$instance;
	}

	/**
	 * Get the user id
	 *
	 * @return  int $user_id
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_user_id() {
		if ( 0 == self::$user_id && is_user_logged_in() ) {
			self::$user_id = get_current_user_id();
		}

		return self::$user_id;
	}

	/**
	 * Set the user id
	 *
	 * @param   int $user_id
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public static function set_user_id( $user_id ) {
		self::$user_id = $user_id;
	}

	/**
	 * Limit vendor upload based on settings
	 *
	 * @param   array $file The details of the file to be uploaded
	 * @return  array $file
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function check_upload_limits( $file ) {

		$user_id = $this->get_user_id();

		$message = '';
		if ( $this->disk_limit_reached() ) {
			$message = apply_filters( 'wcv_global_disk_usage_limit_message', __( 'You have reached the total disk usage allowed in your account.', 'wcvendors-pro' ) );
		}

		if ( $this->files_limit_reached() ) {
			$message .= apply_filters( 'wcv_global_file_count_limit_message', __( 'You have reached the total number of files you are allowed to upload.', 'wcvendors-pro' ) );
		}

		if ( ! empty( $message ) ) {
			$file['error'] = $message;
		}

		return $file;
	}

	/**
	 * Get a user's disk usage
	 *
	 * @param   integer $user_id
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_disk_usage( $user_id = 0 ) {

		$user_id = $this->get_user_id();

		if ( 0 == $user_id ) {
			return;
		}

		$disk_usage = get_transient( 'wcv_user_disk_usage_' . $user_id );

		if ( $disk_usage ) {
			return $disk_usage;
		}

		$include_vendor_thumbnails = $this->include_vendor_thumbnails( $user_id );
		$include_global_thumbnails = $this->include_global_thumbnails();

		if ( $include_vendor_thumbnails || $include_global_thumbnails ) {
			$user_media = self::get_media( $user_id );

			$disk_usage = 0;
			if ( ! empty( $user_media ) ) {
				foreach ( $user_media as $media ) {
					$disk_usage += $media['file_size'];

					if ( ! empty( $media['thumbnails'] ) ) {
						foreach ( $media['thumbnails'] as $thumbnail ) {
							$disk_usage += $thumbnail['file_size'];
						}
					}
				}
			}
		}

		set_transient( 'wcv_user_disk_usage_' . $user_id, $disk_usage, 12 * HOUR_IN_SECONDS );

		return $disk_usage;
	}

	/**
	 * Check if user has reached disk usage limit
	 *
	 * @return  bool
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function disk_limit_reached() {
		$user_id = $this->get_user_id();

		if ( 0 == $user_id ) {
			return;
		}

		$vendor_disk_limited = get_user_meta( $user_id, '_wcv_vendor_disk_usage_limit_reached', true );
		$current_disk_usage  = self::get_disk_usage();
		$limit_disk_usage    = $this->get_disk_usage_limit( $user_id );

		if ( $current_disk_usage >= $limit_disk_usage && $limit_disk_usage > 0 || $vendor_disk_limited ) {
			return true;
		} else {
			delete_user_meta( $user_id, '_wcv_vendor_disk_usage_limit_reached' );
		}

		return false;
	}

	/**
	 * Check if user has reached fles count limit
	 *
	 * @return  bool
	 * @since   1.6.0
	 * @version 1.7.6
	 */
	public function files_limit_reached() {

		$user_id = $this->get_user_id();

		if ( 0 == $user_id ) {
			return;
		}

		$vendor_files_limited = get_user_meta( $user_id, '_wcv_vendor_file_count_limit_reached', true );
		$user_media           = self::get_media();
		$current_number_files = ! empty( $user_media ) ? count( $user_media ) : 0;
		$limit_files_count    = $this->get_files_count_limit( $user_id );

		$user_meta  = get_userdata( $user_id );
		$user_roles = $user_meta->roles;
		if ( in_array( 'vendor', $user_roles ) ) {
			if ( $current_number_files >= $limit_files_count && $limit_files_count > 0 || $vendor_files_limited ) {
				return true;
			} else {
				delete_user_meta( $user_id, '_wcv_vendor_file_count_limit_reached' );
			}
		}

		return false;
	}

	/**
	 * Get the file count limit based on vendor overrides or global settings
	 *
	 * @return  int $limit_disk_usage
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_files_count_limit( $user_id = 0 ) {
		$user_id = $user_id == 0 ? $this->get_user_id() : $user_id;

		$user_file_limit = apply_filters(
			'wcv_global_file_count_limit_' . $user_id,
			get_user_meta( $user_id, '_wcv_vendor_file_count_limit', true ),
			$user_id
		);

		$global_file_limit = apply_filters( 'wcv_global_file_count_limit', get_option( 'wcvendors_global_files_count_limit', 0 ) );

		// Prioritize user/vendor level limits
		$limit_files_count = ! empty( $user_file_limit ) && is_numeric( $user_file_limit ) ? $user_file_limit : $global_file_limit;

		return $limit_files_count;
	}

	/**
	 * Get the disk usage limit based on vendor overrides or global settings
	 *
	 * @return  int $limit_disk_usage
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_disk_usage_limit( $user_id = 0 ) {

		$user_id = $user_id == 0 ? $this->get_user_id() : $user_id;

		$vendor_disk_limit = get_user_meta( $user_id, '_wcv_vendor_disk_usage_limit', true );

		$user_disk_limit = apply_filters(
			'wcv_global_disk_usage_limit_' . $user_id,
			is_numeric( $vendor_disk_limit ) ? $vendor_disk_limit * MB_IN_BYTES : 0,
			$user_id
		);

		$global_disk_limit = apply_filters( 'wcv_global_disk_usage_limit', get_option( 'wcvendors_global_disk_usage_limit', 0 ) * MB_IN_BYTES );

		$limit_disk_usage = ! empty( $user_disk_limit ) && is_numeric( $user_disk_limit ) ? $user_disk_limit : $global_disk_limit;

		return $limit_disk_usage;
	}

	/**
	 * Get all media that belongs to a user
	 *
	 * @param   integer $user_id User id.
	 * @return  array   List of media file details
	 * @since   1.6.0
	 * @version 1.7.7
	 */
	public function get_media( $user_id = 0 ) {

		if ( $user_id == 0 ) {
			$user_id = $this->get_user_id();
		}

		if ( 0 == $user_id ) {
			return;
		}

		$user_media = array();

		$user_attachments = get_transient( 'wcv_user_media_' . $user_id );
		if ( $user_attachments ) {
			return apply_filters( 'wcv_user_media_' . $user_id, $user_attachments, $user_id );
		}

		$user_attachments = get_posts(
			apply_filters(
				'wcv_get_user_media_query_args',
				array(
					'post_type'   => 'attachment',
					'post_status' => 'inheret',
					'author'      => $user_id,
					'number'      => -1,
				)
			)
		);

		if ( $user_attachments ) {
			$site_url = site_url();

			foreach ( $user_attachments as $attachment ) {
				$file_path = str_replace( $site_url, ABSPATH, $attachment->guid );
				$file_path = str_replace( "//", "/", $file_path );
				$file_size = file_exists( $file_path ) ? filesize( $file_path ) : 0;

				$include_vendor_thumbnails = $this->include_vendor_thumbnails( $user_id );
				$include_global_thumbnails = $this->include_global_thumbnails();

				$media_thumbnails = array();
				if ( $include_vendor_thumbnails || $include_global_thumbnails ) {

					$media_meta = wp_get_attachment_metadata( $attachment->ID );

					if ( ! empty( $media_meta['sizes'] ) ) {
						foreach ( $media_meta['sizes'] as $thumbnail ) {
							$thumbnail_path     = plugin_dir_path( $file_path ) . $thumbnail['file'];
							$media_thumbnails[] = array(
								'file_path' => $thumbnail_path,
								'file_size' => file_exists( $thumbnail_path ) ? filesize( $thumbnail_path ) : 0,
							);
						}
					}
				}

				$user_media[] = array(
					'media_id'    => $attachment->ID,
					'url'         => $attachment->guid,
					'file_path'   => $file_path,
					'file_size'   => $file_size,
					'post_author' => $attachment->post_author,
					'thumbnails'  => $media_thumbnails,
				);
			}
		}

		$user_media = apply_filters( 'wcv_user_media_' . $user_id, $user_media, $user_id );

		set_transient( 'wcv_user_media_' . $user_id, $user_media, apply_filters( 'wcv_user_media_transient', 2 * HOUR_IN_SECONDS ) );

		return $user_media;
	}

	/**
	 * Check if thumbnails should contribute to limits for this user
	 *
	 * @param   int $user_id
	 * @return  bool
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function include_vendor_thumbnails( $user_id = 0 ) {

		$user_id = $this->get_user_id();

		return wc_string_to_bool(
			apply_filters(
			'wcv_vendor_upload_limits_include_thumbnails',
			get_user_meta( $user_id, '_wcv_vendor_upload_limits_include_thumbnails', true )
		)
			);
	}

	/**
	 * Check if admin has set media thumbnails to contribute to limits
	 *
	 * @return  bool
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function include_global_thumbnails() {
		return wc_string_to_bool(
			apply_filters(
			'wcvendors_upload_limits_include_thumbnails',
			get_option( 'wcvendors_upload_limits_include_thumbnails', 'yes' )
		)
			);
	}

	/**
	 * Format disk usage size.
	 * Return 0 instead of Unlimited if vendor hasn't uploaded  any file.
	 *
	 * @param $size
	 *
	 * @return int|string
	 */
	public function format_disk_usage_size( $size, $require_units = true ) {
		if ( $size == 0 ) {
			return 0;
		}

		return $this->format_disk_size( $size, $require_units );
	}

	/**
	 * Format size into units
	 *
	 * @param   int $size
	 * @return  string The size of the file with units
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function format_disk_size( $size, $require_units = true ) {
	  if ( $size == 0 ) {
			return __( 'Unlimited', 'wcvendors-pro' );
		}

		$units = explode( ' ', 'B KB MB GB TB PB' );

		$mod = 1024;

		for ( $i = 0; $size > $mod; $i++ ) {
		$size /= $mod;
		}

		$endIndex = strpos( $size, '.' ) + 3;

		return $require_units ? substr( $size, 0, $endIndex ) . ' ' . $units[ $i ] : substr( $size, 0, $endIndex );
	}
}
