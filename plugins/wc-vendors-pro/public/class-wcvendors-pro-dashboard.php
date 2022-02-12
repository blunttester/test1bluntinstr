<?php
/**
 * The main WCVendors Pro Dashboard class
 *
 * This is the main controller class for the dashboard, all actions are defined in this class.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 */

/**
 * Class WCVendors_Pro_Dashboard
 */
class WCVendors_Pro_Dashboard {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wcvendors_pro The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $dashboard_pages an array of dashboard pages
	 */
	private $dashboard_pages = array();

	/**
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );

	}

	/**
	 * Load the dasboard based on the query vars loaded.
	 *
	 * @since    1.0.0
	 */
	public function load_dashboard() {

		$dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id', array() );

		if ( empty( $dashboard_page_ids ) ) {
			echo esc_html__( '<h2>Please ensure you have set a page for the Pro Dashboard.</h2>', 'wcvendors-pro' );
			exit;
		}

		ob_start();

		global $wp;

		if ( isset( $wp->query_vars['object'] ) ) {

			$type     = get_query_var( 'object' );
			$action   = get_query_var( 'action' );
			$id       = get_query_var( 'object_id' );
			$template = get_query_var( 'template' );
			$custom   = get_query_var( 'custom' );

			$this->load_page( $type, $action, $id, $template, $custom );

		} else {

			$this->load_page();
		}

		return ob_get_clean();

	}

	/**
	 * Output the requested page for the dashboard
	 *
	 * @since    1.0.0
	 *
	 * @param    string $object object type.
	 * @param    string $action page action.
	 * @param    int    $object_id the object's post id.
	 * @param    string $template template file.
	 * @param    string $custom custom page.
	 */
	public function load_page( $object = 'dashboard', $action = '', $object_id = null, $template = null, $custom = null ) {

		// Permission check for all dashboard pages.
		if ( ! $this->can_view_dashboard() ) {
			return false;
		}

		// Has the page been disabled ?
		if ( ! $this->page_disabled() ) {
			$return_url = $this->get_dashboard_page_url();
			wc_get_template( 'permission.php', array( 'return_url' => $return_url ), 'wc-vendors/dashboard/', $this->base_dir . 'templates/dashboard/' );

			return false;
		}

		// Does the user own this object ?
		if ( $object_id != null ) {

			if ( $this->check_object_permission( $object, $object_id ) == false ) {
				$return_url = $this->get_dashboard_page_url();
				wc_get_template( 'permission.php', array( 'return_url' => $return_url ), 'wc-vendors/dashboard/', $this->base_dir . 'templates/dashboard/' );

				return false;
			}
		}

		// Include the dashboard wrapper.
		include_once apply_filters( 'wcvendors_pro_dashboard_open_path', 'partials/wcvendors-pro-dashboard-open.php' );

		do_action_deprecated( 'wcv_pro_before_dashboard', array(), '1.7.10', 'wcvendors_pro_before_dashboard' );
		do_action( 'wcvendors_pro_before_dashboard' );

		// Create the menu.
		$this->create_nav();

		// Print woocommerce notices.
		wc_print_notices();

		// Vendor Store Notice.
		$vendor_dashboard_notice = get_option( 'wcvendors_vendor_dashboard_notice', false );

		if ( $vendor_dashboard_notice ) {

			wc_get_template(
				'dashboard-notice.php',
				array(
					'vendor_dashboard_notice' => $vendor_dashboard_notice,
					'notice_type'             => 'message',
				),
				'wc-vendors/dashboard/',
				$this->base_dir . '/templates/dashboard/'
			);
		}

		do_action_deprecated( 'wcv_pro_after_dashboard_nav', array(), '1.7.10', 'wcvendors_pro_after_dashboard_nav' );
		do_action( 'wcvendors_pro_after_dashboard_nav' );

		// if action is set send to edit page with or without object_id else list type.
		if ( 'edit' == $action ) {

			$template_name = '';
			$custom_pages  = self::get_custom_pages();
			$base_dir      = ( array_key_exists( $object, $custom_pages ) ) ? $custom_pages[ $object ]['base_dir'] : $this->base_dir . 'templates/dashboard/';

			$base_dir = apply_filters( 'wcv_dashboard_template_base_dir', $base_dir, $object, $action, $template );

			// Load the form template based on options in backend.
			$template_name = apply_filters( 'wcv_dashboard_template_name', ( 'product' == $object && ! empty( $template ) ) ? $object . '-' . $template : $object . '-' . $action, $object, $template, $action );

			wc_get_template(
				$template_name . '.php',
				array(
					'action'    => $action,
					'template'  => $template,
					'object_id' => $object_id,
				),
				'wc-vendors/dashboard/',
				$base_dir
			);

		} else {

			// Load the custom template.
			if ( ! empty( $custom ) ) {

				$custom_pages = self::get_custom_pages();

				wc_get_template(
					$custom_pages[ $custom ]['template_name'] . '.php',
					$custom_pages[ $custom ]['args'],
					'wc-vendors/dashboard/',
					$custom_pages[ $custom ]['base_dir']
				);

			} else {

				// If the object is a post type then generate a table, otherwise load the custom template.
				if ( post_type_exists( $object ) ) {

					// Use the internal table generator to create object list.
					$object_table = new WCVendors_Pro_Table_Helper( $this->wcvendors_pro, $this->version, $object, $object, get_current_user_id() );
					$object_table->display();

				} else {

					switch ( $object ) {
						case 'order':
							$this->load_order_page();
							break;
						case 'rating':
							$this->load_rating_page();
							break;
						case 'settings':
							$this->load_settings_page();
							break;
						case 'dashboard':
							$this->dashboard_quick_links();
							$store_report = new WCVendors_Pro_Reports_Controller( $this->wcvendors_pro, $this->version, $this->debug );
							$store_report->report_init();
							$store_report->display();
							break;
						default:
							do_action( 'wcv_pro_dashboard_custom_page', $object, $object_id, $template, $custom );
							break;
					}
				}
			}
		}

		do_action( 'wcv_pro_after_dashboard' );

		include_once apply_filters( 'wcvendors_pro_dashboard_close_path', 'partials/wcvendors-pro-dashboard-close.php' );

	}

