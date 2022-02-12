<?php
/**
 * The WCVendors Pro Rating Class
 *
 * This class handles the Vendor ratings system
 *
 * @since      1.0.0
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/includes
 * @author     Jamie Madden <support@wcvendors.com>
 */

class WCVendors_Pro_Ratings_Controller {

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
	 * Is the plugin base directory
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $base_dir string path for the plugin directory
	 */
	private $base_dir;

	/**
	 * Is the ratings table name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $table_name name of the ratings table
	 */
	public static $table_name = 'wcv_feedback';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wcvendors_pro The name of the plugin.
	 * @param      string $version       The version of this plugin.
	 * @param      bool   $debug         If the plugin is currently in debug mode
	 */
	public function __construct( $wcvendors_pro, $version, $debug ) {
		global $wpdb;

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;
		$this->debug         = $debug;
		$this->base_dir      = plugin_dir_path( dirname( __FILE__ ) );
		$this->base_url      = plugin_dir_url( __FILE__ );
		$this->suffix        = $this->debug ? '' : '.min';

	}

	/**
	 * Load admin javascript
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

	} // enqueue_scripts()

	/**
	 * Add the styles
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();

		if ( $screen->id == 'woocommerce_page_wcv_pro_vendor_feedback' || $screen->id == 'wc-vendors_page_wcv_pro_vendor_feedback' ) {
			// SVG Icon Styles
			wp_enqueue_style(
				'wcv-icons',
				WCV_PRO_PUBLIC_ASSETS_URL . 'css/wcv-icons' . $this->suffix . '.css',
				array(),
				$this->version,
				'all'
			);
		}

	} // enqueue_styles()

	/**
	 *  Process the ratings submission
	 *
	 * @since   1.0.0
	 * @version 1.6.0
	 */
	public function process_form_submission() {

		// Is the form submitted
		if ( ! isset( $_POST['wcv-order_id'] ) || ! isset( $_POST['_wcv-submit_feedback'] ) || ! wp_verify_nonce( $_POST['_wcv-submit_feedback'], 'wcv-submit_feedback' ) ) {
			return;
		}

		global $wpdb;

		// Iterate over each line item to leave feedback
		// TO-DO : find better way to validate data before entering during each loop
		// Only allow posting once.
		$err = true;

		foreach ( $_POST['wcv-feedback'] as $feedback ) {

			if ( empty( $feedback['star-rating'] ) && $feedback['rating_title'] == '' && $feedback['comments'] == '' ) {
				continue;
			}

			if ( empty( $feedback['star-rating'] ) ) {
				wc_add_notice( __( 'Please select a star rating.', 'wcvendors-pro' ) );

				return;
			}

			$update = array_key_exists( 'feedback_id', $feedback ) ? true : false;

			if ( $update ) {
				$res = $wpdb->update(
					$wpdb->prefix . self::$table_name,
					array(
						'rating'       => (int) $feedback['star-rating'],
						'order_id'     => (int) $_POST['wcv-order_id'],
						'vendor_id'    => (int) $feedback['vendor_id'],
						'product_id'   => (int) $feedback['product_id'],
						'customer_id'  => (int) $feedback['customer_id'],
						'comments'     => stripslashes( $feedback['comments'] ),
						'rating_title' => stripslashes( $feedback['rating_title'] ),
					),
					array( 'id' => $feedback['feedback_id'] ),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
					)
				);
			} else {
				$res = $wpdb->insert(
					$wpdb->prefix . self::$table_name,
					array(
						'rating'       => (int) $feedback['star-rating'],
						'order_id'     => (int) $_POST['wcv-order_id'],
						'vendor_id'    => (int) $feedback['vendor_id'],
						'product_id'   => (int) $feedback['product_id'],
						'customer_id'  => (int) $feedback['customer_id'],
						'comments'     => stripslashes( $feedback['comments'] ),
						'rating_title' => stripslashes( $feedback['rating_title'] ),
					),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
					)
				);

			}

			if ( $update ) {
				$entry_id = $feedback['feedback_id'];
			} else {
				$entry_id = $wpdb->insert_id;
			}

