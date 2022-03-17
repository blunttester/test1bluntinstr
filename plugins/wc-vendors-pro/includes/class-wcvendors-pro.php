<?php
/**
 * Holds the core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes
 * @author     Jamie Madden <support@wcvendors.com>
 * @since      1.0.0
 */

/**
 * WC Vendors Pro Core Class
 *
 * @version 1.7.5
 * @since   1.0.0
 */
class WCVendors_Pro {


	/**
	 * The loader that's responsible for maintaining
	 * and registering all hooks that power the plugin.
	 *
	 * @since  1.0.0
	 * @var    WCVendors_Pro_Loader $loader Maintains and registers
	 *                              all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @var    string $wcvendors_pro The string used to uniquely
	 *                identify this plugin.
	 */
	protected $wcvendors_pro;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Is the plugin base directory
	 *
	 * @since  1.0.0
	 * @var    string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since  1.0.0
	 * @var    bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * The actions and filter prefix for the plugin.
	 */
	public $prefix;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->wcvendors_pro = 'wcvendors-pro';
		$this->version       = WCV_PRO_VERSION;
		$this->debug         = false;
		$this->base_dir      = plugin_dir_url( __FILE__ );
		$this->suffix        = $this->debug ? '' : '.min';
		$this->prefix        = 'wcvendors_';

		$this->load_dependencies();
		$this->set_locale();

		add_action( 'admin_init', array( $this, 'check_install' ) );