	/**
	 * Generate the page URL based on the dashboard page id set in options
	 *
	 * @since   1.0.0
	 * @version 1.5.4
	 *
	 * @param   string $page_type page type to output
	 */
	public static function get_dashboard_page_url( $page = '' ) {

		$dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id', array() );
		$dashboard_page_id  = reset( $dashboard_page_ids );
		$dashboard_url      = apply_filters( 'wcv_my_account_dashboard_url', get_permalink( $dashboard_page_id ) );

		return $dashboard_url . $page;

	}

	/**
	 * Provide quick links on the dashboard to reduce click through
	 *
	 * @since    1.1.5
	 */
	public function get_dashboard_quick_links() {

		$products_disabled  = wc_string_to_bool( get_option( 'wcvendors_product_management_cap', 'no' ) );
		$coupons_disabled   = wc_string_to_bool( get_option( 'wcvendors_shop_coupon_management_cap', 'no' ) );
		$lock_edit_products = ( get_user_meta( get_current_user_id(), '_wcv_lock_edit_products_vendor', true ) == 'yes' ) ? true : false;
		$lock_new_products  = ( get_user_meta( get_current_user_id(), '_wcv_lock_new_products_vendor', true ) == 'yes' ) ? true : false;

		$quick_links      = array();
		$add_product_link = WCVendors_Pro_Product_Controller::get_default_product_template();
		$product_ids      = WCVendors_Pro_Vendor_Controller::get_products_by_id( get_current_user_id() );

		if ( empty( $product_ids ) ) {
			$coupons_disabled = true;
		}
		if ( ! wc_coupons_enabled() ) {
			$coupons_disabled = true;
		}

		if ( ! $products_disabled ) {
			$quick_links['product'] = array(
				'url'   => apply_filters( 'wcv_add_product_url', self::get_dashboard_page_url( $add_product_link['url_path'] ) ),
				'label' => __( 'Add product', 'wcvendors-pro' ),
			);
		}
		if ( ! $coupons_disabled ) {
			$quick_links['shop_coupon'] = array(
				'url'   => self::get_dashboard_page_url( 'shop_coupon/edit' ),
				'label' => __( 'Add coupon', 'wcvendors-pro' ),
			);
		}

		if ( $lock_edit_products || $lock_new_products ) {
			unset( $quick_links['product'] );
		}

		return apply_filters( 'wcv_dashboard_quick_links', $quick_links );

	}

