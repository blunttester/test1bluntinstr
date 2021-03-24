<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_MSTORE_SINGLE_EDITOR_INTEGRATION {

	var $product_interface;
	var $product_fields;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 10, 0 );
	}

	public function init() {

		if ( ! woomulti_has_valid_license() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'wp_enqueue_woocommerce_style' ) );

		if ( woomulti_has_min_user_role() ) {
			add_action( 'quick_edit_custom_box', array( $this, 'quick_edit' ), 20, 2 );
			add_action( 'add_inline_data', array( $this, 'add_quick_edit_inline_data' ) );
			// @todo: check capability

			// single product tab integration
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_multistore_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_multistore_panel' ) );

			// show site filter on the product page
			add_filter( 'woocommerce_products_admin_list_table_filters', array( $this, 'add_products_parent_child_filter' ) );
			add_filter( 'posts_clauses', array( $this, 'filter_parent_child_post_clauses' ) );
		}
	}

	function wp_enqueue_woocommerce_style() {
		wp_enqueue_style( 'woonet_admin', WOO_MSTORE_URL . '/../assets/css/admin.css' );

		$screen = get_current_screen();
		if ( empty( $screen ) || empty( $screen->id ) ) {
			return;
		}

		if ( in_array( $screen->id, array( 'edit-product', 'woocommerce_page_woonet-woocommerce-products-network' ) ) ) {
			wp_enqueue_script( 'quick-bulk-edit-woonet', WOO_MSTORE_URL . '/../assets/single/quick-bulk-edit.js', array( 'woocommerce_quick-edit' ), WOO_MSTORE_VERSION );
			wp_localize_script( 'quick-bulk-edit-woonet', 'woonet_options', get_option( 'site_option' ), array() );
		}
	}

	public function bulk_edit( $column_name, $post_type ) {
		error_log( 'Function was called in error. ' . __FILE__ . ':' . __LINE__ . ' | ' . wc_print_r( func_get_args(), true ) );
	}

	public function quick_edit( $column_name, $post_type ) {
		if ( 'price' != $column_name || 'product' != $post_type ) {
			return;
		}

		$screen = get_current_screen();

		if ( empty( $screen ) || empty( $screen->id ) ) {
			return;
		}

		if ( in_array( $screen->id, array( 'edit-product', 'woocommerce_page_woonet-woocommerce-products-network' ) ) ) {
			require_once WOO_MSTORE_PATH . '/templates/html-quick-edit-product.php';
		}
	}

	public function add_quick_edit_inline_data( $post ) {
		echo '<div class="hidden" id="woocommerce_multistore_inline_' . absint( $post->ID ) . '">';
		if ( get_option( 'woonet_network_type' ) == 'master' ) {
			echo '<div class="_is_master_product">yes</div>';

			$sites = woomulti_get_sites();

			foreach ( $sites as $site ) {
				$key        = '_woonet_publish_to_' . $site['uuid'];
				$publish_to = get_post_meta( $post->ID, $key, true );

				$key             = '_woonet_publish_to_' . $site['uuid'] . '_child_inheir';
				$inherit_updates = get_post_meta( $post->ID, $key, true );

				$key               = '_woonet_' . $site['uuid'] . '_child_stock_synchronize';
				$stock_synchronize = get_post_meta( $post->ID, $key, true );

				printf(
					'<div id=""> </div> <div class="_woonet_publish_to_%s">%s</div>
						 <div class="_woonet_publish_to_%s_child_inheir">%s</div>
						 <div class="_woonet_%s_child_stock_synchronize">%s</div>',
					$site['uuid'],
					wc_bool_to_string( $publish_to ),
					$site['uuid'],
					wc_bool_to_string( $inherit_updates ),
					$site['uuid'],
					wc_bool_to_string( $stock_synchronize )
				);
			}
		} else {
			echo '<div class="_is_master_product">no</div>';

			$master_blog_id    = 0;
			$inherit_updates   = get_post_meta( $post->ID, '_woonet_child_inherit_updates', true );
			$stock_synchronize = get_post_meta( $post->ID, '_woonet_child_stock_synchronize', true );

			printf(
				'<div class="master_blog_id">%s</div>
					 <div class="_woonet_child_inherit_updates">%s</div>
					 <div class="_woonet_child_stock_synchronize">%s</div>',
				intval( $master_blog_id ),
				wc_bool_to_string( $inherit_updates ),
				wc_bool_to_string( $stock_synchronize )
			);
		}

		echo '</div>';
	}

	public function add_multistore_tab() {
		printf(
			'<li class="woonet_tab"><a href="#woonet_data" rel="woonet_data"><span>%s</span></a></li>',
			__( 'MultiStore', 'woonet' )
		);
	}

	/**
	 * adds the panel to the product interface
	 */
	public function add_multistore_panel() {
		wp_enqueue_style( 'woosl-product', WOO_MSTORE_URL . '/../assets/css/woosl-product.css' );
		wp_enqueue_script( 'woosl-product', WOO_MSTORE_URL . '/../assets/single/product.js', array( 'jquery' ) );

		$this->define_fields();

		echo '<div id="woonet_data" class="panel woocommerce_options_panel" style="display:none;">';
		foreach ( $this->product_fields as $field ) {
			if ( ! is_array( $field ) ) {
				if ( $field == 'start_group' ) {
					echo '<div class="options_group">';
				} elseif ( $field == 'end_group' ) {
					echo '</div>';
				}

				continue;
			}

			switch ( $field['type'] ) {
				case 'heading':
					printf( '<h4>%s</h4>', $field['label'] );
					break;
				case 'description':
					printf(
						'<p class="form-field %s"><span class="description">%s</span></p>',
						$field['class'],
						wp_kses_post( $field['label'] )
					);
					break;
				case 'checkbox':
					printf(
						'<p class="form-field no_label %s" %s>',
						$field['class'],
						isset( $field['custom_attribute'] ) ? $field['custom_attribute'] : ''
					);
					if ( ! empty( $field['label'] ) ) {
						printf( '<label for="%s">%s</label>', $field['id'], $field['label'] );
					}

						$value = get_post_meta( get_the_ID(), $field['id'], true );
						printf(
							'<input type="hidden" name="%s" value="" /><input type="checkbox" id="%s" class="%s" %s %s %s />',
							$field['id'],
							$field['id'],
							$field['class'],
							empty( $field['disabled'] ) ? '' : 'disabled="disabled"',
							checked( wc_string_to_bool( isset( $field['checked'] ) ? $field['checked'] : $value ), true, false ),
							empty( $field['set_default_value'] ) ? '' : 'data-default-value="' . $value . '"'
						);

					if ( ! empty( $field['desc_tip'] ) ) {
						printf(
							'<img class="help_tip" data-tip="%s" src="%s/assets/images/help.png" height="16" width="16" />',
							esc_attr( $field['desc_tip'] ),
							esc_url( plugins_url() . '/woocommerce' )
						);
					}
						printf(
							'<span class="description">%s</span>',
							wp_kses_post( $field['description'] )
						);
					echo '</p>';
					break;
				default:
					$func = 'woocommerce_wp_' . $field['type'] . '_input';
					if ( function_exists( $func ) ) {
						$func( $field );
					}
					break;
			}
		}

		echo '</div>';
	}

	/**
	 * Define the custom new fields
	 */
	public function define_fields() {
		global $post;

		$_connect = get_option( 'woonet_master_connect' );

		if ( $this->product_fields ) {
			return;
		}

		$options = get_option( 'woonet_options' );

		if ( get_option( 'woonet_network_type' ) == 'child' ) {
			$this->product_fields[] = array(
				'id'      => '_woonet_title',
				'label'   => '&nbsp;',
				'type'    => 'heading',
				'no_save' => true,
			);

			if ( ! empty( $_connect['uuid'] ) && get_post_meta( $post->ID, '_woonet_publish_to_' . $_connect['uuid'] . '_child_inheir', true ) ) {
				$this->product_fields[] = array(
					'class'   => '_woonet_description inline',
					'label'   => __( 'Product is receiving updates from master. Child product, can\'t be re-published to other sites.', 'woonet' ),
					'type'    => 'description',
					'no_save' => true,
				);
			} else {
				$this->product_fields[] = array(
					'class'   => '_woonet_description inline',
					'label'   => __( 'Product belongs to this site and is not receiving any updates from master.', 'woonet' ),
					'type'    => 'description',
					'no_save' => true,
				);
			}

			$this->product_fields[] = array(
				'id'      => '_woonet_title',
				'label'   => '&nbsp;',
				'type'    => 'heading',
				'no_save' => true,
			);

			// if ( !empty($_connect['uuid']) ) {
			// $_woonet_child_inherit_updates = get_post_meta( $post->ID, '_woonet_publish_to_' . $_connect['uuid'] . '_child_inheir', true );
			// } else {
			// $_woonet_child_inherit_updates = get_post_meta( $post->ID, '_woonet_child_inherit_updates', true );
			// }

			// $this->product_fields[]        = array(
			// 'id'          => '_woonet_child_inherit_updates',
			// 'class'       => '_woonet_child_inherit_updates inline',
			// 'label'       => '',
			// 'description' => __( 'If checked, this product will inherit any parent updates', 'woonet' ),
			// 'type'        => 'checkbox',
			// 'value'       => $_woonet_child_inherit_updates,
			// );

			// if ( !empty($_connect['uuid']) ) {
			// $_woonet_child_stock_synchronize = get_post_meta( $post->ID, '_woonet_' . $_connect['uuid'] . '_child_stock_synchronize', true );
			// } else {
			// $_woonet_child_stock_synchronize = get_post_meta( $post->ID, '_woonet_child_stock_synchronize', true );
			// }

			// $this->product_fields[]          = array(
			// 'id'          => '_woonet_child_stock_synchronize',
			// 'class'       => '_woonet_child_stock_synchronize inline',
			// 'label'       => '',
			// 'description' => __( 'If checked, any stock change will synchronize across product tree.', 'woonet' ),
			// 'type'        => 'checkbox',
			// 'value'       => 'yes',
			// 'checked'     => ( $_woonet_child_stock_synchronize == 'yes' ) ? true : false,
			// 'disabled'    => ( $options['synchronize-stock'] == 'yes' ) ? true : false,
			// );
		} else {

			$this->product_fields[] = array(
				'id'          => 'woonet_toggle_all_sites',
				'class'       => 'woonet_toggle_all_sites inline',
				'label'       => '',
				'description' => __( 'Toggle all Sites', 'woonet' ),
				'type'        => 'checkbox',
				'value'       => '',
				'no_save'     => true,
			);

			$this->product_fields[] = array(
				'id'          => 'woonet_toggle_child_product_inherit_updates',
				'class'       => '_woonet_child_inherit_updates inline',
				'label'       => '',
				'description' => __( 'Toggle all Child product inherit Parent changes', 'woonet' ),
				'type'        => 'checkbox',
				'value'       => '',
				'no_save'     => true,
			);

			/**
			** Note
			*/
			$this->product_fields[] = array(
				'class'   => 'woomulti-quick-update-notice',
				'label'   => __( 'Note: A linked product (upsell, cross-sell or grouped product) needs to be synced with the child store before it can be synced as upsell, cross-sell or grouped product for a child store product.', 'woonet' ),
				'type'    => 'description',
				'no_save' => true,
			);

			$this->product_fields[] = array(
				'id'      => '_woonet_title',
				'label'   => __( 'Publish to', 'woonet' ),
				'type'    => 'heading',
				'no_save' => true,
			);

			$network_site_ids = get_option( 'woonet_child_sites' );

			foreach ( $network_site_ids as $network_site_id ) {

				$value = get_post_meta( $post->ID, '_woonet_publish_to_' . $network_site_id['uuid'], true );

				if ( true ) {
					$this->product_fields[] = array(
						'id'                => '_woonet_publish_to_' . $network_site_id['uuid'],
						'class'             => '_woonet_publish_to inline',
						'label'             => '',
						'description'       => '<b>' . esc_html( str_replace( array( 'http://', 'https://' ), '', $network_site_id['site_url'] ) ) . '</b><span class="warning">' . __( '<b>Warning:</b> By unselecting this shop the product is unasigned, but not deleted from the shop, which should be done manually.', 'woonet' ) . '</span>',
						'type'              => 'checkbox',
						'disabled'          => false,
						'set_default_value' => true,
						'custom_attribute'  => 'data-group-id=' . $network_site_id['uuid'],
						'save_callback'     => array( $this, 'field_process_publish_to' ),
					);

					$class = ' ';

					if ( 'yes' != $value ) {
						$class .= 'default_hide';
					}

					$_woonet_child_inherit_updates = get_post_meta( $post->ID, '_woonet_publish_to_' . $network_site_id['uuid'] . '_child_inheir', true );

					$_woonet_child_stock_synchronize = get_post_meta( $post->ID, '_woonet_' . $network_site_id['uuid'] . '_child_stock_synchronize', true );

					$this->product_fields[] = array(
						'id'          => '_woonet_publish_to_' . $network_site_id['uuid'] . '_child_inheir',
						'class'       => 'group_' . $network_site_id['uuid'] . ' _woonet_publish_to_child_inheir inline indent' . $class,
						'label'       => '',
						'description' => __( 'Child product inherit Parent changes', 'woonet' ),
						'type'        => 'checkbox',
						'value'       => 'yes',
						'checked'     => $_woonet_child_inherit_updates == 'yes' ? true : false,
						'disabled'    => '',
						'no_save'     => true,
					);

					$this->product_fields[] = array(
						'id'          => '_woonet_' . $network_site_id['uuid'] . '_child_stock_synchronize',
						'class'       => 'group_' . $network_site_id['uuid'] . ' _woonet_child_stock_synchronize inline indent' . $class,
						'label'       => '',
						'description' => __( 'If checked, any stock change will synchronize across product tree.', 'woonet' ),
						'type'        => 'checkbox',
						'value'       => 'yes',
						'checked'     => ( $_woonet_child_stock_synchronize == 'yes' ) ? true : false,
						'disabled'    => ( $options['synchronize-stock'] == 'yes' ) ? true : false,
						'no_save'     => true,
					);
				}
			}
		}

		$this->product_fields = apply_filters( 'WOO_MSTORE_admin_product\define_fields\product_fields', $this->product_fields );
	}

	/**
	 * Render the product filter on products page on the master site
	 *
	 * @return void
	 */
	public function render_products_parent_child_filter() {
		$current_value = isset( $_REQUEST['parent_child'] ) ? wc_clean( wp_unslash( $_REQUEST['parent_child'] ) ) : false; // WPCS: input var ok, sanitization ok.
		$options       = array();

		if ( get_option( 'woonet_network_type' ) == 'child' ) {
			$child_site_data = get_option( 'woonet_master_connect' );

			if ( ! empty( $child_site_data['uuid'] ) ) {
				$options[ $child_site_data['uuid'] ] = __( 'Synced from master', 'woonet' );
			}
		}

		$sites = get_option( 'woonet_child_sites' );

		if ( ! empty( $sites ) ) {
			foreach ( $sites as $site ) {
				$options[ $site['uuid'] ] = trim( str_replace( array( 'http://', 'https://' ), '', $site['site_url'] ), '/' );
			}
		}

		$output  = '<select name="parent_child">';
		$output .= '<option value="">' . esc_html__( 'Filter by site', 'woocommerce' ) . '</option>';

		foreach ( $options as $key => $label ) {
			$output .= '<option ' . selected( $key, $current_value, false ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		echo $output;
	}

	/**
	 * Add site filter.
	 *
	 * @param mixed $filters
	 * @return void
	 */
	public function add_products_parent_child_filter( $filters ) {
		$filters['parent_child'] = array( $this, 'render_products_parent_child_filter' );

		return $filters;
	}

	/**
	 * Filter by parent/child.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public function filter_parent_child_post_clauses( $args ) {

		global $wpdb;

		/**
		 * All master products. No need to filter.
		 */
		if ( ! empty( $_GET['parent_child'] ) && $_GET['parent_child'] == 'all' ) {
			return $args;
		}

		if ( ! empty( $_GET['parent_child'] ) ) {
			if ( ! strstr( $args['join'], 'wc_product_parent_child' ) ) {
				$args['join'] .= " LEFT JOIN {$wpdb->postmeta} wc_product_parent_child ON $wpdb->posts.ID = wc_product_parent_child.post_id ";
			}

			$args['where'] .= $wpdb->prepare( ' AND wc_product_parent_child.meta_key=%s AND wc_product_parent_child.meta_value=\'yes\' ', '_woonet_publish_to_' . $_GET['parent_child'] );

		}

		return $args;
	}
}


$GLOBALS['WOO_MSTORE_SINGLE_EDITOR_INTEGRATION'] = new WOO_MSTORE_SINGLE_EDITOR_INTEGRATION();