			do_action_deprecated( 'wcv_save_product_feedback', array( $feedback, $update, $entry_id, $res ), '1.7.10', 'wcvendors_save_product_feedback' );
			do_action( 'wcvendors_save_product_feedback', $feedback, $update, $entry_id, $res );
		}

		if ( $err ) {
			$notice = __( 'Your feedback has been saved.', 'wcvendors-pro' );
		} else {
			$notice = __( 'There was an error posting your feedback.', 'wcvendors-pro' );
		}

		wc_add_notice( $notice, 'success' );

		$orders_endpoint = get_option( 'woocommerce_myaccount_orders_endpoint' );
		wp_safe_redirect( apply_filters( 'wcv_ratings_redirect', get_permalink( wc_get_page_id( 'myaccount' ) ) . $orders_endpoint ) );

		exit;

	} // process_form_submission()

	/**
	 *  Display the feedback form
	 *
	 * @since    1.0.0
	 */
	public function feedback_form() {
		if ( ! class_exists( 'WC_Vendors' ) ) {
			return;
		}

		if ( ! isset( $_GET['wcv_order_id'] ) || ! is_user_logged_in() || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'wcv-leave_feedback' ) ) {
			if ( is_page() ) {

				echo sprintf( apply_filters( 'wcv_feedback_page_error_msg', __( '<p>This page should not be accessed directly. Please return to the <a href="%s">my account page</a> and select an order to leave feedback. </p>', 'wcvendors-pro' ) ), get_permalink( wc_get_page_id( 'myaccount' ) ) );
			}
			return;
		}

		// Template variables
		$order_id = $_GET['wcv_order_id'];
		$feedback = $this->get_order_feedback( $order_id );
		$order    = new WC_Order( $order_id );
		$products = $order->get_items();

		ob_start();
		wc_get_template(
			'feedback-form.php',
			array(
				'order_id'   => $order_id,
				'feedback'   => $feedback,
				'order'      => $order,
				'order_date' => $order->get_date_created(),
				'products'   => $products,
			),
			'wc-vendors/front/ratings/',
			$this->base_dir . 'templates/front/ratings/'
		);

		return ob_get_clean();

	} // feedback_form()

	/**
	 * Feedback link action that hooks into the my account order page
	 *
	 * @since    1.0.0
	 * @version  1.6.0
	 *
	 * @param    string $actions actions array
	 * @param    object $order   the order object
	 */
	public function feedback_link_action( $actions, $order ) {
		$feedback_status = get_option( 'wcvendors_feedback_order_status', 'processing' );
		if ( 'processing' == $feedback_status ) {
			if ( ! $order || ! $order->has_status( 'processing' ) ) {
				if ( ! $order || ! $order->has_status( 'completed' ) ) {
					return $actions;
				}
			}
		} else {
			if ( ! $order || ! $order->has_status( 'completed' ) ) {
				return $actions;
			}
		}
		$feedback_text             = $this->get_feedback_text( $order->get_id() );
		$feedback_form_page        = get_option( 'wcvendors_feedback_page_id', null );
		$actions['leave_feedback'] = array(
			'url'  => wp_nonce_url( add_query_arg( 'wcv_order_id', $order->get_id(), get_permalink( $feedback_form_page ) ), 'wcv-leave_feedback' ),
			'name' => apply_filters( 'wcv_feedback_btn_text', $feedback_text ),
		);
		return apply_filters( 'wcv_ratings_my_account_my_orders_actions', $actions, $order, $feedback_text );
	} // feedback_link_action()

	/**
	 * Generate the ratings URL
	 *
	 * @since    1.0.0
	 *
	 * @param    int  $vendor_id the vendor id to generate
	 * @param    bool $link      output a link, otherwise just the rating
	 */
	public static function ratings_link( $vendor_id, $link = true, $link_text = '' ) {

		$feedback_form_page = get_option( 'wcvendors_feedback_page_id', null );
		$feedback_system    = wc_string_to_bool( get_option( 'wcvendors_feedback_system', 'no' ) );

		$url = apply_filters( 'wcv_ratings_link_url', WCVendors_Pro_Vendor_Controller::get_vendor_store_url( $vendor_id ) . 'ratings/' );

		if ( $feedback_form_page ) {
			$ratings_count   = self::get_ratings_count( $vendor_id );
			$ratings_average = self::get_ratings_average( $vendor_id );
			include 'partials/ratings/public/wcvendors-pro-ratings-link.php';
		}
	}

	/**
	 * Add leave feedback button on the downloads page
	 *
	 * @param   array $download
	 * @return  void
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function add_feedback_link( $download ) {
		$order = wc_get_order( $download['order_id'] );
		echo '<a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file button alt">' . esc_html( $download['download_name'] ) . '</a>';

		if ( ! empty( $order ) && is_a( $order, 'WC_Order' ) ) {
			$feedback_text      = $this->get_feedback_text( $order->get_id() );
			$feedback_form_page = get_option( 'wcvendors_feedback_page_id', null );
			$url                = wp_nonce_url( add_query_arg( 'wcv_order_id', $order->get_id(), get_permalink( $feedback_form_page ) ), 'wcv-leave_feedback' );
			$name               = apply_filters( 'wcv_feedback_btn_text', $feedback_text );
			echo apply_filters(
				'wcv_download_leave_feedback_button',
				'<a href="' . $url . '" class="woocommerce-button button leave_feedback">' . esc_html( $feedback_text ) . '</a>',
				$url,
				$feedback_text
			);
		}
	}
	/**
	 * Get the text to request feedback based on the order status
	 *
	 * @param   int $order_id
	 * @return  string
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function get_feedback_text( $order_id ) {
		$existing_feedback = $this->get_order_feedback( $order_id );
		$feedback_text     = ( ! empty( $existing_feedback ) ) ? __( 'Revise Feedback', 'wcvendors-pro' ) : __( 'Leave Feedback', 'wcvendors-pro' );

		return apply_filters( 'wcv_feedback_text', $feedback_text, $order_id );
	} //get_feedback_text()

	/**
	 * Get the feedback for an order
	 *
	 * @since    1.0.0
	 *
	 * @param    int $order_id the order id to get
	 */
	public function get_order_feedback( $order_id ) {

		global $wpdb;

		$order_id = (int) $order_id;

		$table_name = $wpdb->prefix . self::$table_name;

		$feedback = $wpdb->get_results(
			$wpdb->prepare(
				"
		SELECT * FROM $table_name
		WHERE order_id = %d
		",
				$order_id
			)
		);

		return $feedback;

	} // get_order_feedback()

	/**
	 * Get the vendor feedback
	 *
	 * @since    1.0.0
	 *
	 * @param    int $vendor_id the vendor id to get feedback for
	 */
	public static function get_vendor_feedback( $vendor_id, $number = - 1 ) {

		global $wpdb;

		$vendor_id = (int) $vendor_id;

		$table_name = $wpdb->prefix . self::$table_name;

		$sort_order = get_option( 'wcvendors_feedback_sort_order', 'desc' );

		$limit = $number > 0 ? " LIMIT $number" : '';

		$feedback = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT * FROM $table_name
			WHERE vendor_id = %d
			ORDER BY postdate $sort_order $limit
			",
				$vendor_id
			)
		);

		return $feedback;

	} // get_vendor_feedback()

	/**
	 * Get the product feedback
	 *
	 * @since    1.0.0
	 *
	 * @param    int $vendor_id the vendor id to get feedback for
	 */
	public function get_product_feedback( $product_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . self::$table_name;

		$sort_order = get_option( 'wcvendors_feedback_sort_order', 'desc' );

		$feedback = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT * FROM $table_name
			WHERE product_id = %d
			ORDER BY postdate $sort_order
			",
				$product_id
			)
		);

		return $feedback;

	} // get_product_feedback()

	/**
	 * Get the feedback
	 *
	 * @since    1.0.0
	 *
	 * @param    int $feedback_id the feedback id
	 */
	public function get_feedback( $feedback_id ) {

		global $wpdb;

		$feedback_id = (int) $feedback_id;

		$table_name = $wpdb->prefix . self::$table_name;

		$sort_order = get_option( 'wcvendors_feedback_sort_order', 'desc' );

		$feedback = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT * FROM $table_name
			WHERE id = %d
			ORDER BY postdate $sort_order
			",
				$feedback_id
			)
		);

		return $feedback;

	} // get_feedback()

	/**
	 * Get the feedback
	 *
	 * @since    1.0.0
	 *
	 * @param    int $vendor_id the vendor id to get the ratings average for
	 */
	public static function get_ratings_average( $vendor_id ) {

		global $wpdb;

		$vendor_ratings = array();
		$average_rating = '';

		$vendor_id = (int) $vendor_id;

		$count = self::get_ratings_count( $vendor_id );

		$table_name = $wpdb->prefix . self::$table_name;

		$ratings = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(rating) FROM $table_name
				WHERE vendor_id = %d
				",
				$vendor_id
			)
		);

		if ( $count > 0 ) {
			$average_rating = number_format( $ratings / $count, 1 );
		} else {
			$average_rating = 0;
		}

		return $average_rating;

	} // get_ratings_average()

	/**
	 * Get the feedback
	 *
	 * @since    1.0.0
	 *
	 * @param    int $vendor_id the vendor id to get the ratings average for
	 */
	public static function get_ratings_count( $vendor_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . self::$table_name;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT count(rating) FROM $table_name
				WHERE vendor_id = %s
				",
				$vendor_id
			)
		);

		return $count;

	} // get_ratings_count()

	/**
	 * Get the number of ratings for a product
	 *
	 * @param int $product_id
	 *
	 * @return int number of ratings
	 * @since 1.5.6
	 */
	public static function get_product_ratings_count( $product_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::$table_name;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT count(rating) FROM $table_name
				WHERE product_id = %s
				",
				$product_id
			)
		);

		return $count;
	} //get_product_ratings_count()

	/**
	 * Get the average rating for the product
	 *
	 * @param int $product_id
	 *
	 * @return int average rating for product
	 * @since 1.5.6
	 */
	public static function get_product_average_rating( $product_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::$table_name;

		$count = self::get_product_ratings_count( $product_id );

		$sum = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT SUM(rating) FROM $table_name
				WHERE product_id = %s
				",
				$product_id
			)
		);

		if ( $sum ) {
			return $sum / $count;
		}

		return 0;

	} //get_product_average_rating()

	/**
	 * The main admin page for the ratings
	 *
	 * @since    1.0.0
	 */
	public function admin_page_setup() {

		$hook = add_submenu_page(
			'wc-vendors',
			__( 'Vendor Ratings', 'wcvendors-pro' ),
			__( 'Vendor Ratings', 'wcvendors-pro' ),
			'manage_woocommerce',
			'wcv_pro_vendor_feedback',
			array(
				$this,
				'ratings_admin_page',
			)
		);

		include 'class-wcvendors-pro-ratings-admin-table.php';

		add_filter( 'set-screen-option', array( $this, 'ratings_set_option' ), 10, 3 );

		add_action( "load-$hook", array( 'WCVendors_Pro_Ratings_Admin_Table', 'add_options' ) );

	} // admin_page_setup()

	public function ratings_set_option( $status, $option, $value ) {
		return $value;
	}

	/**
	 *  Load the admin ratings table in the wp-admin dashboard
	 *
	 * @since    1.0.0
	 */
	public function ratings_admin_page() {

		include_once 'class-wcvendors-pro-ratings-admin-table.php';

		$ratings_table = new WCVendors_Pro_Ratings_Admin_Table( $this->wcvendors_pro, $this->version, $this->debug, self::$table_name );

		include apply_filters( 'wcvendors_pro_ratings_admin_page_table_title_path', 'partials/ratings/admin/wcvendors-pro-ratings-table-title.php' );

		// Display the edit form without the items table
		if ( 'edit' === $ratings_table->current_action() ) {

			$id       = $_GET['wcv_vendor_rating_id'];
			$feedback = $this->get_feedback( $id );
			$ratings_table->display_edit_form( $feedback );

		} else {

			// Process the single item actions
			if ( isset( $_POST['action'] ) && 'save' === $_POST['action'] ) {

				$id       = $_POST['rating_id'];
				$comments = $_POST['rating_comments'];
				$title    = $_POST['rating_title'];

				$feedback = array(
					'id'              => $id,
					'rating_title'    => $title,
					'rating_comments' => $comments,
				);

				$result = $ratings_table->update_rating( $feedback );

				if ( $result ) {
					$message = sprintf( __( '%s rating updated.', 'wcvendors-pro' ), wcv_get_vendor_name() );
					include apply_filters( 'wcvendors_pro_ratings_admin_page_table_notice_path', 'partials/ratings/admin/wcvendors-pro-ratings-table-notice.php' );
				}
			} elseif ( 'delete' === $ratings_table->current_action() ) {

				$id = $_GET['wcv_vendor_rating_id'];

				if ( isset( $id ) ) {

					$result = $ratings_table->delete_ratings( $id );

					if ( $result ) {
						$message = sprintf( __( '%s rating deleted.', 'wcvendors-pro' ), wcv_get_vendor_name() );
						include apply_filters( 'wcvendors_pro_ratings_admin_page_table_notice_path', 'partials/ratings/admin/wcvendors-pro-ratings-table-notice.php' );
					}
				}
			}

			include apply_filters( 'wcvendors_pro_ratings_admin_page_table_path', 'partials/ratings/admin/wcvendors-pro-ratings-table.php' );

		}

		include apply_filters( 'wcvendors_pro_ratings_admin_page_table_end_path', 'partials/ratings/admin/wcvendors-pro-ratings-table-end.php' );

	} // ratings_admin_page()

	/**
	 *  Add the vendor ratings tab on the front end
	 *
	 * @since    1.0.0
	 */
	public function vendor_ratings_panel_tab( $tabs ) {

		global $post;

		$feedback_display = wc_string_to_bool( get_option( 'wcvendors_feedback_display', 'no' ) );

		if ( WCV_Vendors::is_vendor( $post->post_author ) && ! $feedback_display ) {

			$vendor_ratings_label = __( get_option( 'wcvendors_vendor_ratings_label', __( 'Product ratings', 'wcvendors-pro' ) ), 'wcvendors-pro' );

			$tabs['vendor_ratings_tab'] = apply_filters(
				'wcv_vendor_ratings_tab',
				array(
					'title'    => $vendor_ratings_label,
					'priority' => 50,
					'callback' => array( $this, 'vendor_ratings_panel' ),
				)
			);
		}

		return $tabs;

	} // vendor_ratings_panel_tab()

	/**
	 *
	 */

	/**
	 *  Add the vendor ratings information for this product to the front end
	 *
	 * @since    1.0.0
	 */
	public function vendor_ratings_panel() {

		global $product;

		$product_feedback = $this->get_product_feedback( $product->get_id() );
		$post             = get_post( $product->get_id() );

		echo self::ratings_link( $post->post_author, true, __( 'View All Ratings <br /><br />', 'wcvendors-pro' ) );

		if ( $product_feedback ) {

			foreach ( $product_feedback as $pf ) {

				$customer      = get_userdata( $pf->customer_id );
				$rating        = $pf->rating;
				$rating_title  = $pf->rating_title;
				$comment       = $pf->comments;
				$post_date     = date_i18n( get_option( 'date_format' ), strtotime( $pf->postdate ) );
				$customer_name = ucfirst( $customer->display_name );

				wc_get_template(
					'ratings-display-panel.php',
					array(
						'rating'        => $rating,
						'rating_title'  => $rating_title,
						'comment'       => $comment,
						'customer_name' => $customer_name,
						'post_date'     => $post_date,

					),
					'wc-vendors/front/ratings/',
					$this->base_dir . 'templates/front/ratings/'
				);
			}
		} else {

			echo __( 'No ratings have been submitted for this product yet.', 'wcvendors-pro' );
		}

	} // vendor_ratings_panel()

	/**
	 *  Update Table Headers for display of vendor ratings
	 *
	 * @since    1.0.0
	 *
	 * @param     array $headers array passed via filter
	 */
	public function table_columns() {

		$columns = array(
			'ID'          => __( 'ID', 'wcvendors-pro' ),
			'order_id'    => __( 'Order #', 'wcvendors-pro' ),
			'feedback'    => __( 'Feedback', 'wcvendors-pro' ),
			'product_id'  => __( 'Product', 'wcvendors-pro' ),
			'customer_id' => __( 'Customer', 'wcvendors-pro' ),
			'postdate'    => __( 'Date', 'wcvendors-pro' ),
		);

		return $columns;

	} // table_columns()

	/**
	 *  Retrieve the vendor ratings data
	 *
	 * @since    1.0.0
	 * @version  1.7.8
	 * @return   array  $new_rows   array of stdClass objects passed back to the filter
	 */
	public function table_rows() {

		$feedback = self::get_vendor_feedback( get_current_user_id() );

		$new_rows = array();

		foreach ( $feedback as $fb ) {

			$customer = get_userdata( $fb->customer_id );
			$product  = wc_get_product( $fb->product_id );

			// If the product doesn't exist, skip it.
			if ( ! is_a( $product, 'WC_Product' ) ) {
				continue;
			}

			$product_name  = $product->get_title();
			$customer_name = ucfirst( $customer->display_name );
			$feedback      = '';
			$date          = date_i18n( get_option( 'date_format' ), strtotime( $fb->postdate ) );

			include 'partials/ratings/admin/wcvendors-pro-ratings-feedback.php';

			$new_row              = new stdClass();
			$new_row->ID          = $fb->id;
			$new_row->order_id    = $fb->order_id;
			$new_row->feedback    = $feedback;
			$new_row->product_id  = $product->get_title();
			$new_row->customer_id = ucfirst( $customer->display_name );
			$new_row->postdate    = date_i18n( get_option( 'date_format' ), strtotime( $fb->postdate ) );

			$new_rows[] = $new_row;
		}

		return $new_rows;

	} // table_rows()

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_action_column( $column ) {

		$new_column = '';

		return $new_column;

	} // table_action_column()

	/**
	 *  Change the column that actions are displayed in
	 *
	 * @since    1.0.0
	 *
	 * @param     string $column column passed from filter
	 *
	 * @return   string $new_column    new column passed back to filter
	 */
	public function table_no_data_notice( $notice ) {

		$notice = __( 'No ratings found.', 'wcvendors-pro' );

		return $notice;

	}

	/**
	 * Display the custom order table
	 *
	 * @since    1.0.0
	 */
	public function display() {

		// Use the internal table generator to create object list
		$ratings_table = new WCVendors_Pro_Table_Helper( $this->wcvendors_pro, $this->version, 'rating', null, get_current_user_id() );

		$ratings_table->set_columns( $this->table_columns() );
		$ratings_table->set_rows( $this->table_rows() );

		// display the table
		$ratings_table->display();

	} // display()

	/**
	 * Display the custom order table
	 *
	 * @since    1.2.0
	 */
	public function display_vendor_ratings() {

		if ( ! is_admin() ) {

			if ( get_query_var( 'ratings' ) ) {

				$vendor_shop     = urldecode( get_query_var( 'vendor_shop' ) );
				$vendor_id       = WCV_Vendors::get_vendor_id( $vendor_shop );
				$vendor_feedback = self::get_vendor_feedback( $vendor_id );

				wc_get_template(
					'store-ratings.php',
					array(
						'vendor_shop'     => $vendor_shop,
						'vendor_id'       => $vendor_id,
						'vendor_feedback' => $vendor_feedback,
					),
					'wc-vendors/store/',
					$this->base_dir . 'templates/store/'
				);

				exit;

			}
		}

	} // display_vendor_ratings()

	/**
	 * Add the query vars
	 *
	 * @since    1.2.0
	 */
	public function add_query_vars( $query_vars ) {

		$query_vars[] = 'ratings';

		return $query_vars;

	} // add_query_vars()

	/**
	 * Add the ratings rewrite rule
	 *
	 * @since    1.2.0
	 */
	public function add_rewrite_rules( $rules ) {

		$permalink = untrailingslashit( get_option( 'wcvendors_vendor_shop_permalink', '' ) );

		// Remove beginning slash
		if ( substr( $permalink, 0, 1 ) == '/' ) {
			$permalink = substr( $permalink, 1, strlen( $permalink ) );
		}

		$ratings_rule = array( $permalink . '/([^/]*)/ratings' => 'index.php?post_type=product&vendor_shop=$matches[1]&ratings=all' );
		$rules        = $ratings_rule + $rules;

		return $rules;

	} //add_rewrite_rules()

	/**
	 *  Output a vendor ratings link
	 *
	 * @since   1.5.2
	 */
	public function wcv_feedback( $atts ) {

		extract(
			shortcode_atts(
				array(
					'vendor' => '',
				),
				$atts
			)
		);

		ob_start();

		if ( ! wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) ) ) {
			echo self::ratings_link( $vendor, true );
		}

		return ob_get_clean();

	} // wcv_feedback()


	/**
	 * Output the vendors total ratings if it is a vendor page
	 *
	 * @since 1.7.0
	 */
	public function wcv_vendor_total_ratings( $atts ) {
		global $post;
		if ( WCV_Vendors::is_vendor_page() ) {
			$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
			$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		} elseif ( is_product() && WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
				$vendor_id = $post->post_author;
		} else {
			return;
		}

		ob_start();

		if ( ! wc_string_to_bool( get_option( 'wcvendors_ratings_management_cap', 'no' ) ) ) {
			echo self::ratings_link( $vendor_id, true );
		}

		return ob_get_clean();

	}

	/**
	 * Synchronize WCVendors Reviews and WooCommerce Reveiews
	 *
	 * @hooked to 'wcv_save_product_feedback'
	 *
	 * @param   $feedback   {array}     a list of feedback data:
	 *                  'star-rating'       => (int) the star rating (MIND THE DASH!!)
	 *                  'vendor_id'         => (int) vendor_id
	 *                  'product_id'        => (int) product_id
	 *                  'customer_id'       => (int) $customer_id
	 *                  'comments'          => (string) text of the feedback
	 *                  'rating_title'      => (string) title of the feedback
	 *                  'feedback_id'       => (int) the id of the feedback if it already exists and is just being updated
	 *
	 * @param   $update     {bool}      true if the review is being updated, false if it's a new review
	 * @param   $entry_id   {int}       the database entry ID of the inserted/updated feedback
	 * @param   $response   {mixed}     the response we got from the database action. false if failed
	 *
	 * @author  Andrea Piccart
	 * @link    http://bespokewebdeveloper.com
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	function sync_wcv_reviews_with_woo_reviews( $feedback, $update, $entry_id, $response ) {

		if ( ! wc_string_to_bool( get_option( 'wcvendors_feedback_sync_reviews', 'no' ) ) ) {
			return;
		}

		if ( ! $response || ! $feedback || ! $feedback['customer_id'] || ! $feedback['product_id'] || ! $entry_id ) {
			return;
		}

		$comment_ID = '';
		$comments   = get_comments(
			 array(
				 'meta_key'   => 'wcvendors_review_id',
				 'meta_value' => $entry_id,
				 'user_id'    => $feedback['customer_id'],
			 )
			);

		if ( $comments && is_array( $comments ) ) {

			foreach ( $comments as $comment ) {
				$comment_ID   = $comment->comment_ID;
				$comment_text = $comment->comment_content;
			}
		}

		if ( true === $update && $comment_ID ) {

			if ( $feedback['comments'] && $comment_text != $feedback['comments'] ) {

				$updatedata = array(
					'comment_ID'      => $comment_ID,
					'comment_content' => stripslashes( $feedback['comments'] ),
				);

				wp_update_comment( $updatedata );
			}

			if ( $feedback['star-rating'] ) {
				update_comment_meta( $comment_ID, 'rating', $feedback['star-rating'] );
			}

			if ( $feedback['rating_title'] ) {
				update_comment_meta( $comment_ID, 'review_title', $feedback['rating_title'] );
			}
		} else {

			if ( false === get_post_status( $feedback['product_id'] ) ) {
				return;
			}

			$author_data = get_user_by( 'id', $feedback['customer_id'] );

			$commentdata = array(
				'comment_post_ID'      => $feedback['product_id'],
				'comment_author'       => $author_data->display_name,
				'comment_author_email' => $author_data->user_email,
				'comment_author_url'   => $author_data->user_url,
				'comment_content'      => $feedback['comments'],
				'comment_type'         => 'review',
				'comment_parent'       => 0,
				'user_id'              => $feedback['customer_id'],
			);

			$comment_id = wp_new_comment( $commentdata );

			add_comment_meta( $comment_id, 'wcvendors_review_id', $entry_id );
			add_comment_meta( $comment_id, 'rating', $feedback['star-rating'] );
			add_comment_meta( $comment_id, 'review_title', $feedback['rating_title'] );
		}
	}

} // End WCVendors_Pro_Ratings