	/**
	 * Get usage stats array.
	 *
	 * @return array
	 */
	public function get_dashboard_usage_stats() {
		$user_id = get_current_user_id();
		$limits  = WCVendors_Pro_Upload_Limits::get_instance( $user_id );

		$stats = array();
		if ( wc_string_to_bool( get_option( 'wcvendors_dashboard_show_disk_usage', 'no' ) ) ) {
			$disk_limit = $limits->format_disk_size( $limits->get_disk_usage_limit() );
			$disk_usage = $limits->format_disk_usage_size( $limits->get_disk_usage() );

			$stats['disk-limit'] = array(
				'usage'    => $disk_usage,
				'limit'    => $disk_limit,
				'over'     => $limits->disk_limit_reached(),
				'icon'     => 'wcv-icon-hdd',
				'template' => '%s / %s',
			);
		}
		if ( wc_string_to_bool( get_option( 'wcvendors_dashboard_show_files_usage', 'no' ) ) ) {
			$files_limit = $limits->get_files_count_limit();
			$files_limit = $files_limit == 0 ? __( 'Unlimited', 'wcvendors-pro' ) : $files_limit;
			$files       = $limits->get_media();
			$files_usage = ! empty( $files ) ? count( $files ) : 0;

			$stats['file-limit'] = array(
				'usage'    => $files_usage,
				'limit'    => $files_limit,
				'over'     => $limits->files_limit_reached(),
				'icon'     => 'wcv-icon-file-alt',
				'template' => __( '%1$s / %2$s files', 'wcvendors-pro' ),
			);
		}

		/**
		 * Hook to this filter to change the stat buttons.
		 */
		return apply_filters( 'wcvendors_dashboard_usage_stats', $stats, $user_id );
	}

	/**
	 * Provide quick links on the dashboard to reduce click through
	 *
	 * @since    1.1.5
	 */
	public function dashboard_quick_links() {

		$quick_links = $this->get_dashboard_quick_links();
		$stats       = $this->get_dashboard_usage_stats();

		wc_get_template(
			'quick-links.php',
			array(
				'quick_links' => $quick_links,
				'stats'       => $stats,
			),
			'wc-vendors/dashboard/',
			$this->base_dir . 'templates/dashboard/'
		);

	}

