<?php

if ( ! class_exists ( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists ( 'WC_Admin_List_Table_Products' ) ) {
	require_once WC_ABSPATH . 'includes/admin/list-tables/class-wc-admin-list-table-products.php';

	new WC_Admin_List_Table_Products();
}

/**
 * List table class
 */
class Class_Admin_Network_Products_List_Table extends \WP_List_Table {
	private $query;

	function __construct() {
		parent::__construct( array(
			'singular' => 'Product',
			'plural'   => 'Products',
			'ajax'     => false,
			'screen'   => 'woocommerce_page_woonet-woocommerce-products',
		) );


	}

	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	function no_items() {
		_e( 'No products found.', 'woonet' );
	}

	/**
	 * Get the column names
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'network_sites' => __( 'Network Sites', 'woonet' ),
			'thumb'         => '<span class="wc-image tips" data-tip="' . __( 'Image', 'woonet' ) . '">' . __( 'Image', 'woonet' ) . '</span>',
			'name'          => __( 'Name', 'woonet' ),
			'sku'      	    => __( 'SKU', 'woonet' ),
			'in_stock'      => __( 'Stock', 'woonet' ),
			'price'         => __( 'Price', 'woonet' ),
			'categories'    => __( 'Categories', 'woonet' ),
			'product_type'  => __( 'Type', 'woonet' ),
			'date'          => __( 'Date', 'woonet' ),
		);

		return $columns;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column, in this case, 'title'.
	 */
	protected function get_primary_column_name() {
		return $this->get_default_primary_column_name();
	}

	protected function get_default_primary_column_name() {
		return 'name';
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', true ),
			'date' => array( 'date', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : '';

		if ( $post_status ==  'trash' ) {
			$actions = array(
				'untrash' => __( 'Restore', 'woonet' ),
				'delete'  => __( 'Delete Permanently', 'woonet' ),
			);
		} else {
			$actions = array(
				'edit'              => __( 'Edit', 'woonet' ),
				'trash'             => __( 'Move to Trash', 'woonet' ),
			);
		}

		return $actions;
	}

	/**
	 * Render the checkbox column
	 *
	 * @param  WP_Post $item
	 */
	function column_cb( $item ) {
		if ( current_user_can( 'edit_post', get_the_ID() ) ): ?>
			<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php
				printf( __( 'Select %s' ), _draft_or_post_title() );
				?></label>
			<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="post[]" value="<?php the_ID(); ?>" />
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php
					printf(
					/* translators: %s: post title */
						__( '&#8220;%s&#8221; is locked' ),
						_draft_or_post_title()
					);
					?></span>
			</div>
		<?php endif;
	}

	public function column_date( $item ) {
		global $mode, $post;

		if ( '0000-00-00 00:00:00' == $post->post_date ) {
			$t_time = $h_time = __( 'Unpublished', 'woonet' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'woonet' ) );
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS )
				$h_time = sprintf( __( '%s ago', 'woonet' ), human_time_diff( $time ) );
			else
				$h_time = mysql2date( __( 'Y/m/d', 'woonet' ), $m_time );
		}


		if ( 'excerpt' == $mode ) {

			/**
			 * Filter the published time of the post.
			 *
			 * If $mode equals 'excerpt', the published time and date are both displayed.
			 * If $mode equals 'list' (default), the publish date is displayed, with the
			 * time and date together available as an abbreviation definition.
			 *
			 * @since 2.5.1
			 *
			 * @param array   $t_time      The published time.
			 * @param WP_Post $post        Post object.
			 * @param string  $column_name The column name.
			 * @param string  $mode        The list display mode ('excerpt' or 'list').
			 */
			echo apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode );
		} else {

			/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
			echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) . '</abbr>';
		}
		echo '<br />';
		if ( 'publish' == $post->post_status ) {
			_e( 'Published', 'woonet' );
		} elseif ( 'future' == $post->post_status ) {
			if ( $time_diff > 0 )
				echo '<strong class="attention">' . __( 'Missed schedule', 'woonet' ) . '</strong>';
			else
				_e( 'Scheduled', 'woonet' );
		} else {
			_e( 'Last Modified', 'woonet' );
		}
	}

	public function column_network_sites( $item ) {
		global $WOO_MSTORE;

		$template = '<span class="id">%s</span>';

		$blog_names = array(
			sprintf( $template, get_blog_option( $item->blog_id, 'blogname' ) )
		);

		$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $blog_id ) {
			$product_is_published_to = get_post_meta( $item->id, '_woonet_publish_to_' . $blog_id, true );

			if ( 'yes' == $product_is_published_to ) {
				$blog_name = sprintf( $template, get_blog_option( $blog_id, 'blogname' ) );
				if ( ! in_array( $blog_name, $blog_names ) ) {
					$blog_names[] = $blog_name;
				}
			}
		}

		echo implode( '<br />', $blog_names );
	}

	public function column_name( $item ) {
		do_action( 'manage_product_posts_custom_column', 'name', $item->id );
	}

	public function column_product_type( $item ) {
		/**
		 * @var WC_Product $product
		 */
		global $product;

		switch ( $product->get_type() ) {
			case 'grouped':
				$data = array( 'class' => 'grouped', 'name' => __( 'Grouped', 'woonet' ) );
				break;
			case 'external':
				$data = array( 'class' => 'external', 'name' => __( 'External/Affiliate', 'woonet' ) );
				break;
			case 'variable':
				$data = array( 'class' => 'variable', 'name' => __( 'Variable', 'woonet' ) );
				break;
			case 'simple':
				if ( $product->is_virtual() ) {
					$data = array( 'class' => 'virtual', 'name' => __( 'Virtual', 'woonet' ) );
				} elseif ( $product->is_downloadable() ) {
					$data = array( 'class' => 'downloadable', 'name' => __( 'Downloadable', 'woonet' ) );
				} else {
					$data = array( 'class' => 'simple', 'name' => __( 'Simple', 'woonet' ) );
				}
				break;
			default:
				$data = array( 'class' => '', 'name' => '' );
				break;
		}

		echo '<span class="product-type tips ' . $data['class'] . '" data-tip="' . $data['name'] . '">' . $data['name'] . '</span>';
	}

	public function column_categories( $item ) {
		global $product;

		$categories = wp_get_object_terms( array( $product->get_id() ), 'product_cat', array( 'fields' => 'names' ) );
		if ( count( $categories ) > 0 ) {
			echo implode( ', ', $categories );
		}
	}

	public function column_price( $item ) {
		global $product;

		echo $product->get_price_html() ? $product->get_price_html() : '<span class="na">&ndash;</span>';
	}

	public function column_sku( $item ) {
		global $product;

		echo $product->get_sku() ? $product->get_sku() : '<span class="na">&ndash;</span>';
	}

	public function column_in_stock( $item ) {
		global $product;

		if ( $product->is_in_stock() ) {
			echo '<mark class="instock">' . __( 'In stock', 'woonet' ) . '</mark>';
		} else {
			echo '<mark class="outofstock">' . __( 'Out of stock', 'woonet' ) . '</mark>';
		}

		if ( $product->managing_stock() ) {
			echo ' &times; ' . $product->get_stock_quantity();
		}
	}

	public function column_thumb( $item ) {
		global $product;

		echo '<a href="' . get_edit_post_link( $product->get_id() ) . '">' . $product->get_image( 'thumbnail' ) . '</a>';
		echo '<input style="display: none" class="network-cb-select" name="network-post[]" value="' . $item->blog_id . '_' . $product->get_id() . '" type="checkbox" />';
	}

	private function count_posts() {
		global $wpdb, $WOO_MSTORE;

		$counts = array_fill_keys( get_post_stati(), 0 );

		$args = array(
			'product_from_shops' => empty( $_REQUEST['product_from_shops'] ) ? '' : intval( $_REQUEST['product_from_shops'] ),
			'search'             => empty( $_REQUEST['s'] )                  ? '' : esc_sql( $_REQUEST['s'] ),
		);

		if ( empty( $args['product_from_shops'] ) ) {
			$query_template = "
			SELECT post_status, COUNT( * ) AS num_posts
			FROM %1\$s AS p
			JOIN %2\$s AS pm ON pm.post_id=p.ID
			WHERE p.post_type='product' AND pm.meta_key='_woonet_network_main_product' %3\$s
			GROUP BY post_status";

			$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		} else {
			$query_template = "
			SELECT post_status, COUNT( * ) AS num_posts
			FROM %1\$s AS p
			WHERE p.post_type='product' %3\$s
			GROUP BY post_status";

			$blog_ids = array( $args['product_from_shops'] );
		}
		foreach ( $blog_ids as $blog_id ) {
			$query = sprintf(
				$query_template,
				$wpdb->get_blog_prefix( $blog_id ) . 'posts',
				$wpdb->get_blog_prefix( $blog_id ) . 'postmeta',
				empty( $args['search'] ) ? '' : " AND (post_title LIKE '%{$args['search']}%' OR post_content LIKE '%{$args['search']}%')"
			);

			$results = (array) $wpdb->get_results( $query, ARRAY_A );

			foreach ( $results as $row ) {
				if ( in_array( $row['post_status'], array_keys( $counts ) ) ) {
					$counts[ $row['post_status'] ] += $row['num_posts'];
				}
			}
		}

		$counts = (object) $counts;

		return $counts;
	}

	/**
	 * Set the views
	 *
	 * @return array
	 */
	protected function get_views() {
		$status_links = array();
		$num_posts = $this->count_posts();
		$total_posts = array_sum( (array) $num_posts );
		$class = '';

		$all_args = array( 'page' => 'woonet-woocommerce-products' );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		if ( empty( $class ) && ( $this->is_base_request() || isset( $_REQUEST['all_posts'] ) ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'posts'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = $this->get_view_link( $all_args, $all_inner_html, $class );

		$avail_post_stati = wp_edit_posts_query();
		foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) || empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset($_REQUEST['post_status']) && $status_name === $_REQUEST['post_status'] ) {
				$class = 'current';
			}

			$all_args['post_status'] = $status_name;

			$status_label = sprintf(
				translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
				number_format_i18n( $num_posts->$status_name )
			);

			$status_links[ $status_name ] = $this->get_view_link( $all_args, $status_label, $class );
		}

		return $status_links;
	}

	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @since 4.4.0
	 *
	 * @param array  $args  URL parameters for the link.
	 * @param string $label Link text.
	 * @param string $class Optional. Class attribute. Default empty string.
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {
		$url = add_query_arg( $args, 'edit.php' );

		$class_html = $aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$aria_current,
			$label
		);
	}

	protected function get_view_link( $args, $label, $class = '' ) {
		$url = add_query_arg( $args, 'admin.php' );

		$class_html = $aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$aria_current,
			$label
		);
	}

	/**
	 * Determine if the current view is the "All" view.
	 *
	 * @since 4.2.0
	 *
	 * @return bool Whether the current view is the "All" view.
	 */
	protected function is_base_request() {
		$vars = $_GET;
		unset( $vars['paged'] );

		return ( 1 === count( $vars ) && 'woonet-woocommerce-products' === $vars['page'] );
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = $this->get_items_per_page( 'products_per_page' );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$args = array(
			'offset'             => $offset,
			'number'             => $per_page,
			'post_status'        => empty( $_REQUEST['post_status'] ) ? '' : esc_sql( $_REQUEST['post_status'] ),
			'product_from_shops' => empty( $_REQUEST['product_from_shops'] ) ? '' : intval( $_REQUEST['product_from_shops'] ),
			'search'             => empty( $_REQUEST['s'] ) ? '' : esc_sql( $_REQUEST['s'] ),
		);

		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
			$args['order']   = $_REQUEST['order'];
		}

		$this->items = $this->get_products( $args );

		$this->set_pagination_args( array(
			'total_items' => $this->get_products_count(),
			'per_page'    => $per_page
		) );
	}


	/**
	 * Get all Product
	 *
	 * @param $args array
	 *
	 * @return array
	 */
	private function get_products( $args = array() ) {
		global $wpdb, $WOO_MSTORE;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'order'   => 'DESC',
			'orderby' => 'id',
		);
		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['search'] ) ) {
			$sub_query = "
				SELECT ID AS id, CAST( post_title AS CHAR CHARACTER SET utf8 ) AS name, post_date AS 'date', %1\$d AS blog_id
				FROM %2\$s
				WHERE post_type='product' ";
		} else {
			$sub_query = "
				SELECT ID AS id, CAST( post_title AS CHAR CHARACTER SET utf8 ) AS name, post_date AS 'date', %1\$d AS blog_id
				FROM %2\$s AS posts
				JOIN %3\$s AS postmeta ON postmeta.post_id = posts.ID AND postmeta.meta_key = '_sku'
				WHERE post_type='product' ";
		}

		if ( empty( $args['product_from_shops'] ) ) {
			$sub_query .= "
				 AND ID NOT IN
				(
					SELECT DISTINCT p.ID
					FROM %2\$s AS p
					JOIN %3\$s AS pm ON pm.post_id=p.ID
					WHERE p.post_type='product' AND pm.meta_key='_woonet_network_is_child_site_id'
				) ";

			$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		} else {
			$blog_ids = array( $args['product_from_shops'] );
		}

		$query = array();
		foreach ( $blog_ids as $blog_id ) {
			$query[$blog_id] = sprintf(
				$sub_query,
				$blog_id,
				$wpdb->get_blog_prefix( $blog_id ) . 'posts',
				$wpdb->get_blog_prefix( $blog_id ) . 'postmeta'
			);

			if ( empty( $args['post_status'] ) || 'all' == $args['post_status'] ) {
				$query[$blog_id] .= " AND post_status<>'trash' ";
			} else {
				$query[$blog_id] .= " AND post_status='{$args['post_status']}' ";
			}

			if ( ! empty( $args['search'] ) ) {
				$query[$blog_id] .= " AND (post_title LIKE '%{$args['search']}%' OR post_content LIKE '%{$args['search']}%' OR postmeta.meta_value LIKE '%{$args['search']}%')";
			}
		}

		$this->query = '(' . implode( ') UNION ALL (', $query ) . ')';
		$query       = sprintf(
			'%s ORDER BY %s %s LIMIT %d, %d',
			$this->query,
			$args['orderby'],
			$args['order'],
			$args['offset'],
			$args['number']
		);

		$items = $wpdb->get_results( $query );

		return $items;
	}

	/**
	 * Get all products count from database
	 *
	 * @return integer
	 */
	private function get_products_count() {
		global $wpdb;

		if ( empty( $this->query ) ) {
			$result = 0;
		} else {
			$result = (int) $wpdb->get_var( "SELECT COUNT(*) FROM ({$this->query}) AS result" );
		}

		return $result;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 3.1.0
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		global $WOO_MSTORE;

		$product_from_shops = isset( $_GET['product_from_shops'] ) ? trim( $_GET['product_from_shops'] ) : '';

		echo '<div class="alignleft actions">';
			if ( 'top' === $which && !is_singular() ) {
				echo '<label for="filter-by-shop" class="screen-reader-text">' . __( 'Filter by shop', 'woonet' ) . '</label>';
				echo '<select name="product_from_shops" id="filter-by-shop">';
					printf(
						'<option %s value="0">%s</option>',
						selected( '', $product_from_shops, false ),
						__( 'Show products from all shops', 'woonet' )
					);

					$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
					foreach ( $blog_ids as $blog_id ) {
						printf( "<option %s value='%s'>%s</option>\n",
							selected( $blog_id, $product_from_shops, false ),
							esc_attr( $blog_id ),
							get_blog_option( $blog_id, 'blogname' )
						);
					}
				echo '</select>';
				submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			}
		echo '</div>';
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 *
	 * @global string $mode List table view mode.
	 */
	public function inline_edit() {
		global $mode, $post, $WOO_MSTORE;

		$blog_categories = array();
		$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );

			$blog_categories[ $blog_id ] = wp_terms_checklist( null, array( 'taxonomy' => 'product_cat', 'echo' => false ) );

			restore_current_blog();
		}
		printf(
			'<script>const blog_categories=%s;</script>',
			json_encode( $blog_categories )
		);

		$item = $this->items[0];

		$current_blog_id = get_current_blog_id();

		$global_post = $post;
		if ( $current_blog_id != $item->blog_id ) {
			switch_to_blog( $item->blog_id );
		}

		$post = get_post( $item->id );

		$screen = $this->screen;

