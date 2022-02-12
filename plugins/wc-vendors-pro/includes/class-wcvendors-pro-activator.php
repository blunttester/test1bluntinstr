<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes
 * @author     Jamie Madden <support@wcvendors.com>
 * @link       http://www.wcvendors.com
 */
class WCVendors_Pro_Activator {

	/**
	 * The vendor feedback tablename
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WCVendors_Pro_Activator $feedback_tbl_name Vendor Feedback table name
	 */
	public static $feedback_tbl_name = 'wcv_feedback';

	/**    Updates to be run **/
	private static $db_updates = array(
		'1.5.0' => array(
			'wcv_migrate_pro_settings',
			'wcv_update_150_db_version',
		),
		'1.5.3' => array(
			'wcv_fix_product_form',
			'wcv_update_153_db_version',
		),
		'1.5.4' => array(
			'wcv_fix_product_standard_form',
			'wcv_update_154_db_version',
		),
		'1.5.5' => array(
			'wcv_update_inconsistent_option_names',
			'add_defaults_google_maps_options',
			'wcv_add_variation_type_option',
			'wcv_add_variation_dropdown_type_option',
			'wcv_add_color_filter_options',
			'update_facebook_image_option',
		),
		'1.5.7' => array(
			'add_defaults_google_maps_options',
		),
		'1.5.8' => array(
			'add_vacation_disable_cart_defaults',
			'wcv_add_enable_media_option',
		),
		'1.5.9' => array(
			'add_hide_vendor_store_notice_option',
			'add_store_total_sales_options',
		),
		'1.6.0' => array(
			'add_options_for_vendor_shipping_type',
			'add_sync_reviews_option',
			'add_upload_limits',
			'add_product_seo_options',
		),
		'1.6.5' => array(
			'add_variation_required_settings',
			'delete_google_plus_options',
		),
		'1.7.3' => array(
			'update_order_note_settings_option',
		),
		'1.7.4' => array(
			'update_hide_signup_and_settings_shipping_option',
		),
		'1.7.5' => array(
			'update_hide_product_shipping_option',
		),
		'1.7.6' => array(
			'default_vendor_shipping',
		),
		'1.7.7' => array(
			'update_default_store_notice_html_option',
		),
		'1.7.9' => array(
			'update_google_map_address',
		),
	);

	/**
	 * Background update class.
	 *
	 * @var object
	 */
	private static $background_updater;

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 * @todo     check which version of WC Vendors is installed.
	 */
	public static function activate( $plugin_file ) {

		$php_version = phpversion();

		require_once 'wcv-functions.php';

		/**
		 *  Requires PHP 5.4.0 to function
		 */
		if ( version_compare( $php_version, '5.4', '<' ) ) {
			deactivate_plugins( $plugin_file );
			wp_die( __( 'WC Vendors Pro requires PHP 5.4 or newer to function.  Please contact your webhosting company and have them upgrade your hosting account to a version of PHP 5.4 or newer.', 'wcvendors-pro' ) );
		}

		/**
		 *  Requires woocommerce to be installed and active
		 */
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( $plugin_file );
			wp_die( __( 'WC Vendors Pro requires WooCommerce to run. Please install WooCommerce and activate before attempting to activate again.', 'wcvendors-pro' ) );
		}

		/**
		 *  Requires WC Vendors to be installed and active
		 */
		if ( ! class_exists( 'WC_Vendors' ) ) {
			deactivate_plugins( $plugin_file );
			add_action( 'admin_notices', 'wcvendors_required_notice' );
		}

		if ( version_compare( WCV_VERSION, '2.3.0', '<' ) ) {
			deactivate_plugins( $plugin_file );
			add_action( 'admin_notices', 'wcvendors_2_required_notice' );
		}