	/**
	 * Available dashboard urls for front end functionality
	 *
	 * @since    1.0.0
	 */
	public function get_dashboard_pages() {

		$disable_duplicate  = ! wc_string_to_bool( get_option( 'wcvendors_capability_product_duplicate', 'no' ) );
		$lock_edit_products = get_user_meta( get_current_user_id(), '_wcv_lock_edit_products_vendor', true );

		$this->dashboard_pages['product'] = array(
			'slug'    => 'product',
			'id'      => 'product',
			'label'   => __( 'Products', 'wcvendors-pro' ),
			'actions' => array(
				'edit'      => __( ' Edit', 'wcvendors-pro' ),
				'duplicate' => __( ' Duplicate', 'wcvendors-pro' ),
				'delete'    => __( ' Delete', 'wcvendors-pro' ),
			),
		);

		if ( $disable_duplicate || $lock_edit_products ) {
			unset( $this->dashboard_pages['product']['actions']['duplicate'] );
		}

		$this->dashboard_pages['order'] = array(
			'slug'    => 'order',
			'id'      => 'order',
			'label'   => __( 'Orders', 'wcvendors-pro' ),
			'actions' => array(),
		);

		$this->dashboard_pages['settings'] = array(
			'slug'    => 'settings',
			'id'      => 'settings',
			'label'   => __( 'Settings', 'wcvendors-pro' ),
			'actions' => array(),
		);

		$this->dashboard_pages['rating'] = array(
			'slug'    => 'rating',
			'id'      => 'rating',
			'label'   => __( 'Ratings', 'wcvendors-pro' ),
			'actions' => array(),
		);

		if ( 'yes' == get_option( 'woocommerce_enable_coupons' ) ) {

			$this->dashboard_pages['shop_coupon'] = array(
				'slug'    => 'shop_coupon',
				'id'      => 'shop_coupon',
				'label'   => __( 'Coupons', 'wcvendors-pro' ),
				'actions' => array(
					'edit'   => __( 'Edit', 'wcvendors-pro' ),
					'delete' => __( 'Delete', 'wcvendors-pro' ),
				),
			);

		}

		return apply_filters( 'wcv_pro_dashboard_urls', $this->dashboard_pages );

	}

	/**
	 * Load the orders table
	 *
	 * @since    1.0.0
	 */
	public function load_order_page() {

		$wcvendors_pro_order_controller = new WCVendors_Pro_Order_Controller( $this->wcvendors_pro, $this->version, $this->debug );
		$wcvendors_pro_order_controller->display();

	}

	/**
	 * Load the ratings page
	 *
	 * @since    1.0.0
	 */
	public function load_rating_page() {
		$wcvendors_pro_rating_controller = new WCVendors_Pro_Ratings_Controller( $this->wcvendors_pro, $this->version, $this->debug );
		$wcvendors_pro_rating_controller->display();

	}

	/**
	 * Load the store settings page
	 *
	 * @since    1.1.0
	 */
	public function load_settings_page() {

		$vendor_id = get_current_user_id();

		$store_name              = get_user_meta( $vendor_id, 'pv_shop_name', true );
		$store_description       = get_user_meta( $vendor_id, 'pv_shop_description', true );
		$shipping_disabled       = wc_string_to_bool( get_option( 'wcvendors_shipping_management_cap', 'no' ) );
		$shipping_methods        = WC()->shipping->load_shipping_methods();
		$shipping_method_enabled = ( array_key_exists( 'wcv_pro_vendor_shipping', $shipping_methods ) && $shipping_methods['wcv_pro_vendor_shipping']->enabled == 'yes' ) ? true : 0;
		$shipping_details        = get_user_meta( get_current_user_id(), '_wcv_shipping', true );

		wc_get_template(
			'store-settings.php',
			array(
				'store_name'              => $store_name,
				'store_description'       => $store_description,
				'shipping_disabled'       => $shipping_disabled,
				'shipping_method_enabled' => $shipping_method_enabled,
				'shipping_details'        => $shipping_details,
			),
			'wc-vendors/dashboard/',
			$this->base_dir . 'templates/dashboard/'
		);
	}

	/**
	 * Check object permission to see if the vendor owns the object (this is to stop people messing with URLs)
	 *
	 * @since    1.0.0
	 * @version  1.3.7
	 *
	 * @param    string $object the object type to test.
	 * @param    int    $post_id   post id to check.
	 */
	public static function check_object_permission( $object, $post_id ) {

		$can_edit_live = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit', 'no' ) );
		$edit_status   = apply_filters( 'wcv_edit_object_status', array( 'draft', 'pending' ) );
		$post_status   = get_post_status( $post_id );
		$can_edit      = in_array( $post_status, $edit_status, true );

		if ( ! $can_edit_live ) {
			$can_edit_live = $can_edit ? true : false;
		}

		switch ( $object ) {
			// Product permissions.
			case 'product':
				return ( $can_edit_live && WCV_Vendors::get_vendor_from_product( $post_id ) == get_current_user_id() ) ? true : false;
			case 'shop_coupon':
				return ( WCVendors_Pro_Vendor_Controller::get_vendor_from_object( $post_id ) != get_current_user_id() ) ? false : true;
			// Dashboard.
			default:
				return true;
		}

	}