		// Admin Objects.
		$this->wcvendors_pro_admin                   = new WCVendors_Pro_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_commission_controller   = new WCVendors_Pro_Commission_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_shipping_controller     = new WCVendors_Pro_Shipping_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_admin_vendor_controller = new WCVendors_Pro_Admin_Vendor_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );

		// Public Objects.
		$this->wcvendors_pro_public                 = new WCVendors_Pro_Public( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_dashboard              = new WCVendors_Pro_Dashboard( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_product_controller     = new WCVendors_Pro_Product_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_order_controller       = new WCVendors_Pro_Order_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_shop_coupon_controller = new WCVendors_Pro_Shop_Coupon_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_report_controller      = new WCVendors_Pro_Reports_Controller( $this->wcvendors_pro, $this->version, $this->get_debug() );
		$this->wcvendors_pro_vendor_controller      = new WCVendors_Pro_Vendor_Controller( $this->wcvendors_pro, $this->version, $this->get_debug() );
		$this->wcvendors_pro_product_form           = new WCVendors_Pro_Product_Form( $this->wcvendors_pro, $this->version, $this->get_debug() );
		$this->wcvendors_pro_store_form             = new WCVendors_Pro_Store_Form( $this->wcvendors_pro, $this->version, $this->get_debug() );

		// Upload limits pass user_id of 0 to allow the class to set the correct user_id in the filter
		$this->wcvendors_pro_upload_limits = new WCVendors_Pro_Upload_Limits( 0 );

		// Shared Objects.
		$this->wcvendors_pro_ratings_controller = new WCVendors_Pro_Ratings_Controller( $this->get_plugin_name(), $this->get_version(), $this->get_debug() );
		$this->wcvendors_pro_delivery           = new WCVendors_Pro_Delivery();

		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();
	}

	/**
	 * Deactivate pro if WC Vendors 2.3.0 isn't found.
	 *
	 * @version 1.7.10
	 */
	public function check_install() {

		include_once 'wcv-functions.php';
		if ( version_compare( WCV_VERSION, '2.3.0', '<' ) ) {
			deactivate_plugins( WCV_PRO_PLUGIN_FILE );
			add_action( 'admin_notices', 'wcvendors_2_required_notice' );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WCVendors_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - WCVendors_Pro_i18n. Defines internationalization functionality.
	 * - WCVendors_Pro_Admin. Defines all hooks for the dashboard.
	 * - WCVendors_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-loader.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-activator.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-i18n.php';

		/**
		 *  A utility class for use throughout the plugin
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-utils.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin-notices.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-commission-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-shipping-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin-vendor-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin-settings.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wcv-admin-functions.php';

		/**
		 *  The classes that are shared between both admin and public
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-ratings-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-delivery.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-product-dropdown-walker.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-product-category-checklist.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/walkers/class-wcvendors-pro-store-cat-list-walker.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wcv-update-functions.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcvendors-pro-upload-limits.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-public.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-form-helper.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-table-helper.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-dashboard.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-product-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-order-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-vendor-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-shop-coupon-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcvendors-pro-reports-controller.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/wcv-template-functions.php';

		/**
		 *   All forms for the public facing side
		 */

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/forms/class-wcvendors-pro-store-form.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/forms/class-wcvendors-pro-tracking-number-form.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/forms/class-wcvendors-pro-coupon-form.php';
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/forms/class-wcvendors-pro-product-form.php';

		/**
		 * Email templates
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/emails/class-emails.php';

		$this->loader = new WCVendors_Pro_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WCVendors_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new WCVendors_Pro_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @version 1.7.10
	 */
	private function define_admin_hooks() {

		$plugin_basename   = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->wcvendors_pro . '.php' );
		$shipping_disabled = 'yes' === get_option( 'wcvendors_shipping_management_cap', 'no' ) ? true : false;

		// Load email templates.
		new WCV_Pro_Emails();

		// Installer & Update.
		$this->loader->add_action( 'init', 'WCVendors_Pro_Activator', 'check_version' );
		$this->loader->add_action( 'init', 'WCVendors_Pro_Activator', 'init_background_updater' );
		$this->loader->add_action( 'admin_init', 'WCVendors_Pro_Activator', 'install_actions' );
		$this->loader->add_action( 'init', 'WCVendors_Pro_Admin_Notices', 'init' );

		// Setup Wizard.
		$this->loader->add_action( 'init', __CLASS__, 'include_setup_wizard' );
		$this->loader->add_action( 'admin_head', __CLASS__, 'hide_setup_wizard' );

		// Register admin actions.
		$this->loader->add_action( 'admin_enqueue_scripts', $this->wcvendors_pro_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_shared_scripts' );
		$this->loader->add_action( 'wcvendors_capability_product_data_tabs', $this->wcvendors_pro_admin, 'add_product_data_tabs', 10, 1 );

		// Add product edit template to edit screen.
		$this->loader->add_action( 'add_meta_boxes', $this->wcvendors_pro_admin, 'add_template_meta_box' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $this->wcvendors_pro_admin, 'save_template_product_meta' );

		// Product extra fields.
		$this->loader->add_action( 'woocommerce_product_options_advanced', $this->wcvendors_pro_admin, 'disable_vendor_edit_product' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $this->wcvendors_pro_admin, 'save_disable_vendor_edit_product' );

		// Category Commission fields.
		$this->loader->add_filter( 'product_cat_edit_form_fields', $this->wcvendors_pro_admin, 'product_cat_edit_form_fields' );
		$this->loader->add_filter( 'product_cat_add_form_fields', $this->wcvendors_pro_admin, 'product_cat_edit_form_fields' );
		$this->loader->add_filter( 'created_product_cat', $this->wcvendors_pro_admin, 'save_category_commissions' );
		$this->loader->add_filter( 'edited_product_cat', $this->wcvendors_pro_admin, 'save_category_commissions' );

		// Store Widgets.
		$this->loader->add_action( 'widgets_init', $this->wcvendors_pro_admin, 'register_widgets' );

		$this->loader->add_action( 'init', $this->wcvendors_pro_admin, 'admin_lockout' );

		$this->loader->add_action( 'woocommerce_system_status_report', $this->wcvendors_pro_admin, 'wcvendors_pro_system_status' );
		$this->loader->add_action( 'woocommerce_system_status_report', $this->wcvendors_pro_admin, 'wcvendors_pro_template_status' );

		$this->loader->add_filter( 'woocommerce_debug_tools', $this->wcvendors_pro_admin, 'wc_pro_tools' );
		$this->loader->add_filter( 'wp_dropdown_users', $this->wcvendors_pro_admin, 'vendor_dropdown_users' );

		// @todo replace this with the plugin_basename once work out how to correct the path to wcvendors-pro instead of wc-vendors-pro.
		$this->loader->add_action( 'plugin_action_links_' . $plugin_basename, $this->wcvendors_pro_admin, 'add_action_links' );

		$this->loader->add_filter( $this->prefix . 'commission_rate', $this->wcvendors_pro_commission_controller, 'process_commission', 10, 6 );
		$this->loader->add_action( 'wcvendors_shipping_due', $this->wcvendors_pro_commission_controller, 'get_shipping_due_from_order', 10, 5 );

		// Product Meta Commission Tab.
		// disable free commission tabs.
		$this->loader->add_filter( $this->prefix . 'product_commission_tab', $this->wcvendors_pro_commission_controller, 'update_product_meta' );
		$this->loader->add_action( 'woocommerce_product_write_panel_tabs', $this->wcvendors_pro_commission_controller, 'add_commission_tab' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $this->wcvendors_pro_commission_controller, 'add_commission_panel' );
		$this->loader->add_action( $this->prefix . 'commission_panel_after', $this->wcvendors_pro_commission_controller, 'add_product_commission_tiers_panel' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $this->wcvendors_pro_commission_controller, 'save_commission_panel' );
		$this->loader->add_filter( 'wcvendors_admin_user_meta_commission_rate_enable', $this->wcvendors_pro_commission_controller, 'disable_free_commission_user' );

		// Order commission actions and filters.
		$this->loader->add_filter( 'bulk_actions-edit-shop_order', $this->wcvendors_pro_commission_controller, 'add_bulk_order_commissions_action' );
		$this->loader->add_action( 'admin_action_wcv_bulk_order_commissions', $this->wcvendors_pro_commission_controller, 'calculate_bulk_order_commissions' );
		$this->loader->add_action( 'admin_notices', $this->wcvendors_pro_commission_controller, 'commissions_calculated_notice' );
		$this->loader->add_action( 'woocommerce_order_actions', $this->wcvendors_pro_commission_controller, 'add_order_commissions_action' );
		$this->loader->add_action( 'woocommerce_order_action_wcv_order_commissions', $this->wcvendors_pro_commission_controller, 'calculate_order_commissions' );
		$this->loader->add_action( 'wcvendors_settings_save_commission', $this->wcvendors_pro_commission_controller, 'save_commission_tiers_settings' );

		// Vendor Commission Overrides.
		$this->loader->add_action( 'show_user_profile', $this->wcvendors_pro_commission_controller, 'store_commission_meta_fields', 11 );
		$this->loader->add_action( 'edit_user_profile', $this->wcvendors_pro_commission_controller, 'store_commission_meta_fields', 11 );
		$this->loader->add_action( '_wcv_after_admin_store_commission_fields', $this->wcvendors_pro_commission_controller, 'add_vendor_commission_tiers_panel' );

		$this->loader->add_action( 'personal_options_update', $this->wcvendors_pro_commission_controller, 'store_commission_meta_fields_save', 11 );
		$this->loader->add_action( 'edit_user_profile_update', $this->wcvendors_pro_commission_controller, 'store_commission_meta_fields_save', 11 );

		// Vendor Controller.
		$this->loader->add_action( 'edit_user_profile', $this->wcvendors_pro_admin_vendor_controller, 'add_pro_vendor_meta_fields', 11 );
		$this->loader->add_action( 'show_user_profile', $this->wcvendors_pro_admin_vendor_controller, 'add_pro_vendor_meta_fields', 11 );

		$this->loader->add_action( 'personal_options_update', $this->wcvendors_pro_admin_vendor_controller, 'save_pro_vendor_meta_fields' );
		$this->loader->add_action( 'personal_options_update', $this->wcvendors_pro_admin_vendor_controller, 'save_pro_vendor_opening_hours' );
		$this->loader->add_action( 'edit_user_profile_update', $this->wcvendors_pro_admin_vendor_controller, 'save_pro_vendor_opening_hours' );
		$this->loader->add_action( 'edit_user_profile_update', $this->wcvendors_pro_admin_vendor_controller, 'save_pro_vendor_meta_fields' );
		$this->loader->add_action( $this->prefix . 'admin_after_store_general', $this->wcvendors_pro_admin_vendor_controller, 'add_opening_hours', 10, 1 );

		// Coupon.
		$this->loader->add_filter( 'woocommerce_coupon_discount_types', __CLASS__, 'remove_admin_fixed_cart_discount', 10, 1 );

		// Check shipping capability.
		if ( ! $shipping_disabled ) {

			// Shipping calculator.
			$this->loader->add_action( 'woocommerce_shipping_init', $this->wcvendors_pro_admin, 'wcvendors_pro_shipping_init' );
			$this->loader->add_filter( 'woocommerce_shipping_methods', $this->wcvendors_pro_admin, 'wcvendors_pro_shipping_method' );

			// Shipping Controller.
			$this->loader->add_action( 'woocommerce_product_tabs', $this->wcvendors_pro_shipping_controller, 'shipping_panel_tab', 11, 2 );

			// Store Shipping Override for User Meta.
			$this->loader->add_action( 'personal_options_update', $this->wcvendors_pro_shipping_controller, 'save_vendor_shipping_user', 11 );
			$this->loader->add_action( 'edit_user_profile_update', $this->wcvendors_pro_shipping_controller, 'save_vendor_shipping_user', 11 );
			$this->loader->add_action( 'edit_user_profile', $this->wcvendors_pro_shipping_controller, 'add_pro_vendor_meta_fields', 11 );
			$this->loader->add_action( 'show_user_profile', $this->wcvendors_pro_shipping_controller, 'add_pro_vendor_meta_fields', 11 );
			$this->loader->add_action( $this->prefix . 'admin_after_shipping_flat_rate', $this->wcvendors_pro_shipping_controller, 'add_pro_vendor_country_rate_fields', 11 );

			// Shipping Product edit.
			$this->loader->add_action( 'woocommerce_product_options_shipping', $this->wcvendors_pro_shipping_controller, 'product_vendor_shipping_panel' );
			$this->loader->add_action( 'woocommerce_process_product_meta', $this->wcvendors_pro_shipping_controller, 'save_vendor_shipping_product' );

			// Cart and checkout.
			$this->loader->add_filter( 'woocommerce_cart_shipping_packages', $this->wcvendors_pro_shipping_controller, 'vendor_split_woocommerce_cart_shipping_packages' );
			$this->loader->add_filter( 'woocommerce_shipping_package_name', $this->wcvendors_pro_shipping_controller, 'rename_vendor_shipping_package', 10, 3 );
			$this->loader->add_filter( 'woocommerce_cart_shipping_method_full_label', $this->wcvendors_pro_shipping_controller, 'rename_vendor_shipping_method_label', 10, 2 );
		}

		// Add menu mata box.
		$this->loader->add_filter( 'nav_menu_meta_box_object', $this->wcvendors_pro_admin, 'add_nav_menu_meta_boxes', 10, 1 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since   1.0.0
	 * @since   1.7.10
	 */
	private function define_public_hooks() {

		$shipping_disabled             = 'yes' === get_option( 'wcvendors_shipping_management_cap', 'no' ) ? true : false;
		$pro_store_header              = get_option( 'wcvendors_vendor_store_header_type', 'pro' );
		$shop_store_header             = 'yes' === get_option( 'wcvendors_display_shop_headers', 'no' ) ? true : false;
		$single_store_header           = 'yes' === get_option( 'wcvendors_store_single_headers', 'no' ) ? true : false;
		$single_product_tools          = 'yes' === get_option( 'wcvendors_single_product_tools', 'no' ) ? true : false;
		$header_prority                = get_option( 'wcvendors_store_header_priority', 30 );
		$single_product_header_prority = get_option( 'wcvendors_single_product_store_header_priority', 30 );

		// Public Class.
		$this->loader->add_action( 'wp', $this->wcvendors_pro_public, 'load_theme_support' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->wcvendors_pro_public, 'enqueue_styles', 15 );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->wcvendors_pro_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_shared_scripts' );
		$this->loader->add_filter( 'body_class', $this->wcvendors_pro_public, 'body_class' );
		$this->loader->add_action( 'wp_head', $this->wcvendors_pro_public, 'product_seo_meta' );
		$this->loader->add_action( 'wp_head', $this->wcvendors_pro_public, 'add_vendor_ga_code' );
		$this->loader->add_filter( 'wp_list_categories', $this->wcvendors_pro_public, 'filter_categories_list_output', 10, 2 );

		// Public Ajax calls.
		$this->loader->add_action( 'wp_ajax_wcv_file_uploader_preview', $this->wcvendors_pro_public, 'file_uploader_preview' );

		// WCVendors Pro Dashboard.
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_dashboard, 'check_permission' );
		$this->loader->add_action( $this->prefix . 'pro_after_dashboard_nav', $this->wcvendors_pro_dashboard, 'lock_new_products_notice' );
		// $this->loader->add_action( 'wcv_pro_after_dashboard_nav', $this->wcvendors_pro_dashboard, 'vacation_mode_notice' );
		// Dashboard Rewrite rule filters.
		$this->loader->add_filter( 'query_vars', $this->wcvendors_pro_dashboard, 'add_query_vars' );
		$this->loader->add_filter( 'rewrite_rules_array', $this->wcvendors_pro_dashboard, 'rewrite_rules' );
		$this->loader->add_shortcode( 'wcv_pro_dashboard', $this->wcvendors_pro_dashboard, 'load_dashboard' );
		$this->loader->add_shortcode( 'wcv_pro_dashboard_nav', $this->wcvendors_pro_dashboard, 'load_dashboard_nav' );

		// Product controller.
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_product_controller, 'process_submit' );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_product_controller, 'process_delete' );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_product_controller, 'process_duplicate' );
		$this->loader->add_filter( 'woocommerce_product_tabs', $this->wcvendors_pro_public, 'product_policy_tab', 20 );
		$this->loader->add_action( $this->prefix . 'delete_post', $this->wcvendors_pro_product_controller, 'delete_rating_data', 20, 1 );
		$this->loader->add_action( 'after_delete_post', $this->wcvendors_pro_product_controller, 'delete_rating_data', 20, 1 );

		// File upload limits code.
		$this->loader->add_filter( 'wp_handle_upload_prefilter', $this->wcvendors_pro_product_controller, 'filter_upload' );
		$this->loader->add_action( 'pre_get_posts', $this, 'filter_product_search_query' );

		// Product Display table.
		$this->loader->add_filter( 'wcvendors_pro_table_row_args_product', $this->wcvendors_pro_product_controller, 'product_search_args' );
		$this->loader->add_filter( 'wcvendors_pro_table_columns_product', $this->wcvendors_pro_product_controller, 'table_columns' );
		$this->loader->add_filter( 'wcvendors_pro_table_rows_product', $this->wcvendors_pro_product_controller, 'table_rows', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_action_column_product', $this->wcvendors_pro_product_controller, 'table_action_column' );
		$this->loader->add_filter( 'wcvendors_pro_table_before_product', $this->wcvendors_pro_product_controller, 'table_actions', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_after_product', $this->wcvendors_pro_product_controller, 'table_actions', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_post_per_page_product', $this->wcvendors_pro_product_controller, 'table_posts_per_page' );
		$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_product', $this->wcvendors_pro_product_controller, 'table_no_data_notice' );
		$this->loader->add_filter( $this->prefix . 'product_form_templates', $this->wcvendors_pro_product_controller, 'template_overrides_options' );
		$this->loader->add_filter( 'wcv_product_table_row_actions', $this->wcvendors_pro_product_controller, 'maybe_disable_actions', 10, 2 );
		$this->loader->add_filter( 'wcvendors_page_disabled', $this->wcvendors_pro_product_controller, 'maybe_disable_page', 10, 2 );

		// Product AJAX calls.
		$this->loader->add_action( 'wp_ajax_wcv_json_search_products', $this->wcvendors_pro_product_controller, 'json_search_products' );
		$this->loader->add_action( 'wp_ajax_wcv_json_search_tags', $this->wcvendors_pro_product_controller, 'json_search_product_tags' );
		$this->loader->add_action( 'wp_ajax_wcv_json_add_attribute', $this->wcvendors_pro_product_controller, 'json_add_attribute' );
		$this->loader->add_action( 'wp_ajax_wcv_json_add_new_attribute', $this->wcvendors_pro_product_controller, 'json_add_new_attribute' );
		$this->loader->add_action( 'wp_ajax_wcv_json_default_variation_attributes', $this->wcvendors_pro_product_controller, 'json_default_variation_attributes' );
		$this->loader->add_action( 'wp_ajax_wcv_json_load_variation', $this->wcvendors_pro_product_controller, 'json_load_variations' );
		$this->loader->add_action( 'wp_ajax_wcv_json_add_variation', $this->wcvendors_pro_product_controller, 'json_add_variation' );
		$this->loader->add_action( 'wp_ajax_wcv_json_link_all_variations', $this->wcvendors_pro_product_controller, 'json_link_all_variations' );

		// Orders controller.
		$this->loader->add_filter( 'wcvendors_pro_table_columns_order', $this->wcvendors_pro_order_controller, 'table_columns' );
		$this->loader->add_filter( 'wcvendors_pro_table_rows_order', $this->wcvendors_pro_order_controller, 'table_rows', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_action_column_order', $this->wcvendors_pro_order_controller, 'table_action_column' );
		$this->loader->add_filter( 'wcvendors_pro_table_before_order', $this->wcvendors_pro_order_controller, 'table_actions' );
		$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_order', $this->wcvendors_pro_order_controller, 'table_no_data_notice' );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_order_controller, 'process_submit' );
		$this->loader->add_action( 'template_redirect', $this, 'wc_filter_address_hook' );
		$this->loader->add_filter( 'woocommerce_order_item_get_formatted_meta_data', $this->wcvendors_pro_order_controller, 'filter_order_item_get_formatted_meta_data', 10, 2 );

		// Shop Coupon controller.
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_shop_coupon_controller, 'process_submit' );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_shop_coupon_controller, 'process_delete' );

		// Shop coupon table.
		$this->loader->add_filter( 'wcvendors_pro_table_columns_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_columns' );
		$this->loader->add_filter( 'wcvendors_pro_table_rows_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_rows', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_actions_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_row_actions' );
		$this->loader->add_filter( 'wcvendors_pro_table_action_column_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_action_column' );
		$this->loader->add_filter( 'wcvendors_pro_table_before_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_actions', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_after_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_actions', 10, 2 );
		$this->loader->add_filter( 'wcvendors_pro_table_post_per_page_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_posts_per_page' );
		$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_shop_coupon', $this->wcvendors_pro_shop_coupon_controller, 'table_no_data_notice' );

		// Validate min/max amounts for vendor coupons.
		$this->loader->add_filter( 'woocommerce_coupon_is_valid', $this->wcvendors_pro_shop_coupon_controller, 'validate_vendor_coupon_min_max', 10, 3 );

		$this->loader->add_filter( 'manage_shop_coupon_posts_columns', $this->wcvendors_pro_shop_coupon_controller, 'display_vendor_store_column', 15 );
		$this->loader->add_action( 'manage_shop_coupon_posts_custom_column', $this->wcvendors_pro_shop_coupon_controller, 'display_vendor_store_custom_column', 2, 99 );

		// Reports.
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_report_controller, 'process_submit' );
		$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_recent_product', $this->wcvendors_pro_report_controller, 'product_table_no_data_notice' );
		$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_recent_order', $this->wcvendors_pro_report_controller, 'order_table_no_data_notice' );

		// Vendor Controller.
		$this->loader->add_filter( 'wp_head', $this->wcvendors_pro_vendor_controller, 'storefront_seo' );
		$this->loader->add_filter( 'woocommerce_login_redirect', $this->wcvendors_pro_vendor_controller, 'vendor_login_redirect', 10, 2 );
		$this->loader->add_action( 'woocommerce_created_customer', $this->wcvendors_pro_vendor_controller, 'apply_vendor_redirect', 10, 2 );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_vendor_controller, 'process_submit' );
		$this->loader->add_action( $this->prefix . 'pro_store_settings_saved', $this->wcvendors_pro_vendor_controller, 'save_social_media_settings' );
		$this->loader->add_action( 'woocommerce_before_my_account', $this->wcvendors_pro_vendor_controller, 'pro_dashboard_link_myaccount' );
		$this->loader->add_shortcode( 'wcv_pro_vendorslist', $this->wcvendors_pro_vendor_controller, 'vendors_list' );
		$this->loader->add_shortcode( 'wcv_pro_vendor_totalsales', $this->wcvendors_pro_vendor_controller, 'vendor_total_sales_shortcode' );
		$this->loader->add_shortcode( 'wcv_pro_product_totalsales', $this->wcvendors_pro_vendor_controller, 'product_total_sales_shortcode' );
		$this->loader->add_shortcode( 'wcv_vendor', $this->wcvendors_pro_vendor_controller, 'vendor_details_shortcode' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->wcvendors_pro_vendor_controller, 'wcvendors_list_scripts' );
		$this->loader->add_action( 'wp_ajax_wcv_json_unique_store_name', $this->wcvendors_pro_vendor_controller, 'json_unique_store_name' );
		$this->loader->add_filter( 'woocommerce_get_endpoint_url', $this->wcvendors_pro_vendor_controller, 'become_a_vendor_override', 10, 4 );
		$this->loader->add_action( 'woocommerce_single_product_summary', $this->wcvendors_pro_vendor_controller, 'product_total_sales_summary', 11 );

		// Store query filters.
		$this->loader->add_action( 'pre_get_posts', $this->wcvendors_pro_vendor_controller, 'vendor_store_search_where', 99 );
		$this->loader->add_action( 'pre_get_posts', $this->wcvendors_pro_vendor_controller, 'vendor_store_category_filter', 99 );
		$this->loader->add_action( 'pre_get_posts', $this->wcvendors_pro_vendor_controller, 'vendor_store_products_filter', 99 );

		// Dashboard notices.
		$this->loader->add_action( $this->prefix . 'pro_before_dashboard', $this->wcvendors_pro_vendor_controller, 'vacation_mode_notice' );
		$this->loader->add_filter( 'woocommerce_is_purchasable', $this->wcvendors_pro_vendor_controller, 'is_product_purchasable', 20, 2 );
		$this->loader->add_action( 'woocommerce_before_main_content', $this->wcvendors_pro_vendor_controller, 'show_vendor_store_notice' );
		$this->loader->add_action( $this->prefix . 'pro_before_dashboard', $this->wcvendors_pro_vendor_controller, 'show_vendor_upload_limits_notice' );

		if ( 'free' !== $pro_store_header ) {

			// Disable free shop headers.
			add_filter(
				'wcvendors_disable_shop_headers',
				function () {
					return false;
				}
			);

			// Main Shop Header.
			if ( $shop_store_header ) {
				$this->loader->add_action( 'woocommerce_before_main_content', $this->wcvendors_pro_vendor_controller, 'store_main_content_header', $header_prority );
				$this->loader->add_action( $this->prefix . 'after_vendor_store_header', $this->wcvendors_pro_vendor_controller, 'vacation_mode' );

			} else {
				$this->loader->add_action( 'woocommerce_before_main_content', $this->wcvendors_pro_vendor_controller, 'vacation_mode', $header_prority );
			}

			// Single shop header.
			if ( $single_store_header ) {
				$this->loader->add_action( 'woocommerce_before_single_product', $this->wcvendors_pro_vendor_controller, 'store_single_header', $single_product_header_prority );
			} else {
				$this->loader->add_action( 'woocommerce_before_single_product', $this->wcvendors_pro_vendor_controller, 'vacation_mode' );
			}
		}

		if ( ! $shipping_disabled ) {
			$this->loader->add_action( 'woocommerce_product_meta_start', $this->wcvendors_pro_vendor_controller, 'product_ships_from', 9 );
		}

		// Single product page vendor tools.
		if ( $single_product_tools ) {
			$this->loader->add_action( 'woocommerce_product_meta_start', $this->wcvendors_pro_vendor_controller, 'enable_vendor_tools', 8 );
		}

		// My Account > Orders.
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $this->wcvendors_pro_delivery, 'add_orders_list_action', 10, 2 );
		$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_delivery, 'mark_received' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->wcvendors_pro_delivery, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_my_account_my_orders_column_order-status', $this->wcvendors_pro_delivery, 'print_received_text' );
		$this->loader->add_action( $this->prefix . 'orders_add_new_row', $this->wcvendors_pro_delivery, 'print_received_text_for_vendor', 10, 3 );
		$this->loader->add_filter( 'woocommerce_order_item_class', $this->wcvendors_pro_delivery, 'add_class_to_order_item', 10, 3 );
	}

	/**
	 * Register all of the hooks related to shared functionality
	 * of the plugin.
	 *
	 * @version 1.7.10
	 * @since   1.0.0
	 */
	private function define_shared_hooks() {
		// Settings.
		$ratings_disabled = 'yes' === get_option( 'wcvendors_ratings_management_cap', 'no' ) ? true : false;
		$pro_store_header = get_option( 'wcvendors_vendor_store_header_type', 'pro' );

		// Filter all uploads to include an md5 of the guid.
		$this->loader->add_filter( 'wp_update_attachment_metadata', 'WCVendors_Pro', 'add_md5_to_attachment', 10, 2 );

		if ( ! $ratings_disabled ) {

			// ADMIN.
			$this->loader->add_action( 'admin_menu', $this->wcvendors_pro_ratings_controller, 'admin_page_setup', 60 );
			$this->loader->add_filter( 'init', $this->wcvendors_pro_ratings_controller, 'process_form_submission' );
			$this->loader->add_filter( 'rewrite_rules_array', $this->wcvendors_pro_ratings_controller, 'add_rewrite_rules' );
			$this->loader->add_filter( 'query_vars', $this->wcvendors_pro_ratings_controller, 'add_query_vars' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->wcvendors_pro_ratings_controller, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->wcvendors_pro_ratings_controller, 'enqueue_styles' );

			// PUBLIC.
			$this->loader->add_filter( 'wcvendors_pro_table_columns_rating', $this->wcvendors_pro_ratings_controller, 'table_columns' );
			$this->loader->add_filter( 'wcvendors_pro_table_rows_rating', $this->wcvendors_pro_ratings_controller, 'table_rows' );
			$this->loader->add_filter( 'wcvendors_pro_table_action_column_rating', $this->wcvendors_pro_ratings_controller, 'table_action_column' );
			$this->loader->add_filter( 'wcvendors_pro_table_no_data_notice_rating', $this->wcvendors_pro_ratings_controller, 'table_no_data_notice' );
			$this->loader->add_action( 'template_redirect', $this->wcvendors_pro_ratings_controller, 'display_vendor_ratings' );
			$this->loader->add_action( 'woocommerce_product_tabs', $this->wcvendors_pro_ratings_controller, 'vendor_ratings_panel_tab' );
			$this->loader->add_shortcode( 'wcv_feedback', $this->wcvendors_pro_ratings_controller, 'wcv_feedback' );
			$this->loader->add_shortcode( 'wcv_vendor_total_ratings', $this->wcvendors_pro_ratings_controller, 'wcv_vendor_total_ratings' );

			$this->loader->add_action( 'woocommerce_account_downloads_column_download-file', $this->wcvendors_pro_ratings_controller, 'add_feedback_link' );

			// Display the link to view the ratings in both headers.
			if ( 'free' === $pro_store_header ) {
				$this->loader->add_action( $this->prefix . 'after_main_header', $this->wcvendors_pro_ratings_controller, 'ratings_link' );
				$this->loader->add_action( $this->prefix . 'after_mini_header', $this->wcvendors_pro_ratings_controller, 'ratings_link' );
			}

			$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $this->wcvendors_pro_ratings_controller, 'feedback_link_action', 10, 3 );
			$this->loader->add_shortcode( 'wcv_feedback_form', $this->wcvendors_pro_ratings_controller, 'feedback_form' );
			$this->loader->add_action( 'wcvendors_save_product_feedback', $this->wcvendors_pro_ratings_controller, 'sync_wcv_reviews_with_woo_reviews', 10, 4 );

		}

		$this->loader->add_filter( 'wp_handle_upload_prefilter', $this->wcvendors_pro_upload_limits, 'check_upload_limits' );
		$this->loader->add_filter( 'wp_kses_allowed_html', $this->wcvendors_pro_product_controller, 'allowed_html_tags', 11, 2 );
	}

	/**
	 * Enqueue script required in admin and frontend
	 *
	 * @return  void
	 * @since   1.5.9
	 * @version 1.5.9
	 */
	public function enqueue_shared_scripts() {

		if ( is_admin() ) {
			$screen = get_current_screen();
		}

		$current_page_id = get_the_ID();
		if ( empty( $screen ) && ! wcv_is_dashboard_page( $current_page_id ) ) {
			return;
		}

		$times = apply_filters(
			'wcv_store_opening_times',
			array_merge(
				get_time_interval_options(),
				array(
					array( 'closed' => __( 'Closed', 'wcvendors-pro' ) ),
					array( 'open' => __( 'Open', 'wcvendors-pro' ) ),
				)
			)
		);

		$times_html = wcv_options_html( $times );

		$wcv_days = apply_filters(
			'wcv_opening_hours_times_labels',
			array_merge(
				wcv_days_labels(),
				array(
					'times'          => apply_filters( 'wcv_opening_hours_times_html', $times_html ),
					'assets_url'     => WCV_PRO_PUBLIC_ASSETS_URL,
					'confirm_remove' => __( 'Are you sure you want to remove this?', 'wcvendors-pro' ),
				)
			)
		);

		wp_register_script( 'wcv-opening-hours', $this->base_dir . 'assets/js/store-opening-hours' . $this->suffix . '.js', array( 'jquery' ), WCV_PRO_VERSION, true );
		wp_enqueue_script( 'wcv-opening-hours' );
		wp_localize_script( 'wcv-opening-hours', 'wcv_days', $wcv_days );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string    The name of the plugin.
	 * @since  1.0.0
	 */
	public function get_plugin_name() {
		return $this->wcvendors_pro;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return WCVendors_Pro_Loader    Orchestrates the hooks of the plugin.
	 * @since  1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string    The version number of the plugin.
	 * @since  1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the debug status of the plugin.
	 *
	 * @return bool    The debug status of the plugin.
	 * @since  1.0.0
	 */
	public function get_debug() {
		return $this->debug;
	}

	/**
	 * Get Option wrapper for WC Vendors calls
	 *
	 * @param string $option The option name.
	 * @return mixed    The option requested from the main options system.
	 * @since  1.0.0
	 */
	public static function get_option( $option = '' ) {

		$mappings = wcv_get_settings_mapping();

		if ( array_key_exists( $option, $mappings ) ) {
			return get_option( $mappings[ $option ] );
		} else {
			get_option( $option );
		}

	} // get_option

	/**
	 * Get the plugin path
	 *
	 * @return string    The path to the plugin dir
	 * @since  1.0.0
	 */
	public static function get_path() {

		return plugin_dir_path( dirname( __FILE__ ) );

	} // get_path

	/**
	 * Class logger so that we can keep our debug and logging information cleaner
	 *
	 * @param mixed  $data the data to go to the error log could be string, array or object.
	 * @param string $prefix The prefix.
	 * @version 1.4.4
	 * @since   1.4.0
	 */
	public static function log( $data = '', $prefix = '' ) {
		// phpcs:disable
		$trace  = debug_backtrace( false, 2 );
		$caller = ( isset( $trace[1]['class'] ) ) ? $trace[1]['class'] : basename( $trace[1]['file'] );

		if ( is_array( $data ) || is_object( $data ) ) {
			if ( $prefix ) {
				error_log( '===========================' );
				error_log( $prefix );
				error_log( '===========================' );
			}
			error_log( $caller . ' : ' . print_r( $data, true ) );
		} else {
			if ( $prefix ) {
				error_log( '===========================' );
				error_log( $prefix );
				error_log( '===========================' );
			}
			error_log( $caller . ' : ' . $data );
		}
		// phpcs:enable

	} // log

	/**
	 * Filter the WooCommerce shipping and billing addresses on the pro dashboard to show and hide options
	 *
	 * @since   1.3.6
	 * @version 1.5.4
	 */
	public function wc_filter_address_hook() {

		$dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id', array() );

		foreach ( $dashboard_page_ids as $dashboard_page_id ) {
			if ( isset( $dashboard_page_id ) ) {
				// Dashboard page or the shipping label page.
				if ( is_page( $dashboard_page_id ) || ( isset( $_GET['wcv_shipping_label'] ) ) ) {
					add_filter(
						'woocommerce_order_formatted_shipping_address',
						array(
							$this->wcvendors_pro_order_controller,
							'filter_formatted_shipping_address',
						)
					);
					add_filter(
						'woocommerce_order_formatted_billing_address',
						array(
							$this->wcvendors_pro_order_controller,
							'filter_formatted_billing_address',
						)
					);
				}
			}
		}

	} // wc_shipping_address_hook

	/**
	 * Hook into the pre_get_posts to modify the search
	 *
	 * @param object $query The WP_Query object.
	 * @since   1.5.0
	 * @version 1.5.4
	 */
	public function filter_product_search_query( $query ) {

		$current_page_id = get_the_ID();

		if ( ! wcv_is_dashboard_page( $current_page_id ) ) {
			return;
		}

		$search = $query->get( '_wcv_product_search' );

		if ( $search ) {

			add_filter(
				'get_meta_sql',
				function ( $sql ) use ( $search ) {
					global $wpdb;

					// Only run once.
					static $nr = 0;
					if ( 0 != $nr ++ ) {
						return $sql;
					}

					// Modified WHERE.
					$sql['where'] = sprintf(
						' AND ( %s OR %s ) ',
						$wpdb->prepare( "{$wpdb->posts}.post_title like '%%%s%%'", $search ),
						mb_substr( $sql['where'], 5, mb_strlen( $sql['where'] ) )
					);

					return $sql;
				}
			);
		}
	}

	/**
	 * --------------------------------------------------------------------------
	 * WC Vendors Pro settings
	 * --------------------------------------------------------------------------
	 *
	 * These methods return which front end components are enabled for WC Vendors Pro
	 */


	/**
	 *  Check if the vendor shipping method is enabled in woocommerce settings
	 *
	 * @since  1.4.0
	 */
	public function is_vendor_shipping_method_enabled() {

		$shipping_methods        = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
		$shipping_method_enabled = ( array_key_exists( 'wcv_pro_vendor_shipping', $shipping_methods ) && 'yes' === $shipping_methods['wcv_pro_vendor_shipping']->enabled ) ? true : false;

		return $shipping_method_enabled;

	} // is_vendor_shipping_method_enabled

	/**
	 * This function fires when an attachment is uploaded in wp-admin and will generate an md5 of the post GUID.
	 *
	 * @param array $meta_data The meta data of the post.
	 * @param int   $post_id The post ID.
	 * @return array
	 * @version 1.3.9
	 * @since   1.3.9
	 */
	public static function add_md5_to_attachment( $meta_data, $post_id ) {

		self::md5_attachment_url( $post_id );

		// Return original Meta data.
		return $meta_data;

	} // add_md5_to_attachment

	/**
	 * This function will add an md5 hash of the file url ( post GUID ) on attachment post types.
	 *
	 * @param int $post_id The post ID.
	 * @since  1.3.9
	 */
	public static function md5_attachment_url( $post_id ) {

		// Add an MD5 of the GUID for later queries.
		$attachment_post = get_post( $post_id );
		if ( ! $attachment_post ) {
			return false;
		}

		update_post_meta( $attachment_post->ID, '_md5_guid', md5( $attachment_post->guid ) );

	} // md5_upload_attachment

	/**
	 * This function will return the md5 hash of an attachment post if the id is
	 *
	 * @param string $md5_guid The guid.
	 * @return int $attachment_id
	 * @since  1.3.9
	 */
	public static function get_attachment_id( $md5_guid ) {

		global $wpdb;
		// Get the attachment_id from the database.
		$attachment_id = $wpdb->get_var( "select post_id from $wpdb->postmeta where meta_key = '_md5_guid' AND meta_value ='$md5_guid'" );

		return $attachment_id;

	} // get_attachment_id

	/**
	 * Need to load this at admin init.
	 */
	public static function include_setup_wizard() {

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcvendors-pro-admin-setup-wizard.php';
	}

	/**
	 * Hide the setup wizard
	 *
	 * @return void
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public static function hide_setup_wizard() {
		remove_submenu_page( 'index.php', 'wcvendors-pro-setup' );
	}

	/**
	 * Remove fixed cart from coupon discount type dropdown.
	 *
	 * @param  array $types array of type.
	 * @return array $types .
	 * @version 1.7.9
	 * @since 1.7.9
	 */
	public static function remove_admin_fixed_cart_discount( $types ) {

		if ( array_key_exists( 'fixed_cart', $types ) ) {
			unset( $types['fixed_cart'] );
		}

		return $types;

	}

} // WCVendors_Pro