		self::install();
	}

	/**
	 * Create the vendor ratings table
	 *
	 * Stores relevant vendor ratings feedback from customer orders.
	 *
	 * @since    1.0.0
	 */
	public static function create_tables() {

		global $wpdb;

		$table_name      = $wpdb->prefix . self::$feedback_tbl_name;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			rating bigint(20) NOT NULL,
			order_id bigint(20) NOT NULL,
			vendor_id bigint(20) NOT NULL,
			product_id bigint(20) NOT NULL,
			customer_id bigint(20) NOT NULL,
			rating_title varchar(255),
			comments LONGTEXT,
			postdate timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );
	}

	/**
	 *  Create the new pages for pro
	 *
	 * @since    1.0.0
	 */
	public static function create_pages() {

		// Nest the feedback page under my-account
		$my_account_page = wc_get_page_id( 'myaccount' );

		$dashboard_page_id = wc_create_page(
			esc_sql( _x( 'dashboard', 'Page slug', 'wcvendors-pro' ) ),
			'wcvendors_dashboard_page_id',
			_x( 'Pro Dashboard', 'Page title', 'wcvendors-pro' ),
			'[wcv_pro_dashboard]',
			''
		);

		$feedback_page_id = wc_create_page(
			esc_sql( _x( 'feedback', 'Page slug', 'wc-vendors' ) ),
			'wcvendors_feedback_page_id',
			_x( 'Feedback', 'Page title', 'wc-vendors' ),
			'[wcv_feedback_form]',
			$my_account_page
		);

	} // create_pages()

	/**
	 *
	 *
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'wcvendors_pro_version', null ) !== WCV_PRO_VERSION ) {
			self::install();
			do_action( 'wcvendors_pro_updated' );
		}
	}

	/**
	 * Grouped functions for installing the WC Vendor Pro plugin.
	 *
	 * @version 1.7.6
	 * @since   1.7.6
	 */
	public static function install() {

		// Check if we are not already running this routine.
		if ( wc_string_to_bool( get_transient( 'wcvendors_pro_installing' ) ) ) {
			return;
		}

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin-notices.php';

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wcvendors_pro_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		wc_maybe_define_constant( 'WCV_PRO_INSTALLING', true );

		self::remove_admin_notices();
		self::create_tables();
		self::maybe_run_setup_wizard();
		self::update_wcvendors_pro_version();
		self::maybe_update_db_version();
		delete_transient( 'wcvendors_pro_installing' );

		do_action( 'wcvendors_flush_rewrite_rules' );
		do_action( 'wcvendors_pro_installed' );

	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 2.0.0
	 */
	private static function remove_admin_notices() {
		WCVendors_Pro_Admin_Notices::remove_all_notices();
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		include_once dirname( __FILE__ ) . '/class-wcvendors-pro-background-updater.php';
		self::$background_updater = new WCVendors_Pro_Background_Updater();
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  1.5.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'wcvendors_pro_db_version', null );
		$logger             = wc_get_logger();
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$logger->info(
						sprintf( 'Queuing %s - %s', $version, $update_callback ),
						array( 'source' => 'wcvendors_pro_db_updates' )
					);
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Update WC version to current.
	 */
	private static function update_wcvendors_pro_version() {
		delete_option( 'wcvendors_pro_version' );
		add_option( 'wcvendors_pro_version', WCV_PRO_VERSION );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'wcvendors_pro_db_version' );
		add_option( 'wcvendors_pro_db_version', is_null( $version ) ? WCV_PRO_VERSION : $version );
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {

		if ( ! empty( $_GET['do_update_wcvendors_pro'] ) ) {
			self::update();
			WCVendors_Pro_Admin_Notices::add_notice( 'update' );
		}

		if ( ! empty( $_GET['force_update_wcvendors_pro'] ) ) {
			self::update();
			wp_safe_redirect( admin_url( 'admin.php?page=wcv-settings' ) );
			exit;
		}
	}

	/**
	 * Is this a brand new WC install?
	 *
	 * @since 1.5.2
	 * @return boolean
	 */
	public static function is_new_install() {
		return is_null( get_option( 'wcvendors_pro_version', null ) ) && is_null( get_option( 'wcvendors_pro_db_version', null ) );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 1.5.2
	 */
	public static function maybe_run_setup_wizard() {
		if ( apply_filters( 'wcvendors_pro_enable_setup_wizard', self::is_new_install() ) ) {
			WCVendors_Pro_Admin_Notices::add_notice( 'install' );
		}
	}

	private static function needs_db_update() {
		$current_db_version = get_option( 'wcvendors_pro_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );

	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 1.5.2
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			if ( apply_filters( 'wcvendors_pro_enable_auto_update_db', false ) ) {
				self::init_background_updater();
				self::update();
			} else {
				WCVendors_Pro_Admin_Notices::add_notice( 'update' );
			}
		} else {
			self::update_db_version();
		}
	}
}