	/**
	 * Check permission before the page loads
	 *
	 * @since    1.0.0
	 */
	public function check_permission() {

		$current_page_id = get_the_ID();

		if ( wcv_is_dashboard_page( $current_page_id ) ) {

			if ( ! is_user_logged_in() ) {

				$my_account_page = wc_get_page_id( 'myaccount' );

				if ( ! is_string( get_post_status( $my_account_page ) ) ) {
					/* translators: %s the label for vendors. */
					wc_add_notice( sprintf( __( '<h2>Please contact the website administrator and instruct them that in order for the %s Dashboard to work for logged out users, they must have their My Account page configured and set properly in their WooCommerce settings.</h2>', 'wcvendors-pro' ), wcv_get_vendor_name() ), 'error' );
				} else {
					wp_safe_redirect( apply_filters( 'wcv_login_redirect', get_permalink( wc_get_page_id( 'myaccount' ) ) ), 302 );
					exit;
				}
			}
		}

	}

	/**
	 * Can the current user view the dashboard ?
	 *
	 * @since    1.0.0
	 */
	public function can_view_dashboard() {

		if ( ! is_user_logged_in() ) {
			return false;
		} elseif ( ! WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			// Include the dashboard wrapper.
			include_once apply_filters( 'wcvendors_pro_dashboard_open_path', 'partials/wcvendors-pro-dashboard-open.php' );

			if ( WCVendors_Pro_Vendor_Controller::is_pending_vendor( get_current_user_id() ) ) {
				$vendor_pending_notice = get_option( 'wcvendors_vendor_pending_notice', WCVendors_Pro_Admin_Settings::get_default_pending_vendor_notice() );
				wc_get_template( 'vendor-pending-notice.php', array( 'vendor_pending_notice' => $vendor_pending_notice ), 'wc-vendors/front/', $this->base_dir . '/templates/front/' );

				return false;
			} elseif ( ! current_user_can( 'administrator' ) ) {
				$vendor_signup_notice = get_option( 'wcvendors_vendor_signup_notice', '' );
				// Load the new sign up form template.
				wc_get_template(
					'vendor-signup-form.php',
					array(
						'vendor_signup_notice' => $vendor_signup_notice,
						'shipping_details'     => array(),
					),
					'wc-vendors/front/',
					$this->base_dir . '/templates/front/'
				);

				return false;
			} else {
				echo esc_html__( 'Admins cannot apply to be vendors. ', 'wcvendors-pro' );

				return false;
			}

			// Close the dashboard wrapper.
			include_once apply_filters( 'wcvendors_pro_dashboard_close_path', 'partials/wcvendors-pro-dashboard-close.php' );
		}

		return true;

	}

	/**
	 * Add the query vars for the rewrirte rules add_query_vars function.
	 *
	 * @access        public
	 * @since         1.0.0
	 *
	 * @param        array $vars query vars array.
	 * @return       array $vars new query vars.
	 */
	public function add_query_vars( $vars ) {

		$vars[] = 'object';
		$vars[] = 'object_id';
		$vars[] = 'action';
		$vars[] = 'template';
		$vars[] = 'custom';

		return $vars;

	}

	/**
	 * Get any custom pages defined by integrations
	 *
	 * @since   1.4.0
	 * @version 1.4.0
	 * @return array $pages custom page routes and template information
	 */
	public static function get_custom_pages() {

		return apply_filters( 'wcv_dashboard_custom_pages', array() );
	}