//		$post = get_default_post_to_edit( 'product' );
		$post_type_object = get_post_type_object( 'product' );

		$taxonomy_names = get_object_taxonomies( 'product' );
		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomy_names as $taxonomy_name ) {

			$taxonomy = get_taxonomy( $taxonomy_name );

			$show_in_quick_edit = $taxonomy->show_in_quick_edit;

			/**
			 * Filters whether the current taxonomy should be shown in the Quick Edit panel.
			 *
			 * @since 4.2.0
			 *
			 * @param bool   $show_in_quick_edit Whether to show the current taxonomy in Quick Edit.
			 * @param string $taxonomy_name      Taxonomy name.
			 * @param string $post_type          Post type of current Quick Edit post.
			 */
			if ( ! apply_filters( 'quick_edit_show_taxonomy', $show_in_quick_edit, $taxonomy_name, 'product' ) ) {
				continue;
			}

			if ( $taxonomy->hierarchical )
				$hierarchical_taxonomies[] = $taxonomy;
			else
				$flat_taxonomies[] = $taxonomy;
		}

		$m = ( isset( $mode ) && 'excerpt' === $mode ) ? 'excerpt' : 'list';
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
		$core_columns = array( 'cb' => true, 'date' => true, 'title' => true, 'categories' => true, 'tags' => true, 'comments' => true, 'author' => true );

		?>

		<form method="get"><table style="display: none"><tbody id="inlineedit">
				<?php
				$hclass = count( $hierarchical_taxonomies ) ? 'post' : 'page';
				$inline_edit_classes = "inline-edit-row inline-edit-row-$hclass";
				$bulk_edit_classes   = "bulk-edit-row bulk-edit-row-$hclass bulk-edit-product";
				$quick_edit_classes  = "quick-edit-row quick-edit-row-$hclass inline-edit-product";

				$bulk = 0;
				while ( $bulk < 2 ) { ?>

					<tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="<?php echo $inline_edit_classes . ' ';
					echo $bulk ? $bulk_edit_classes : $quick_edit_classes;
					?>" style="display: none"><td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

							<fieldset class="inline-edit-col-left">
								<legend class="inline-edit-legend"><?php echo $bulk ? __( 'Bulk Edit' ) : __( 'Quick Edit' ); ?></legend>
								<div class="inline-edit-col">
									<?php

									if ( post_type_supports( 'product', 'title' ) ) :
										if ( $bulk ) : ?>
											<div id="bulk-title-div">
												<div id="bulk-titles"></div>
											</div>

										<?php else : // $bulk ?>

											<label>
												<span class="title"><?php _e( 'Title' ); ?></span>
												<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value="" /></span>
											</label>

											<label>
												<span class="title"><?php _e( 'Slug' ); ?></span>
												<span class="input-text-wrap"><input type="text" name="post_name" value="" /></span>
											</label>

										<?php endif; // $bulk
									endif; // post_type_supports title ?>

									<?php if ( !$bulk ) : ?>
										<fieldset class="inline-edit-date">
											<legend><span class="title"><?php _e( 'Date' ); ?></span></legend>
											<?php touch_time( 1, 1, 0, 1 ); ?>
										</fieldset>
										<br class="clear" />
									<?php endif; // $bulk

									if ( post_type_supports( 'product', 'author' ) ) :
										$authors_dropdown = '';

										if ( current_user_can( $post_type_object->cap->edit_others_posts ) ) :
											$users_opt = array(
												'hide_if_only_one_author' => false,
												'who' => 'authors',
												'name' => 'post_author',
												'class'=> 'authors',
												'multi' => 1,
												'echo' => 0,
												'show' => 'display_name_with_login',
											);
											if ( $bulk )
												$users_opt['show_option_none'] = __( '&mdash; No Change &mdash;' );

											if ( $authors = wp_dropdown_users( $users_opt ) ) :
												$authors_dropdown  = '<label class="inline-edit-author">';
												$authors_dropdown .= '<span class="title">' . __( 'Author' ) . '</span>';
												$authors_dropdown .= $authors;
												$authors_dropdown .= '</label>';
											endif;
										endif; // authors
										?>

										<?php if ( !$bulk ) echo $authors_dropdown;
									endif; // post_type_supports author

									if ( !$bulk && $can_publish ) :
										?>

										<div class="inline-edit-group wp-clearfix">
											<label class="alignleft">
												<span class="title"><?php _e( 'Password' ); ?></span>
												<span class="input-text-wrap"><input type="text" name="post_password" class="inline-edit-password-input" value="" /></span>
											</label>

											<em class="alignleft inline-edit-or">
												<?php
												/* translators: Between password field and private checkbox on post quick edit interface */
												_e( '&ndash;OR&ndash;' );
												?>
											</em>
											<label class="alignleft inline-edit-private">
												<input type="checkbox" name="keep_private" value="private" />
												<span class="checkbox-title"><?php _e( 'Private' ); ?></span>
											</label>
										</div>

									<?php endif; ?>

								</div></fieldset>

							<?php if ( count( $hierarchical_taxonomies ) && !$bulk ) : ?>

								<fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

										<?php foreach ( $hierarchical_taxonomies as $taxonomy ) : ?>

											<span class="title inline-edit-categories-label"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
											<input type="hidden" name="<?php echo ( $taxonomy->name === 'category' ) ? 'post_category[]' : 'tax_input[' . esc_attr( $taxonomy->name ) . '][]'; ?>" value="0" />
											<ul class="cat-checklist <?php echo esc_attr( $taxonomy->name )?>-checklist">
												<?php wp_terms_checklist( null, array( 'taxonomy' => $taxonomy->name ) ) ?>
											</ul>

										<?php endforeach; //$hierarchical_taxonomies as $taxonomy ?>

									</div></fieldset>

							<?php endif; // count( $hierarchical_taxonomies ) && !$bulk ?>

							<fieldset class="inline-edit-col-right"><div class="inline-edit-col">

									<?php
									if ( post_type_supports( 'product', 'author' ) && $bulk )
										echo $authors_dropdown;

									if ( post_type_supports( 'product', 'page-attributes' ) ) :

										if ( $post_type_object->hierarchical ) :
											?>
											<label>
												<span class="title"><?php _e( 'Parent' ); ?></span>
												<?php
												$dropdown_args = array(
													'post_type'         => $post_type_object->name,
													'selected'          => $post->post_parent,
													'name'              => 'post_parent',
													'show_option_none'  => __( 'Main Page (no parent)' ),
													'option_none_value' => 0,
													'sort_column'       => 'menu_order, post_title',
												);

												if ( $bulk )
													$dropdown_args['show_option_no_change'] =  __( '&mdash; No Change &mdash;' );

												/**
												 * Filters the arguments used to generate the Quick Edit page-parent drop-down.
												 *
												 * @since 2.7.0
												 *
												 * @see wp_dropdown_pages()
												 *
												 * @param array $dropdown_args An array of arguments.
												 */
												$dropdown_args = apply_filters( 'quick_edit_dropdown_pages_args', $dropdown_args );

												wp_dropdown_pages( $dropdown_args );
												?>
											</label>

										<?php
										endif; // hierarchical

										if ( !$bulk ) : ?>

											<label>
												<span class="title"><?php _e( 'Order' ); ?></span>
												<span class="input-text-wrap"><input type="text" name="menu_order" class="inline-edit-menu-order-input" value="<?php echo $post->menu_order ?>" /></span>
											</label>

										<?php
										endif; // !$bulk
									endif; // page-attributes
									?>

									<?php if ( 0 < count( get_page_templates( null, 'product' ) ) ) : ?>
										<label>
											<span class="title"><?php _e( 'Template' ); ?></span>
											<select name="page_template">
												<?php	if ( $bulk ) : ?>
													<option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option>
												<?php	endif; // $bulk ?>
												<?php
												/** This filter is documented in wp-admin/includes/meta-boxes.php */
												$default_title = apply_filters( 'default_page_template_title',  __( 'Default Template' ), 'quick-edit' );
												?>
												<option value="default"><?php echo esc_html( $default_title ); ?></option>
												<?php page_template_dropdown( '', 'product' ) ?>
											</select>
										</label>
									<?php endif; ?>

									<?php if ( count( $flat_taxonomies ) && !$bulk ) : ?>

										<?php foreach ( $flat_taxonomies as $taxonomy ) : ?>
											<?php if ( current_user_can( $taxonomy->cap->assign_terms ) ) :
												$taxonomy_name = esc_attr( $taxonomy->name );

												?>
												<label class="inline-edit-tags">
													<span class="title"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
													<textarea data-wp-taxonomy="<?php echo $taxonomy_name; ?>" cols="22" rows="1" name="tax_input[<?php echo $taxonomy_name; ?>]" class="tax_input_<?php echo $taxonomy_name; ?>"></textarea>
												</label>
											<?php endif; ?>

										<?php endforeach; //$flat_taxonomies as $taxonomy ?>

									<?php endif; // count( $flat_taxonomies ) && !$bulk  ?>

									<?php if ( post_type_supports( 'product', 'comments' ) || post_type_supports( 'product', 'trackbacks' ) ) :
										if ( $bulk ) : ?>

											<div class="inline-edit-group wp-clearfix">
												<?php if ( post_type_supports( 'product', 'comments' ) ) : ?>
													<label class="alignleft">
														<span class="title"><?php _e( 'Comments' ); ?></span>
														<select name="comment_status">
															<option value=""><?php _e( '&mdash; No Change &mdash;' ); ?></option>
															<option value="open"><?php _e( 'Allow' ); ?></option>
															<option value="closed"><?php _e( 'Do not allow' ); ?></option>
														</select>
													</label>
												<?php endif; if ( post_type_supports( 'product', 'trackbacks' ) ) : ?>
													<label class="alignright">
														<span class="title"><?php _e( 'Pings' ); ?></span>
														<select name="ping_status">
															<option value=""><?php _e( '&mdash; No Change &mdash;' ); ?></option>
															<option value="open"><?php _e( 'Allow' ); ?></option>
															<option value="closed"><?php _e( 'Do not allow' ); ?></option>
														</select>
													</label>
												<?php endif; ?>
											</div>

										<?php else : // $bulk ?>

											<div class="inline-edit-group wp-clearfix">
												<?php if ( post_type_supports( 'product', 'comments' ) ) : ?>
													<label class="alignleft">
														<input type="checkbox" name="comment_status" value="open" />
														<span class="checkbox-title"><?php _e( 'Allow Comments' ); ?></span>
													</label>
												<?php endif; if ( post_type_supports( 'product', 'trackbacks' ) ) : ?>
													<label class="alignleft">
														<input type="checkbox" name="ping_status" value="open" />
														<span class="checkbox-title"><?php _e( 'Allow Pings' ); ?></span>
													</label>
												<?php endif; ?>
											</div>

										<?php endif; // $bulk
									endif; // post_type_supports comments or pings ?>

									<div class="inline-edit-group wp-clearfix">
										<label class="inline-edit-status alignleft">
											<span class="title"><?php _e( 'Status' ); ?></span>
											<select name="_status">
												<?php if ( $bulk ) : ?>
													<option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option>
												<?php endif; // $bulk ?>
												<?php if ( $can_publish ) : // Contributors only get "Unpublished" and "Pending Review" ?>
													<option value="publish"><?php _e( 'Published' ); ?></option>
													<option value="future"><?php _e( 'Scheduled' ); ?></option>
													<?php if ( $bulk ) : ?>
														<option value="private"><?php _e( 'Private' ) ?></option>
													<?php endif; // $bulk ?>
												<?php endif; ?>
												<option value="pending"><?php _e( 'Pending Review' ); ?></option>
												<option value="draft"><?php _e( 'Draft' ); ?></option>
											</select>
										</label>

										<?php if ( 'post' === 'product' && $can_publish && current_user_can( $post_type_object->cap->edit_others_posts ) ) : ?>

											<?php	if ( $bulk ) : ?>

												<label class="alignright">
													<span class="title"><?php _e( 'Sticky' ); ?></span>
													<select name="sticky">
														<option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option>
														<option value="sticky"><?php _e( 'Sticky' ); ?></option>
														<option value="unsticky"><?php _e( 'Not Sticky' ); ?></option>
													</select>
												</label>

											<?php	else : // $bulk ?>

												<label class="alignleft">
													<input type="checkbox" name="sticky" value="sticky" />
													<span class="checkbox-title"><?php _e( 'Make this post sticky' ); ?></span>
												</label>

											<?php	endif; // $bulk ?>

										<?php endif; // 'post' && $can_publish && current_user_can( 'edit_others_cap' ) ?>

									</div>

									<?php

									if ( $bulk && current_theme_supports( 'post-formats' ) && post_type_supports( 'product', 'post-formats' ) ) {
										$post_formats = get_theme_support( 'post-formats' );

										?>
										<label class="alignleft">
											<span class="title"><?php _ex( 'Format', 'post format' ); ?></span>
											<select name="post_format">
												<option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option>
												<option value="0"><?php echo get_post_format_string( 'standard' ); ?></option>
												<?php
												if ( is_array( $post_formats[0] ) ) {
													foreach ( $post_formats[0] as $format ) {
														?>
														<option value="<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></option>
														<?php
													}
												}
												?>
											</select></label>
										<?php

									}

									?>

								</div></fieldset>

							<?php
							list( $columns ) = $this->get_column_info();

							foreach ( $columns as $column_name => $column_display_name ) {
								if ( isset( $core_columns[$column_name] ) )
									continue;

								if ( $bulk ) {

									/**
									 * Fires once for each column in Bulk Edit mode.
									 *
									 * @since 2.7.0
									 *
									 * @param string  $column_name Name of the column to edit.
									 * @param WP_Post $post_type   The post type slug.
									 */
									do_action( 'bulk_edit_custom_box', $column_name, 'product' );
								} else {

									/**
									 * Fires once for each column in Quick Edit mode.
									 *
									 * @since 2.7.0
									 *
									 * @param string $column_name Name of the column to edit.
									 * @param string $post_type   The post type slug, or current screen name if this is a taxonomy list table.
									 * @param string taxonomy     The taxonomy name, if any.
									 */
									do_action( 'quick_edit_custom_box', $column_name, 'product', '' );
								}

							}
							?>
							<div class="submit inline-edit-save">
								<button type="button" class="button cancel alignleft"><?php _e( 'Cancel' ); ?></button>
								<?php if ( ! $bulk ) {
									wp_nonce_field( 'inlineeditnonce', '_inline_edit', false );
									?>
									<button type="button" class="button button-primary save alignright"><?php _e( 'Update' ); ?></button>
									<span class="spinner"></span>
								<?php } else {
									submit_button( __( 'Update' ), 'primary alignright', 'bulk_edit', false );
								} ?>
								<input type="hidden" name="post_view" value="<?php echo esc_attr( $m ); ?>" />
								<input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
								<?php if ( ! $bulk && ! post_type_supports( 'product', 'author' ) ) { ?>
									<input type="hidden" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
								<?php } ?>
								<br class="clear" />
								<div class="notice notice-error notice-alt inline hidden">
									<p class="error"></p>
								</div>
							</div>
						</td></tr>
					<?php
					$bulk++;
				}
				?>
				</tbody></table></form>
		<?php

		if ( $current_blog_id != $item->blog_id ) {
			restore_current_blog();
		}
		$post = $global_post;
	}

	public function single_row( $item, $level = 0 ) {
		$global_post    = get_post();
		$global_product = wc_get_product();

		$current_blog_id = get_current_blog_id();

		if ( $current_blog_id != $item->blog_id ) {
			switch_to_blog( $item->blog_id );
		}

		$this->current_level = $level;
		$post = get_post( $item->id );

		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$classes = 'iedit author-' . ( get_current_user_id() == $post->post_author ? 'self' : 'other' );

		$lock_holder = wp_check_post_lock( $post->ID );
		if ( $lock_holder ) {
			$classes .= ' wp-locked';
		}

		if ( $post->post_parent ) {
			$count = count( get_post_ancestors( $post->ID ) );
			$classes .= ' level-'. $count;
		} else {
			$classes .= ' level-0';
		}
		?>
		<tr id="post-<?php echo $post->ID; ?>" class="<?php echo implode( ' ', get_post_class( $classes, $post->ID ) ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php

		if ( $current_blog_id != $item->blog_id ) {
			restore_current_blog();
		}

		$GLOBALS['post']    = $global_post;
		$GLOBALS['product'] = $global_product;
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @since 4.3.0
	 *
	 * @param object $post        Post being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string Row actions output for posts.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		global $WOO_MSTORE;

		if ( $primary !== $column_name ) {
			return '';
		}

		$actions = $this->handle_product_actions( get_post( $item->id ) );
		$out = $this->row_actions( $actions );

		$master_product_blog_id = get_current_blog_id();
		$blog_ids = $WOO_MSTORE->functions->get_active_woocommerce_blog_ids();
		foreach ( $blog_ids as $blog_id ) {
			if ( $blog_id == $master_product_blog_id ) {
				continue;
			}

			switch_to_blog( $blog_id );

				$slave_product_id = $WOO_MSTORE->product_interface->get_slave_product_id( $master_product_blog_id, $item->id );

				if ( $slave_product_id ) {
					$actions = $this->handle_product_actions( get_post( $slave_product_id ), true );
					$out .= $this->row_actions( $actions );
				}

			restore_current_blog();
		}

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}

	protected function handle_product_actions( $post, $is_slave_product = false ) {
		$actions = array();

		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );

		if ( $is_slave_product ) {
			$blog_name = get_blog_option( null, 'blogname' );
			$actions[] = array( 'id',  'ID: ' . $post->ID . ' ' . $blog_name );
		} else {
			$actions[] = array( 'id',  'ID: ' . $post->ID );
		}

		$title = _draft_or_post_title();

		if ( $can_edit_post && 'trash' != $post->post_status ) {
			$actions[] = array(
				'edit',
				sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					get_edit_post_link( $post->ID ),
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
					__( 'Edit' )
				)
			);
			if ( ! $is_slave_product ) {
				$actions[] = array(
					'inline hide-if-no-js',
					sprintf(
						'<a href="#" class="editinline" aria-label="%s">%s</a>',
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline' ), $title ) ),
						__( 'Quick&nbsp;Edit' )
					)
				);
			}
		}

		if ( is_post_type_viewable( $post_type_object ) ) {
			if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
				if ( $can_edit_post ) {
					$preview_link = get_preview_post_link( $post );
					$actions[] = array(
					   'view',
						sprintf(
							'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
							esc_url( $preview_link ),
							/* translators: %s: post title */
							esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ),
							__( 'Preview' )
						)
					);
				}
			} elseif ( 'trash' != $post->post_status ) {
				$actions[] = array(
					'view',
					sprintf(
						'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
						get_permalink( $post->ID ),
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
						__( 'View' )
					)
				);
			}
		}

		return $actions;
	}

	/**
	 * Generate row actions div
	 *
	 * @since 3.1.0
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action ) {
			++$i;

			list( $action, $link ) = $action;

			$sep = ( $i == $action_count || empty( $action) ) ? '' : ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 *
	 * @param array $items
	 */
	public function display_rows( $items = array() ) {
		if ( empty( $items) ) {
			foreach ( $this->items as $item ) {
				$this->single_row( $item );
			}
		} else {
			foreach ( $items as $item ) {
				$this->single_row( $item );
			}
		}
	}
}
