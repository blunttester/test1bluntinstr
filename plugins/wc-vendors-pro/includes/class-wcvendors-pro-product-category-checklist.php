<?php

/**
 * WCV_Walker_Category_Checklist class.
 *
 * @extends        Walker
 * @class          WCV_Walker_Category_Checklist
 * @version        1.1.4
 * @package        WCVendors_Pro
 * @subpackage     WCVendors_Pro/includes
 * @author         Jamie Madden <support@wcvendors.com>
 */
class WCV_Walker_Category_Checklist extends Walker {
	public $tree_type = 'category';
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
	); // TODO: decouple this

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see   Walker:start_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see   Walker::end_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see   Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		if ( empty( $args['taxonomy'] ) ) {
			$taxonomy = 'product_cat';
		} else {
			$taxonomy = $args['taxonomy'];
		}

		if ( $taxonomy == 'product_cat' ) {
			$name = 'product_cat[]';
		} else {
			$name = $taxonomy . '[]';
		}

		$args['popular_cats'] = empty( $args['popular_cats'] ) ? array() : $args['popular_cats'];
		$class                = in_array( $category->term_id, $args['popular_cats'] ) ? ' class="popular-category"' : '';

		$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];

		/** This filter is documented in wp-includes/category-template.php */
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
				   '<input class="wcv_category_check" value="' . $category->term_id . '" type="checkbox" name="' . $name . '" id="in-' . $taxonomy . '-' . $category->term_id . '"' .
				   checked( in_array( $category->term_id, $args['selected_cats'] ), true, false ) .
				   disabled( empty( $args['disabled'] ), false, false ) . ' ';

		if ( wc_string_to_bool( get_option( 'wcvendors_required_product_basic_categories', 'no' ) ) ) {
			$output .= ' required ';
		}

		if ( $category_limit = get_option( 'wcvendors_category_limit', '' ) ) {
			$output .= ' data-parsley-maxcheck="' . intval( $category_limit ) . '" ';
		}

		$output .= ' /> ';
		$output .= '<label class="selectit" for="in-' . $taxonomy . '-' . $category->term_id . '">' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';

	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see   Walker::end_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}