	/**
	 * Dashboard rewrite rules
	 *
	 * @since    1.0.0
	 *
	 * @param  array $rules rules array.
	 */
	public function rewrite_rules( $rules ) {

		$dashboard_page_ids = get_option( 'wcvendors_dashboard_page_id', array() );

		if ( ! is_array( $dashboard_page_ids ) && ! empty( $dashboard_page_ids ) ) {
			$dashboard_page_ids = (array) $dashboard_page_ids;
		}

		// If the dashboard page hasn't been set, don't create the re-write rules.
		if ( ! empty( $dashboard_page_ids ) ) {

			foreach ( $dashboard_page_ids as $dashboard_page_id ) {

				$_post               = get_post( $dashboard_page_id );
				$dashboard_page_slug = $_post->post_name;

				if ( $_post->post_parent ) {
					$_parent_slug_prefix = get_post( $_post->post_parent )->post_name;
					$dashboard_page_slug = $_parent_slug_prefix . '/' . $dashboard_page_slug;
				}

				$pages        = self::get_dashboard_pages();
				$custom_pages = self::get_custom_pages();

				foreach ( $pages as $page ) {
					// Type Rule.
					$type_rule = array(
						$dashboard_page_slug . '/' . $page['slug'] . '?$'             => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'],
						$dashboard_page_slug . '/' . $page['slug'] . '/page/([0-9]+)' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'] . '&paged=$matches[1]',
					);
					$rules     = $type_rule + $rules;

					// Allow custom rules to be added
					if ( ! empty( $custom_pages ) ) {

						foreach ( $custom_pages as $custom_page ) {

							// check if a parent object has been defined.
							if ( array_key_exists( 'parent', $custom_page ) ) {
								$custom_rule = array( $dashboard_page_slug . '/' . $custom_page['parent'] . '/' . $custom_page['slug'] . '?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $custom_page['parent'] . '&custom=' . $custom_page['slug'] );
							} else {
								$custom_rule = array( $dashboard_page_slug . '/' . $custom_page['slug'] . '?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $custom_page['slug'] );
							}

							$rules = $custom_rule + $rules;

							// Register the actions for the custom page.
							if ( array_key_exists( 'actions', $custom_page ) ) {

								foreach ( $custom_page['actions'] as $action => $label ) {

									// Actions Rule.
									$action_rule = array( $dashboard_page_slug . '/' . $custom_page['slug'] . '/' . $action . '?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $custom_page['slug'] . '&action=' . $action );
									$id_rule     = array( $dashboard_page_slug . '/' . $custom_page['slug'] . '/' . $action . '/([0-9]+)?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $custom_page['slug'] . '&action=' . $action . '&object_id=$matches[1]' );
									$rules       = $action_rule + $id_rule + $rules;
								}
							}
						}
					}

					if ( is_array( $page['actions'] ) ) {

						foreach ( $page['actions'] as $action => $label ) {
							// Actions Rule.
							$action_rule   = array( $dashboard_page_slug . '/' . $page['slug'] . '/' . $action . '?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'] . '&action=' . $action );
							$template_rule = array( $dashboard_page_slug . '/' . $page['slug'] . '/([^/]*)/' . $action . '?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'] . '&template=$matches[1]&action=' . $action );
							// Id parsed ?
							$id_rule          = array( $dashboard_page_slug . '/' . $page['slug'] . '/' . $action . '/([0-9]+)?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'] . '&action=' . $action . '&object_id=$matches[1]' );
							$template_id_rule = array( $dashboard_page_slug . '/' . $page['slug'] . '/([^/]*)/' . $action . '/([0-9]+)?$' => 'index.php?pagename=' . $dashboard_page_slug . '&object=' . $page['slug'] . '&template=$matches[1]&action=' . $action . '&object_id=$matches[2]' );

							$rules = $action_rule + $template_rule + $id_rule + $template_id_rule + $rules;
						}
					}
				}
			}
		}

		return apply_filters( 'wcv_dashboard_rewrite_rules', $rules );

	}

	/**
	 * Create the dashboard navigation from available pages.
	 *
	 * @since    1.0.0
	 * @todo     Have this menu output better
	 */
	public function create_nav() {

		$pages = self::get_dashboard_pages();

		$current_page = get_query_var( 'object' );

		$products_disabled  = wc_string_to_bool( get_option( 'wcvendors_product_management_cap', 'no' ) );
		$orders_disabled    = wc_string_to_bool( get_option( 'wcvendors_order_management_cap', 'no' ) );
		$coupons_disabled   = wc_string_to_bool( get_option( 'wcvendors_shop_coupon_management_cap', 'no' ) );
		$ratings_disabled   = wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) );
		$settings_disabled  = wc_string_to_bool( get_option( 'wcvendors_settings_management_cap', 'no' ) );
		$viewstore_disabled = wc_string_to_bool( get_option( 'wcvendors_view_store_cap', 'no' ) );
		$show_logout        = wc_string_to_bool( get_option( 'wcvendors_dashboard_show_logout', 'no' ) );
		$vertical_menu      = wc_string_to_bool( get_option( 'wcvendors_use_vertical_menu', 'no' ) );

