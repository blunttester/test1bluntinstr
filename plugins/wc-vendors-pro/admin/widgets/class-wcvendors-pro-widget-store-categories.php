<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Categories Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Jamie Madden <support@wcvendors.com>
 * @version    1.4.4
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Categories extends WC_Widget {

	/**
	 * Category ancestors.
	 *
	 * @var array
	 */
	public $cat_ancestors;

	/**
	 * Current Category.
	 *
	 * @var bool
	 */
	public $current_cat;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->widget_cssclass    = 'wcv widget_product_categories';
		$this->widget_description = __( 'A list or dropdown of store categories.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_categories';
		$this->widget_name        = __( 'WC Vendors Store Categories', 'wcvendors-pro' );
		$this->settings           = array(
			'title'              => array(
				'type'  => 'text',
				'std'   => __( 'Store categories', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'orderby'            => array(
				'type'    => 'select',
				'std'     => 'name',
				'label'   => __( 'Order by', 'wcvendors-pro' ),
				'options' => array(
					'order' => __( 'Category order', 'wcvendors-pro' ),
					'name'  => __( 'Name', 'wcvendors-pro' ),
				),
			),
			'dropdown'           => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show as dropdown', 'wcvendors-pro' ),
			),
			'hierarchical'       => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show hierarchy', 'wcvendors-pro' ),
			),
			'count'              => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show product counts', 'wcvendors-pro' ),
			),
			'show_children_only' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Only show children of the current category', 'wcvendors-pro' ),
			),
			'hide_empty'         => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide empty categories', 'wcvendors-pro' ),
			),
			'show_globally'      => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show on main store page.', 'wcvendors-pro' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @see  WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @todo fix rewrite rules for the category URLs within the store
	 */
	public function widget( $args, $instance ) {

		global $wp_query, $post;

		if ( ! is_woocommerce() ) {
			return;
		}

		if ( ! $post ) {
			return;
		}

		$vendor_id          = 0;
		$count              = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
		$hierarchical       = isset( $instance['hierarchical'] ) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
		$show_children_only = isset( $instance['show_children_only'] ) ? $instance['show_children_only'] : $this->settings['show_children_only']['std'];
		$dropdown           = isset( $instance['dropdown'] ) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
		$orderby            = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->settings['orderby']['std'];
		$hide_empty         = isset( $instance['hide_empty'] ) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
		$show_globally      = isset( $instance['show_globally'] ) ? $instance['show_globally'] : $this->settings['show_globally']['std'];
		$dropdown_args      = array( 'hide_empty' => $hide_empty );
		$list_args          = array(
			'show_count'   => $count,
			'hierarchical' => $hierarchical,
			'taxonomy'     => 'product_cat',
			'hide_empty'   => $hide_empty,
		);

		// Menu Order
		$list_args['menu_order'] = false;
		if ( 'order' === $orderby ) {
			$list_args['menu_order'] = 'asc';
		} else {
			$list_args['orderby'] = 'title';
		}

		if ( WCV_Vendors::is_vendor_page() ) {
			$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
			$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
		} elseif ( is_singular( 'product' ) && WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
			$vendor_id = $post->post_author;
		} else {
			if ( isset( $_GET['wcv_vendor_id'] ) ) {
				$vendor_id = $_GET['wcv_vendor_id'];
			}
		}

		if ( $vendor_id == 0 && $show_globally == 0 ) {
			return;
		}

		$vendor_categories = WCVendors_Pro_Vendor_Controller::get_categories( $vendor_id );

		// Only show vendor categories
		$list_args['include'] = array_keys( $vendor_categories );
		// Carry the vendor category counts into the walker
		$list_args['vendor_categories'] = $vendor_categories;

		// Setup Current Category
		$this->current_cat   = false;
		$this->cat_ancestors = array();

		if ( is_tax( 'product_cat' ) ) {

			$this->current_cat   = $wp_query->queried_object;
			$this->cat_ancestors = get_ancestors( $this->current_cat->term_id, 'product_cat' );

		} elseif ( is_singular( 'product' ) ) {

			$product_category = wc_get_product_terms( $post->ID, 'product_cat', apply_filters( 'wcvendors-pro_product_categories_widget_product_terms_args', array( 'orderby' => 'parent' ) ) );

			if ( ! empty( $product_category ) ) {
				$this->current_cat   = end( $product_category );
				$this->cat_ancestors = get_ancestors( $this->current_cat->term_id, 'product_cat' );
			}
		}

		// Show Siblings and Children Only
		if ( $show_children_only && $this->current_cat ) {

			// Top level is needed
			$top_level = get_terms(
				'product_cat',
				array(
					'fields'       => 'ids',
					'parent'       => 0,
					'hierarchical' => true,
					'hide_empty'   => false,
				)
			);

			// Direct children are wanted
			$direct_children = get_terms(
				'product_cat',
				array(
					'fields'       => 'ids',
					'parent'       => $this->current_cat->term_id,
					'hierarchical' => true,
					'hide_empty'   => false,
				)
			);

			// Gather siblings of ancestors
			$siblings = array();
			if ( $this->cat_ancestors ) {
				foreach ( $this->cat_ancestors as $ancestor ) {
					$ancestor_siblings = get_terms(
						'product_cat',
						array(
							'fields'       => 'ids',
							'parent'       => $ancestor,
							'hierarchical' => false,
							'hide_empty'   => false,
						)
					);
					$siblings          = array_merge( $siblings, $ancestor_siblings );
				}
			}

			if ( $hierarchical ) {
				$include = array_merge( $top_level, $this->cat_ancestors, $siblings, $direct_children, array( $this->current_cat->term_id ) );
			} else {
				$include = array_merge( $direct_children );
			}

			$dropdown_args['include'] = implode( ',', $include );
			$list_args['include']     = implode( ',', $include );

			if ( empty( $include ) ) {
				return;
			}
		} elseif ( $show_children_only ) {

			$dropdown_args['depth']        = 1;
			$dropdown_args['child_of']     = 0;
			$dropdown_args['hierarchical'] = 1;
			$list_args['depth']            = 1;
			$list_args['child_of']         = 0;
			$list_args['hierarchical']     = 1;

		}

		$this->widget_start( $args, $instance );

		// Dropdown
		if ( $dropdown ) {

			// Only include the vendor categories in the drop down
			$dropdown_defaults = array(
				'show_count'         => $count,
				'hierarchical'       => $hierarchical,
				'show_uncategorized' => 0,
				'include'            => array_keys( $vendor_categories ),
				'orderby'            => $orderby,
				'id'                 => 'vendor_category',
				'name'               => 'vendor_category',
				'selected'           => $this->current_cat ? $this->current_cat->slug : '',
			);

			$dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults );

			// Stuck with this until a fix for https://core.trac.wordpress.org/ticket/13258
			wc_product_dropdown_categories( apply_filters( 'wcvendors_pro_product_categories_widget_dropdown_args', $dropdown_args ) );

			global $wp;
			$current_page = home_url( $wp->request );

			wc_enqueue_js(
				"
				jQuery( '.dropdown_product_cat' ).change( function() {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var current_page  = '" . esc_js( $current_page ) . "';
						if ( current_page.indexOf( '?' ) > 0 ) {
							this_page = current_page + '&vendor_category=' + jQuery(this).val();
						} else {
							this_page = current_page + '?vendor_category=' + jQuery(this).val();
						}
						location.href = this_page;
					}
				});
			"
			);

			// List
		} else {

			$list_args['walker']                     = new WCV_Store_Cat_List_Walker();
			$list_args['title_li']                   = '';
			$list_args['pad_counts']                 = 1;
			$list_args['show_option_none']           = __( 'No store categories exist.', 'wcvendors-pro' );
			$list_args['current_category']           = ( $this->current_cat ) ? $this->current_cat->term_id : '';
			$list_args['current_category_ancestors'] = $this->cat_ancestors;

			echo '<ul class="store-categories">';

			wp_list_categories( apply_filters( 'wcvendors-pro_store_categories_widget_args', $list_args ) );

			echo '</ul>';
		}

		$this->widget_end( $args );
	}
} // WCV_Widget_Store_Categories