		if ( $products_disabled ) {
			unset( $pages['product'] );
		}
		if ( $orders_disabled ) {
			unset( $pages['order'] );
		}
		if ( $coupons_disabled ) {
			unset( $pages['shop_coupon'] );
		}
		if ( $ratings_disabled ) {
			unset( $pages['rating'] );
		}
		if ( $settings_disabled ) {
			unset( $pages['settings'] );
		}

		// Add dashboard home to the pages array.
		$dashboard_home = apply_filters(
			'wcv_dashboard_home_url',
			array(
				'label' => __( 'Dashboard', 'wcvendors-pro' ),
				'slug'  => '',
			)
		);

		if ( ! $viewstore_disabled ) {
			$store_url = apply_filters(
				'wcv_dashboard_view_store_url',
				array(
					'label' => __( 'View store', 'wcvendors-pro' ),
					'id'    => 'view-store',
					'slug'  => WCVendors_Pro_Vendor_Controller::get_vendor_store_url( get_current_user_id() ),
				)
			);
			if ( wc_string_to_bool( get_option( 'wcvendors_dashboard_view_store_new_window', 'no' ) ) ) {
				$store_url['target'] = '_blank';
			}
			$pages['view_store'] = $store_url;
		}

		if ( $show_logout ) {
			$store_url = apply_filters(
				'wcv_dashboard_show_logout',
				array(
					'label' => __( 'Logout', 'wcvendors-pro' ),
					'id'    => 'logout',
					'slug'  => wc_logout_url(),
				)
			);

			$pages['logout'] = $store_url;
		}

		$pages          = array_merge( array( 'dashboard_home' => $dashboard_home ), $pages );
		$pages          = apply_filters( 'wcv_dashboard_pages_nav', $pages );
		$nav_class      = apply_filters( 'wcv_dashboard_nav_class', '' );
		$menu_dir_class = ( $vertical_menu ) ? 'vertical' : 'horizontal';
		$menu_dir_size  = ( $vertical_menu ) ? 'all-20 small-100 medium-100' : 'all-100';

		// Move this into a template.
		$menu_wrapper_start = apply_filters( 'wcv_dashboard_nav_wrapper_start', '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="' . $menu_dir_size . '"><nav class="wcv-navigation ' . $nav_class . ' "><ul class="menu ' . $menu_dir_class . '  black">' );

		echo $menu_wrapper_start;

		foreach ( $pages as $page ) {

			if ( filter_var( $page['slug'], FILTER_VALIDATE_URL ) === false ) {
				$page_url = $this->get_dashboard_page_url( $page['slug'] );
			} else {
				$page_url = $page['slug'];
			}

			$class      = ( $current_page === $page['slug'] ) ? 'active' : '';
			$id         = isset( $page['id'] ) ? $page['id'] : '';
			$page_label = $page['label'];
			$target     = isset( $page['target'] ) ? $page['target'] : false;

			wc_get_template(
				'nav.php',
				array(
					'page'       => $page,
					'page_url'   => $page_url,
					'target'     => $target,
					'page_label' => $page_label,
					'class'      => $class,
					'id'         => $id,
				),
				'wc-vendors/dashboard/',
				$this->base_dir . 'templates/dashboard/'
			);

		}
		echo '</ul>';

		echo '</div>';

		if ( ! $vertical_menu ) {
			echo '</div>';
		}

		do_action( 'wcv_pro_after_dashboard_nav_container' );

		if ( $vertical_menu ) {
			echo '<div class="all-80 medium-100 small-100 wcv-main-content">';
		}

	}

	/**
	 * Check if a page is disabled and return if it is
	 *
	 * @since    1.3.0
	 * @version  1.5.0
	 */
	public function page_disabled() {

		$current_page = get_query_var( 'object' );
		$disabled     = false;

		switch ( $current_page ) {
			case 'product':
				$disabled = wc_string_to_bool( get_option( 'wcvendors_product_management_cap', 'no' ) );
				break;
			case 'order':
				$disabled = wc_string_to_bool( get_option( 'wcvendors_order_management_cap', 'no' ) );
				break;
			case 'shop_coupon':
				$disabled = wc_string_to_bool( get_option( 'wcvendors_shop_coupon_management_cap', 'no' ) );
				break;
			case 'rating':
				$disabled = wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) );
				break;
			case 'settings':
				$disabled = wc_string_to_bool( get_option( 'wcvendors_settings_management_cap', 'no' ) );
				break;
		}

		return ! apply_filters( 'wcvendors_page_disabled', $disabled, $current_page );

	}

	/**
	 * Shortcode for dashboard navigation
	 *
	 * @since    1.3.3
	 */
	public function load_dashboard_nav() {

		if ( ! is_user_logged_in() ) {

			return false;

		} elseif ( WCV_Vendors::is_vendor( get_current_user_id() ) ) {

			ob_start();
			echo '<div class="wcvendors-pro-dashboard-wrapper"><div class="wcv-grid">';
			$this->create_nav();
			echo '</div></div>';

			return ob_get_clean();

		}

	}

	/**
	 * Check if the current page is a dashboard page
	 *
	 * @since      1.4.0
	 * @access     public
	 * @deprecated 1.5.4
	 * @param int $page_id the page id to check.
	 * @return bool returns if the page id passed is a dashboard page.
	 */
	public static function is_dashboard_page( $page_id ) {
		return wcv_is_dashboard_page( $page_id );
	}

	/**
	 * Check if the the vendors access has been disabled
	 *
	 * @since  1.4.0
	 * @access public
	 */
	public function lock_new_products_notice() {

		$lock_new_products         = ( get_user_meta( get_current_user_id(), '_wcv_lock_new_products_vendor', true ) == 'yes' ) ? true : false;
		$lock_new_products_notice  = get_user_meta( get_current_user_id(), '_wcv_lock_new_products_vendor_msg', true );
		$lock_edit_products        = ( get_user_meta( get_current_user_id(), '_wcv_lock_edit_products_vendor', true ) == 'yes' ) ? true : false;
		$lock_edit_products_notice = get_user_meta( get_current_user_id(), '_wcv_lock_edit_products_vendor_msg', true );
		$notice                    = '';

		if ( $lock_new_products ) {
			$notice .= $lock_new_products_notice;
		}
		if ( $lock_edit_products ) {
			$notice .= ' ' . $lock_edit_products_notice;
		}

		if ( $lock_new_products || $lock_edit_products ) {

			wc_get_template(
				'dashboard-notice.php',
				array(
					'vendor_dashboard_notice' => $notice,
					'notice_type'             => 'error',
				),
				'wc-vendors/dashboard/',
				$this->base_dir . '/templates/dashboard/'
			);
		}

	}

}
