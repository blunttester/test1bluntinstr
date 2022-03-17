<?php

/**
 * Form Helper Class
 *
 * Defines relevant static methods for generating form elements for public facing forms.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Form_Helper {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $wcvendors_pro The name of the plugin.
	 * @param    string $version       The version of this plugin.
	 */
	public function __construct( $wcvendors_pro, $version ) {

		$this->wcvendors_pro = $wcvendors_pro;
		$this->version       = $version;

	}

	/**
	 * Create an input field with label
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @Todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function input( $field ) {

		if ( empty( $field ) ) {
			return;
		}

		$allow_markup = 'yes' === get_option( 'wcvendors_allow_form_markup', 'no' ) ? true : false;

		$post_id                = isset( $field['post_id'] ) ? $field['post_id'] : 0;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['label']         = isset( $field['label'] ) ? $field['label'] : '&nbsp;';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['wrapper_start'] = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']   = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $post_id, $field['id'], true );
		$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
		$field['show_label']    = isset( $field['show_label'] ) ? $field['show_label'] : true;
		$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];
		$html                   = '';

		// Strip tags
		$field['value'] = ( $allow_markup ) ? $field['value'] : wp_strip_all_tags( $field['value'] );
		if ( $field['type'] == 'number' && $data_type == 'stock' ) {
			$field['value'] = (int) $field['value'];
		}

		if ( 'price' == $data_type ) {
			$field['value'] = wc_format_localized_price( $field['value'] );
		}

		// disable label for hidden
		$field['show_label'] = ( 'hidden' == $field['type'] ) ? false : true;

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			// Update validation rules to new system
			if ( array_key_exists( 'data-rules', $field['custom_attributes'] ) ) {
				$field['custom_attributes'] = self::check_custom_attributes( $field['custom_attributes'], $field['id'] );
			}

			foreach ( $field['custom_attributes'] as $attribute => $value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		do_action( 'wcv_form_input_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		if ( ! empty( $field['wrapper_class'] ) ) {
			echo '<div class="' . esc_attr( $field['wrapper_class'] ) . '">';
		}

		if ( 'hidden' !== $field['type'] ) {
			echo '<div class="control-group">';
		}

		// Change the output slightly for check boxes
		if ( 'checkbox' === $field['type'] ) {

			echo '<ul class="control unstyled inline" style="padding:0; margin:0;">';
			echo '<li><input type="checkbox" class="' . esc_attr( $field['class'] ) . ' ' . esc_attr( $field['wrapper_class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label></li>';
			echo '</ul>';

			if ( ! empty( $field['description'] ) ) {
				if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
					echo '<p class="tip">' . $field['description'] . '</p>';
				}
			}
		} else {

			if ( $field['show_label'] ) {
				echo '<label for="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['wrapper_class'] ) . '">' . wp_kses_post( $field['label'] );
			}
			echo '</label>';

			$html .= apply_filters( 'wcv_wp_input_start_' . $field['id'], $html );

			if ( 'hidden' !== $field['type'] ) {
				echo '<div class="control">';
			}

			if ( $data_type == 'decimal' || $data_type == 'price' || $data_type == 'number' ) {

				$type_number = 'data-parsley-type="number"';

				if ( ! empty( $custom_attributes ) && in_array( $type_number, $custom_attributes ) ) {
					$key = array_search( $type_number, $custom_attributes );
					unset( $custom_attributes[ $key ] );
				}

				if ( 'price' === $data_type ) {
					$custom_attributes[] = 'data-parsley-price';
				} else {
					$custom_attributes[] = 'data-parsley-decimal="."';
				}
			}

			echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . '  /> ';

			if ( 'hidden' !== $field['type'] ) {
				echo '</div>';
			}

			if ( ! empty( $field['description'] ) ) {
				if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
					echo '<p class="tip">' . $field['description'] . '</p>';
				}
			}

			$html .= apply_filters( 'wcv_wp_input_end_' . $field['id'], $html );

			echo $html;

		}

		if ( ! empty( $field['wrapper_class'] ) ) {
			echo '</div>';
		}

		if ( 'hidden' !== $field['type'] ) {
			echo '</div>';
		}

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_input_after_' . $field['id'], $field );

	}

	/**
	 * Create select with label
	 *
	 * @since      1.0.0
	 * @version    1.6.2
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function select( $field ) {

		$post_id                   = isset( $field['post_id'] ) ? $field['post_id'] : 0;
		$field['class']            = isset( $field['class'] ) ? $field['class'] : 'select2';
		$field['style']            = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class']    = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['wrapper_start']    = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']      = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['value']            = isset( $field['value'] ) ? $field['value'] : get_post_meta( $post_id, $field['id'], true );
		$field['show_option_none'] = isset( $field['show_option_none'] ) ? $field['show_option_none'] : '';
		$field['options']          = isset( $field['options'] ) ? $field['options'] : array();
		$field['taxonomy_field']   = isset( $field['taxonomy_field'] ) ? $field['taxonomy_field'] : 'slug';
		$field['show_label']       = isset( $field['show_label'] ) ? $field['show_label'] : true;
		$field['multiple']         = isset( $field['multiple'] ) && $field['multiple'] ? true : false;

		$field_name = $field['id'];
		if ( $field['multiple'] ) {
			$field_name .= '[]';
		}

		if ( isset( $field['name'] ) && $field['name'] ) {
			$field_name = $field['name'];
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ) {

				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		if ( $field['multiple'] ) {
			$custom_attributes[] = 'multiple="multiple"';
		}

		// Taxonomy drop down
		// @todo Support nested parent/child attributes
		if ( isset( $field['taxonomy'] ) && ( isset( $field['taxonomy_args'] ) && is_array( $field['taxonomy_args'] ) ) ) {

			// Default terms args
			$defaults = array(
				'orderby'           => 'name',
				'order'             => 'ASC',
				'hide_empty'        => true,
				'exclude'           => array(),
				'exclude_tree'      => array(),
				'include'           => array(),
				'number'            => '',
				'fields'            => 'all',
				'slug'              => '',
				'parent'            => '',
				'hierarchical'      => true,
				'child_of'          => 0,
				'get'               => '',
				'name__like'        => '',
				'description__like' => '',
				'pad_counts'        => false,
				'offset'            => '',
				'search'            => '',
				'cache_domain'      => 'core',
			);

			if ( 'product_cat' === $field['taxonomy'] ) {
				$existing_terms = wp_get_post_terms( $post_id, $field['taxonomy'], array( 'fields' => 'all' ) );
				$selected       = array();
				if ( ! empty( $existing_terms ) ) {
					foreach ( $existing_terms as $existing_term ) {
						$selected[] = $existing_term->term_id;
					}
				}
				$defaults = array_merge(
					 $defaults,
					array(
						'pad_counts'         => 1,
						'show_count'         => 0,
						'hierarchical'       => 1,
						'hide_empty'         => 1,
						'fields'             => 'all',
						'show_uncategorized' => 1,
						'orderby'            => 'name',
						'selected'           => $selected,
						'menu_order'         => false,
						'value'              => 'id',
					)
					);
			}

			// Merge args
			$args = wp_parse_args( $field['taxonomy_args'], $defaults );

			if ( $args['orderby'] == 'order' ) {
				$args['menu_order'] = 'asc';
				$args['orderby']    = 'name';
			}

			// Get terms for taxonomy
			$terms = get_terms( $field['taxonomy'], $args );

			if ( 'product_cat' === $field['taxonomy'] ) {
				$field['options'] = wcv_walk_category_dropdown_tree( $terms, 0, $args );
			} else {
				$options = array();
				foreach ( $terms as $term ) {
					$options[ $term->term_id ] = $term->name;
				}
				$field['options'] = $options;
			}
		}

		do_action( 'wcv_form_select_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<div class="control-group">';

		if ( $field['show_label'] ) {
			echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		}

		echo '<div class="control select">';

		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . $field_name . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';

		if ( ! empty( $field['show_option_none'] ) ) {
			echo '<option value>' . esc_html( $field['show_option_none'] ) . '</option>';
		}

		if ( isset( $field['taxonomy'] ) && 'product_cat' === $field['taxonomy'] ) {
			echo $field['options'];
		} else {
			foreach ( $field['options'] as $key => $value ) {
				$selected = '';
				if ( isset( $field['multiple'] ) && $field['multiple'] ) {
					$field_values = $field['value'];
					if ( ! is_array( $field['value'] ) ) {
						$field_values = explode( ',', $field['value'] );
					}
					if ( in_array( $key, $field_values ) ) {
						$selected = 'selected=\'selected\'';
					}
				} else {
					if ( is_array( $field['value'] ) ) {
						$selected = selected( esc_attr( reset( $field['value'] ) ), esc_attr( $key ), false );
					} else {
						$selected = selected( esc_attr( $field['value'] ), esc_attr( $key ), false );
					}
				}
				echo '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
			}
		}

		echo '</select> ';

		if ( ! empty( $field['description'] ) ) {
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<p class="tip">' . $field['description'] . '</p>';
			}
		}

		echo '</div>'; // control
		echo '</div>'; // control-group

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_select_after_' . $field['id'], $field );

	}

	/**
	 * Create select2 with label
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 * @todo       considering remove this.
	 */
	public static function select2( $field ) {

		$post_id                   = isset( $field['post_id'] ) ? $field['post_id'] : 0;
		$field['class']            = isset( $field['class'] ) ? $field['class'] : 'select2';
		$field['style']            = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class']    = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['wrapper_start']    = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']      = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['value']            = isset( $field['value'] ) ? $field['value'] : get_post_meta( $post_id, $field['id'], true );
		$field['show_option_none'] = isset( $field['show_option_none'] ) ? $field['show_option_none'] : '';
		$field['options']          = isset( $field['options'] ) ? $field['options'] : array();
		$field['custom_tax']       = isset( $field['custom_tax'] ) ? $field['custom_tax'] : false;

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ) {

				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Taxonomy drop down
		if ( isset( $field['taxonomy'] ) && ( isset( $field['taxonomy_args'] ) && is_array( $field['taxonomy_args'] ) ) ) {

			$existing_terms = wp_get_post_terms( $post_id, $field['taxonomy'], array( 'fields' => 'all' ) );

			$selected = array();
			if ( ! empty( $existing_terms ) ) {
				foreach ( $existing_terms as $existing_term ) {
					$selected[] = $existing_term->term_id;
				}
			}

			// Default terms args
			$defaults = apply_filters(
				'wcv_select2_args_' . $field['taxonomy'],
				array(
					'pad_counts'         => 1,
					'show_count'         => 0,
					'hierarchical'       => 1,
					'hide_empty'         => 1,
					'fields'             => 'all',
					'show_uncategorized' => 1,
					'orderby'            => 'name',
					'selected'           => $selected,
					'menu_order'         => false,
					'value'              => 'id',
				)
			);

			// Merge args
			$args = wp_parse_args( $field['taxonomy_args'], $defaults );

			if ( $args['orderby'] == 'order' ) {
				$args['menu_order'] = 'asc';
				$args['orderby']    = 'name';
			}

			// Get terms for taxonomy
			$terms = get_terms( $field['taxonomy'], $args );

			if ( ! $terms ) {
				return;
			}

			$field['options'] = wcv_walk_category_dropdown_tree( $terms, 0, $args );

		}

		do_action( 'wcv_form_select2_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<div class="control-group">';

		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

		echo '<div class="control select">';

		if ( ! empty( $field['description'] ) ) {
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<span data-tooltip data-tip="' . esc_attr( $field['description'] ) . '" aria-haspopup="true" class="has-tip right" title="' . __( esc_attr( $field['description'] ), 'wcvendors-pro' ) . '"><i class="wcv-icon wcv-icon-info-circle"></i></span>';
			}
		}

		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';

		if ( ! empty( $field['show_option_none'] ) ) {
			echo '<option value>' . esc_html( $field['show_option_none'] ) . '</option>';
		}

		// If taxonomy provided then display the custom walked drop down, otherwise iterate over provided options
		if ( isset( $field['taxonomy'] ) && ( isset( $field['taxonomy_args'] ) && is_array( $field['taxonomy_args'] ) ) ) {
			echo $field['options'];
		} else {
			foreach ( $field['options'] as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
			}
		}

		echo '</select> ';

		echo '</div>'; // control
		echo '</div>'; // control-group

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		if ( $field['custom_tax'] ) {

			$id = str_replace( '[]', '', $field['id'] );

			self::input(
				apply_filters(
					'wcv_form_select2_custom_tax_' . $field['id'],
					array(
						'post_id' => $post_id,
						'type'    => 'hidden',
						'id'      => 'track_' . $id,
						'value'   => '-1',
					)
				)
			);
		}

		do_action( 'wcv_form_select2_after_' . $field['id'], $field );

	}

	/**
	 * Create select2 with label
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function nested_select( $field ) {

		$post_id                   = isset( $field['post_id'] ) ? $field['post_id'] : 0;
		$field['class']            = isset( $field['class'] ) ? $field['class'] : 'select2';
		$field['style']            = isset( $field['style'] ) ? $field['style'] : 'width: 100%';
		$field['wrapper_class']    = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['wrapper_start']    = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']      = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['value']            = isset( $field['value'] ) ? $field['value'] : get_post_meta( $post_id, $field['id'], true );
		$field['show_option_none'] = isset( $field['show_option_none'] ) ? $field['show_option_none'] : '';
		$field['options']          = isset( $field['options'] ) ? $field['options'] : array();
		$field['value_type']       = isset( $field['value_type'] ) ? $field['value_type'] : 'value';

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ) {

				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		do_action( 'wcv_form_nested_select_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] );

		if ( ! empty( $field['description'] ) ) {
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<span data-tooltip data-tip="' . esc_attr( $field['description'] ) . '" aria-haspopup="true" class="has-tip right" title="' . __( esc_attr( $field['description'] ), 'wcvendors-pro' ) . '"><i class="wcv-icon wcv-icon-info-circle"></i></span>';
			}
		}

		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';

		if ( ! empty( $field['show_option_none'] ) ) {
			echo '<option value>' . esc_html( $field['show_option_none'] ) . '</option>';
		}

		foreach ( $field['options'] as $option_group => $option ) {

			echo '<optgroup label="' . $option_group . '">';

			foreach ( $option as $key => $value ) {
				$output = ( $field['value_type'] == 'value' ) ? $value : $key;
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $output ) . '</option>';
			}

			echo '</optgroup>';

		}

		echo '</select>';
		echo '</label>';

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_nested_select_after_' . $field['id'], $field );

	}

	/**
	 * Create a textarea with label
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function textarea( $field ) {

		$allow_markup           = 'yes' === get_option( 'wcvendors_allow_form_markup', 'no' ) ? true : false;
		$post_id                = isset( $field['post_id'] ) ? $field['post_id'] : 0;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['label']         = isset( $field['label'] ) ? $field['label'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['rows']          = isset( $field['rows'] ) ? $field['rows'] : 5;
		$field['cols']          = isset( $field['cols'] ) ? $field['cols'] : 5;
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_start'] = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']   = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $post_id, $field['id'], true );

		// Strip tags
		$field['value'] = ( $allow_markup ) ? $field['value'] : wp_strip_all_tags( $field['value'] );

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

			foreach ( $field['custom_attributes'] as $attribute => $value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		do_action( 'wcv_form_textarea_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<div class="control-group">';
		echo '<div class="control">';

		echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

		echo '<textarea class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '"  name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="' . $field['rows'] . '" cols="' . $field['cols'] . '" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

		echo '</div>';

		if ( ! empty( $field['description'] ) ) {
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<p class="tip">' . $field['description'] . '</p>';
			}
		}

		echo '</div>';

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_textarea_after_' . $field['id'], $field );
	}

	/**
	 * Output a woocommerce attribute select
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function attribute( $post_id ) {

		if ( 'yes' !== get_option( 'wcvendors_hide_product_basic_attributes', 'no' ) ) {

			// Array of defined attribute taxonomies
			$attribute_taxonomies = wc_get_attribute_taxonomies();

			// If there are any defined attributes display them
			if ( ! empty( $attribute_taxonomies ) ) {

				$i = 0;
				// Get any set attributes for the product
				$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

				foreach ( $attribute_taxonomies as $product_attribute ) {

					$current_attribute = '';
					$is_variation      = 'no';

					// If the attributes aren't empty, extract the attribute value for the current product
					// Does not support multi select at this time
					// TODO:  Support select2 and multiple attributes
					if ( ! empty( $attributes ) && array_key_exists( wc_attribute_taxonomy_name( $product_attribute->attribute_name ), $attributes ) ) {
						// get all terms
						$current_attribute = wp_get_post_terms( $post_id, wc_attribute_taxonomy_name( $product_attribute->attribute_name ) );
						$is_variation      = $attributes[ wc_attribute_taxonomy_name( $product_attribute->attribute_name ) ]['is_variation'] ? 'yes' : 'no';
						$current_attribute = reset( $current_attribute );
						$current_attribute = $current_attribute->slug;

					}

					// Output attribute select
					self::select(
						array(
							'id'               => 'attribute_values[' . $i . '][]',
							'post_id'          => $post_id,
							'label'            => ucfirst( wc_attribute_taxonomy_name( $product_attribute->attribute_name ) ),
							'value'            => $current_attribute,
							'show_option_none' => __( 'Select a ', 'wcvendors-pro' ) . ucfirst( $product_attribute->attribute_name ),
							'taxonomy'         => wc_attribute_taxonomy_name( $product_attribute->attribute_name ),
							'taxonomy_args'    => array(
								'hide_empty' => 0,
								'orderby'    => $product_attribute->attribute_orderby,
							),
						)
					);

					// Output attribute name hidden
					self::input(
						array(
							'post_id'    => $post_id,
							'id'         => 'attribute_names[' . $i . ']',
							'type'       => 'hidden',
							'show_label' => false,
							'value'      => wc_attribute_taxonomy_name( $product_attribute->attribute_name ),
						)
					);
					$i ++;
				}
			}

			// Support other plugins hooking into attributes
			// Not sure if this will work ?
			do_action( 'woocommerce_product_options_attributes' );

		}

	} //attribute()

	/**
	 * Output a the product images and hook into media uploader on front end
	 *
	 * @since      1.1.3
	 *
	 * @param      int $post_id the post id for the files being uploaded
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function product_media_uploader( $post_id ) {

		if ( 'yes' !== get_option( 'wcvendors_hide_product_media_featured', 'no' ) ) {

			do_action( 'wcv_form_product_media_uploader_before_product_media_uploader', $post_id );

			echo '<div class="all-33 small-100 tiny-100">';

			echo '<h6>' . __( 'Featured Image', 'wcvendors-pro' ) . '</h6>';
			$post_thumb = has_post_thumbnail( $post_id );

			echo '<div class="wcv-featuredimg" data-title="' . __( 'Select or Upload a Feature Image', 'wcvendors-pro' ) . '" data-button_text="' . __( 'Set Product Feature Image', 'wcvendors-pro' ) . '">';
			if ( $post_thumb ) {
				$image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), array( 150, 150 ) );
				echo '<img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '">';
			}
			echo '</div>';
			echo '<input type="hidden" id="_featured_image_id" name="_featured_image_id" value="' . ( $post_thumb ? get_post_thumbnail_id( $post_id ) : '' ) . '" />';

			echo '<a class="wcv-media-uploader-featured-add ' . ( $post_thumb ? 'hidden' : '' ) . '" href="#" >' . __( 'Set featured image', 'wcvendors-pro' ) . '</a><br />';
			echo '<a class="wcv-media-uploader-featured-delete ' . ( ! $post_thumb ? 'hidden' : '' ) . '" href="#" >' . __( 'Remove featured image', 'wcvendors-pro' ) . '</a><br />';

			echo '<span class="wcv_featured_image_msg"></span>';

			echo '</div>';

			if ( 'yes' !== get_option( 'wcvendors_hide_product_media_gallery', 'no' ) ) {

				if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
					$product_image_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
				} else {
					// Backwards compat
					if ( ! empty( $post_id ) ) {
						$attachment_ids = get_posts( 'post_parent=' . $post_id . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_woocommerce_exclude_image&meta_value=0' );
					} else {
						$attachment_ids = array();
					}

					$attachment_ids        = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
					$product_image_gallery = implode( ',', $attachment_ids );
				}

				// Output the image gallery if there are any images.
				$attachment_ids = array_filter( explode( ',', $product_image_gallery ) );

				$max_gallery_count = get_option( 'wcvendors_product_max_gallery_count', 4 );

				$max_gallery_count = $max_gallery_count ? $max_gallery_count : 4;

				$gallery_options = apply_filters(
					'wcv_product_gallery_options',
					array(
						'max_upload'          => $max_gallery_count,
						'notice'              => __( 'You have reached the maximum number of gallery images.', 'wcvendors-pro' ),
						'max_selected_notice' => sprintf( __( 'Maximum number of gallery images selected. Please select a maximum of %1$d images.', 'wcvendors-pro' ), $max_gallery_count ),
					)
				);

				echo '<div class="all-66 small-100 tiny-100" >';

				echo '<h6>' . __( 'Gallery', 'wcvendors-pro' ) . '</h6>';

				echo '<div id="product_images_container" data-gallery_max_upload="' . $gallery_options['max_upload'] . '" data-gallery_max_notice="' . $gallery_options['notice'] . '" data-gallery_max_selected_notice="' . $gallery_options['max_selected_notice'] . '">';
				echo '<ul class="product_images inline">';

				if ( sizeof( $attachment_ids ) > 0 ) {

					foreach ( $attachment_ids as $attachment_id ) {

						echo '<li class="wcv-gallery-image" data-attachment_id="' . $attachment_id . '">';
						echo wp_get_attachment_image( $attachment_id, array( 150, 150 ) );
						echo '<ul class="actions">';
						echo '<li><a href="#" class="delete" title="delete"><svg class="wcv-icon wcv-icon-md">
						<use xlink:href="' . WCV_PRO_PUBLIC_ASSETS_URL . 'svg/wcv-icons.svg#wcv-icon-times"></use>
				</svg></a></li>';
						echo '</ul>';
						echo '</li>';

					}
				}
				echo '</ul>';
				echo '<input type="hidden" id="product_image_gallery" name="product_image_gallery" value="' . ( ( sizeof( $attachment_ids ) > 0 ) ? $product_image_gallery : '' ) . '">';
				echo '</div>';
				echo '<p class="wcv-media-uploader-gallery"><a href="#" data-choose="' . __( 'Add Images to Product Gallery', 'wcvendors-pro' ) . '" data-update="' . __( 'Add to gallery', 'wcvendors-pro' ) . '" data-delete="Delete image" data-text="Delete">' . __( 'Add product gallery images', 'wcvendors-pro' ) . '</a></p>';

				echo '<span class="wcv_gallery_msg"></span>';

				echo '</div>';

			}

			echo '<div class="all-100"></div>';

			do_action( 'wcv_form_product_media_uploader_after_product_media_uploader', $post_id );

		}

	} // media_uploader ()

	/**
	 * Output a file upload link
	 *
	 * @since   1.0.0
	 * @version 1.5.9
	 *
	 * @param   array  $field file uploader arguments
	 * @param   string $type  Upload file type
	 *
	 * @todo    add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function file_uploader( $field, $type = 'image' ) {

		if ( 'image' === $type ) {
			self::image_uploader( $field );

			return;
		}

		$field['header_text']   = isset( $field['header_text'] ) ? $field['header_text'] : __( 'File', 'wcvendors-pro' );
		$field['add_text']      = isset( $field['add_text'] ) ? $field['add_text'] : __( 'Add file', 'wcvendors-pro' );
		$field['remove_text']   = isset( $field['remove_text'] ) ? $field['remove_text'] : __( 'Remove file', 'wcvendors-pro' );
		$field['file_meta_key'] = isset( $field['file_meta_key'] ) ? $field['file_meta_key'] : '_wcv_file_id';
		$field['save_button']   = isset( $field['save_button'] ) ? $field['save_button'] : __( 'Add File', 'wcvendors-pro' );
		$field['window_title']  = isset( $field['window_title'] ) ? $field['window_title'] : __( 'Select a File', 'wcvendors-pro' );
		$field['value']         = isset( $field['value'] ) ? $field['value'] : 0;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : '';
		$field['wrapper_start'] = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']   = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['required']      = isset( $field['required'] ) ? $field['required'] : '';
		$field['id']            = isset( $field['id'] ) ? $field['id'] : $field['file_meta_key'];

		$file_url = '';
		$has_file = false;
		$required = '' !== $field['required'] ? 'required' : '';

		if ( $field['value'] ) {
			$file_url = wp_get_attachment_url( $field['value'] );
		}

		if ( $file_url ) {
			$has_file = true;
		}

		do_action( 'wcv_form_file_uploader_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<div class="wcv-file-uploader' . $field['file_meta_key'] . ' ' . $field['class'] . '">';

		if ( $file_url ) {
			switch ( $type ) {
				case 'video':
					echo do_shortcode( '[video src="' . $file_url . '"]' );
					break;
				case 'audio':
					echo do_shortcode( '[audio src="' . $file_url . '"]' );
					break;
				case 'document':
					printf( '<a href="%1$s" target="_blank">%1$s</a>', $file_url );
					break;
				default:
					echo '<div class="control-group"><div class="control"><input value="' . $file_url . '" type="text" readonly /></div></div>';
			}
		}

		echo '</div>';

		echo '<a class="wcv-file-uploader-add' . $field['file_meta_key'] . ' ' . ( $has_file ? 'hidden' : '' ) . '" href="#">' . $field['add_text'] . '</a><br />';
		echo '<a class="wcv-file-uploader-delete' . $field['file_meta_key'] . ' ' . ( ! $has_file ? 'hidden' : '' ) . '" href="#" >' . $field['remove_text'] . '</a><br />';
		echo '<input class="wcv-img-id wcv-file-uploader" name="' . $field['file_meta_key'] . '" id="' . $field['file_meta_key'] . '" type="hidden" value="' . esc_attr( $field['value'] ) . '" data-file_meta_key="' . $field['file_meta_key'] . '" data-save_button="' . $field['save_button'] . '" data-window_title="' . $field['window_title'] . '" data-type="' . $type . '" data-msg-id="' . $field['file_meta_key'] . '_msg" ' . esc_attr( $required ) . ' />';
		echo '<span id="' . $field['file_meta_key'] . '_msg"></span>';

		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_file_uploader_after_' . $field['id'], $field );

	} // file_uploader()

	/**
	 * Output a file upload link
	 * This method is the original of file_uploader medthod
	 *
	 * @since      1.5.9
	 *
	 * @param      array $field file uploader arguments
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function image_uploader( $field ) {

		$field['header_text']    = isset( $field['header_text'] ) ? $field['header_text'] : __( 'Image', 'wcvendors-pro' );
		$field['add_text']       = isset( $field['add_text'] ) ? $field['add_text'] : __( 'Add Image', 'wcvendors-pro' );
		$field['remove_text']    = isset( $field['remove_text'] ) ? $field['remove_text'] : __( 'Remove Image', 'wcvendors-pro' );
		$field['image_meta_key'] = isset( $field['image_meta_key'] ) ? $field['image_meta_key'] : '_wcv_image_id';
		$field['save_button']    = isset( $field['save_button'] ) ? $field['save_button'] : __( 'Add Image', 'wcvendors-pro' );
		$field['window_title']   = isset( $field['window_title'] ) ? $field['window_title'] : __( 'Select an Image', 'wcvendors-pro' );
		$field['value']          = isset( $field['value'] ) ? $field['value'] : 0;
		$field['size']           = isset( $field['size'] ) ? $field['size'] : 'full';
		$field['class']          = isset( $field['class'] ) ? $field['class'] : '';
		$field['wrapper_start']  = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']    = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['required']       = isset( $field['required'] ) ? $field['required'] : '';
		$field['id']             = isset( $field['id'] ) ? $field['id'] : $field['image_meta_key'];

		// Get the image src
		$image_src = wp_get_attachment_image_src( $field['value'], $field['size'] );

		// see if the array is valid
		$has_image = is_array( $image_src );

		do_action( 'wcv_form_file_uploader_before_' . $field['id'], $field );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_start'];
		}

		echo '<div class="wcv-file-uploader' . $field['image_meta_key'] . ' ' . $field['class'] . '">';

		if ( $has_image ) {
			echo '<img src="' . $image_src[0] . '" alt="" style="max-width:100%;" />';
		}

		echo '</div>';

		$required = '' !== $field['required'] ? 'required' : '';

		echo '<a class="wcv-file-uploader-add' . $field['image_meta_key'] . ' ' . ( $has_image ? 'hidden' : '' ) . '" href="#">' . $field['add_text'] . '</a> ';
		echo '<a class="wcv-file-uploader-delete' . $field['image_meta_key'] . ' ' . ( ! $has_image ? 'hidden' : '' ) . '" href="#" >' . $field['remove_text'] . '</a><br />';
		echo '<input class="wcv-img-id wcv-file-uploader" name="' . $field['image_meta_key'] . '" id="' . $field['image_meta_key'] . '" type="hidden" value="' . esc_attr( $field['value'] ) . '" data-image_meta_key="' . $field['image_meta_key'] . '" data-save_button="' . $field['save_button'] . '" data-window_title="' . $field['window_title'] . '" data-type="image" data-msg-id="' . $field['image_meta_key'] . '_msg" ' . esc_attr( $required ) . ' />';
		echo '<span id="' . $field['image_meta_key'] . '_msg"></span>';
		// container wrapper end if defined
		if ( ! empty( $field['wrapper_start'] ) && ! empty( $field['wrapper_end'] ) ) {
			echo $field['wrapper_end'];
		}

		do_action( 'wcv_form_file_uploader_after_' . $field['id'], $field );

	} // image_uploader()

	/**
	 * Output a submit button
	 *
	 * @since      1.0.0
	 *
	 * @param      array $array the text for the submit button
	 */
	public static function submit( $args ) {

		$args['id']    = isset( $args['id'] ) ? $args['id'] : '';
		$args['value'] = isset( $args['value'] ) ? $args['value'] : 'Submit';
		$args['class'] = isset( $args['class'] ) ? $args['class'] : '';

		do_action( 'wcv_form_submit_before_' . $args['id'], $args );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			echo $args['wrapper_start'];
		}

		// echo '<div class="control-group">';
		// echo '<div class="control">';
		echo '<input type="submit" value="' . $args['value'] . '" class="wcv-button ' . $args['class'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '">';
		// echo '</div>';
		// echo '</div>';
		// container wrapper end if defined
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			echo $args['wrapper_end'];
		}

		do_action( 'wcv_form_submit_after_' . $args['id'], $args );

	}  // submit()

	/**
	 * Output a clear button
	 *
	 * @since      1.5.5
	 *
	 * @param      array $args the attributes for the clear button
	 */
	public static function clear( $args ) {

		$args['id']    = isset( $args['id'] ) ? $args['id'] : '';
		$args['value'] = isset( $args['value'] ) ? $args['value'] : 'Clear';
		$args['class'] = isset( $args['class'] ) ? $args['class'] : '';

		do_action( 'wcv_form_submit_before_' . $args['id'], $args );

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			echo $args['wrapper_start'];
		}

		// echo '<div class="control-group">';
		// echo '<div class="control">';
		echo '<input type="reset" value="' . $args['value'] . '" class="wcv-button ' . $args['class'] . '" name="' . $args['id'] . '" id="' . $args['id'] . '">';
		// echo '</div>';
		// echo '</div>';
		// container wrapper end if defined
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			echo $args['wrapper_end'];
		}

		do_action( 'wcv_form_submit_after_' . $args['id'], $args );

	}  // clear()

	/**
	 * Output an HTML element
	 *
	 * @since 1.5.5
	 *
	 * @param array $args the properties of the html element
	 */
	public static function html_element( $args ) {

		if ( isset( $args['attributes'] ) ) {
			$element_attributes = '';
			foreach ( $args['attributes'] as $key => $value ) {
				$element_attributes .= ' ' . $key . '="' . $value . '"';
			}
		}

		do_action( 'wcv_form_html_before_' . $args['id'], $args );

		$html = '';

		// Container wrapper start if defined start & end required otherwise no output is shown
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			$html .= $args['wrapper_start'];
		}

		$html .= '<' . $args['element'] . ' id="' . $args['id'] . '"' . $element_attributes . '>';
		$html .= ( isset( $args['content'] ) && '' != $args['content'] ) ? wp_kses( $args['content'], 'post' ) : '';
		$html .= '</' . $args['element'] . '>';

		// container wrapper end if defined
		if ( ! empty( $args['wrapper_start'] ) && ! empty( $args['wrapper_end'] ) ) {
			$html .= $args['wrapper_end'];
		}

		$html = apply_filters( 'wcv_form_html_' . $args['id'], $html );

		if ( isset( $args['echo'] ) && ! $args['echo'] ) {
			return $html;
		} else {
			echo $html;
		}

		do_action( 'wcv_form_html_after_' . $args['id'], $args );

	}

	/**
	 * Output a selec2 country selector
	 *
	 * @version    1.7.6
	 * @since      1.0.0
	 *
	 * @param      array $field country select arguments
	 */
	public static function country_select2( $field ) {

		$field['id']               = isset( $field['id'] ) ? $field['id'] : '';
		$field['label']            = isset( $field['label'] ) ? $field['label'] : '';
		$field['value']            = isset( $field['value'] ) ? $field['value'] : '';
		$field['class']            = isset( $field['class'] ) ? $field['class'] : '';
		$field['wrapper_start']    = isset( $field['wrapper_start'] ) ? $field['wrapper_start'] : '';
		$field['wrapper_end']      = isset( $field['wrapper_end'] ) ? $field['wrapper_end'] : '';
		$field['show_option_none'] = isset( $field['show_option_none'] ) ? $field['show_option_none'] : '';
		$field['options']          = isset( $field['options'] ) ? $field['options'] : ( ( WC()->countries->get_allowed_countries() ) ? WC()->countries->get_allowed_countries() : WC()->countries->get_shipping_countries() );

		if ( $field['value'] == '' ) {
			$field['value'] = WC()->countries->get_base_country();
		}

		do_action( 'wcv_form_country_select2_before_' . $field['id'], $field );

		self::select(
			apply_filters(
				'wcv_country_select2',
				array(
					'id'            => $field['id'],
					'label'         => $field['label'],
					'value'         => $field['value'],
					'class'         => 'select2 country_to_state country_select ' . $field['class'],
					'options'       => $field['options'],
					'wrapper_start' => $field['wrapper_start'],
					'wrapper_end'   => $field['wrapper_end'],
				)
			)
		);

		do_action( 'wcv_form_country_select2_after_' . $field['id'], $field );
	}

	/**
	 * Countries array
	 *
	 * @version 1.7.5
	 * @since   1.0.0
	 */
	public static function countries() {
		if ( ! function_exists( 'WC' ) ) {
			return array();
		}

		$countries = WC()->countries->get_allowed_countries();

		if ( ! array_key_exists( 'EWE', $countries ) ) {
			$countries['EWE'] = __( 'Everywhere else', 'wcvendors-pro' );
		}

		if ( ! is_array( $countries ) ) {
			return array();
		}

		return apply_filters( 'wcv_countries_list', $countries );
	}

	/**
	 * Generate a check list for categories on the front end
	 *
	 * @since 1.2.0
	 */
	public static function wcv_terms_checklist( $post_id = 0, $args = array(), $field = array() ) {

		$field['class'] = isset( $field['class'] ) ? $field['class'] : '';

		$defaults = array(
			'descendants_and_self' => 0,
			'selected_cats'        => false,
			'walker'               => null,
			'taxonomy'             => 'category',
			'checked_ontop'        => false,
			'echo'                 => true,
		);

		$params = apply_filters( 'wp_terms_checklist_args', $args, $post_id );

		$r = wp_parse_args( $params, $defaults );

		if ( empty( $r['walker'] ) || ! ( $r['walker'] instanceof Walker ) ) {
			$walker = new WCV_Walker_Category_Checklist();
		} else {
			$walker = $r['walker'];
		}

		$taxonomy = $r['taxonomy'];

		$descendants_and_self = (int) $r['descendants_and_self'];

		$args = array( 'taxonomy' => $taxonomy );

		$tax = get_taxonomy( $taxonomy );

		$args['list_only'] = ! empty( $r['list_only'] );

		if ( is_array( $r['selected_cats'] ) ) {
			$args['selected_cats'] = $r['selected_cats'];
		} elseif ( $post_id ) {
			$args['selected_cats'] = wp_get_object_terms( $post_id, $taxonomy, array_merge( $args, array( 'fields' => 'ids' ) ) );
		} else {
			$args['selected_cats'] = array();
		}

		if ( $descendants_and_self ) {
			$categories = (array) get_terms(
				$taxonomy,
				array(
					'child_of'     => $descendants_and_self,
					'hierarchical' => 0,
					'hide_empty'   => 0,
					'exclude'      => $r['exclude'],
				)
			);
			$self       = get_term( $descendants_and_self, $taxonomy );
			array_unshift( $categories, $self );
		} else {
			$categories = (array) get_terms(
				$taxonomy,
				array(
					'get'     => 'all',
					'exclude' => $r['exclude'],
				)
			);
		}

		$output = '';

		// Then the rest of them
		$output .= call_user_func_array( array( $walker, 'walk' ), array( $categories, 0, $args ) );

		if ( $r['echo'] ) {

			do_action( 'wcv_form_wcv_terms_checklist_before_' . $field['id'], $field );

			echo '<div class="control-group">';
			echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
			echo '<div class="wcv_terms_checklist_container">';

			echo '<ul class="control unstyled wcv_terms_checklist ' . $field['class'] . '">';
			echo $output;
			echo '</ul>';

			echo '</div>';
			echo '<span class="wcv_required_form_msg wcv_check_list_msg"></span>';
			echo '</div>';

			do_action( 'wcv_form_wcv_terms_checklist_after_' . $field['id'], $field );
		}

		return $output;

	} // wcv_terms_checklist()

	/**
	 * Output a custom form input based on an included file
	 */
	public static function custom_field( $field ) {

		extract( $field );

		do_action( 'wcv_form_custom_field_before_' . $id, $field );

		echo '<div class="control-group">';

		if ( isset( $wrapper_class ) ) {
			echo '<div class="' . $wrapper_class . '">';
		}

		include_once $path;

		if ( isset( $wrapper_class ) ) {
			echo '</div>';
		}

		echo '</div>';

		do_action( 'wcv_form_custom_field_after_' . $id, $field );

	} // custom_field()

	/**
	 * This function will output form fields based on the options parsed
	 *
	 * @since 1.4.0
	 *
	 * @param array $options field options
	 * @param int   $post_id the post id
	 */
	public static function generate_form_part( $options, $post_id = 0 ) {

		extract( $options );

		if ( isset( $wrapper_class ) ) {
			echo '<div class="' . $wrapper_class . '" id="' . $wrapper_id . '">';
		}

		foreach ( $fields as $field_id => $field ) {

			$field['id']      = $field_id;
			$field['post_id'] = $post_id;

			switch ( $field['field_type'] ) {
				case 'input':
				case 'hidden':
					self::input( $field );
					break;
				case 'textarea':
					self::textarea( $field );
					break;
				case 'select':
					self::select( $field );
					break;
				case 'select2':
					self::select2( $field );
					break;
				case 'custom_field':
					self::custom_field( $field );
					break;
				default:
					break;
			}
		}

		if ( isset( $wrapper_class ) ) {
			echo '</div>';
		}

	} // generate_form()

	/**
	 * Sanatize a value passed
	 *
	 * @since  1.4.4
	 * @access public
	 */
	public static function sanatize_value( $value, $sanitize = '' ) {

		switch ( $sanitize ) {
			case 'int':
				$value = $value ? absint( $value ) : '';
				break;
			case 'float':
				$value = $value ? floatval( $value ) : '';
				break;
			case 'yesno':
				$value = $value == 'yes' ? 'yes' : 'no';
				break;
			case 'issetyesno':
				$value = $value ? 'yes' : 'no';
				break;
			case 'max_date':
				$value = absint( $value );
				if ( $value == 0 ) {
					$value = 1;
				}
				break;
			case 'unixtime':
				$value = strtotime( $value );
				break;
			default:
				$value = sanitize_text_field( $value );
		}

		return $value;

	} // sanatize_value()

	/**
	 *    Check custom attributes to see if it matches the old validation system.
	 *
	 * @since 1.5.0
	 */
	public static function check_custom_attributes( $custom_attributes, $field_id ) {

		$new_validation_rules = array();

		foreach ( $custom_attributes as $attribute => $value ) {

			switch ( $attribute ) {
				case 'data-rules':
					error_log( 'Validation system has been updated please see. https://docs.wcvendors.com/knowledge-base/new-validation-system/' );
					$rules = explode( '|', $value );

					foreach ( $rules as $rule ) {
						switch ( $rule ) {
							case 'required':
								$new_validation_rules['required'] = '';
								break;
							case 'decimal':
								$new_validation_rules['data-parsley-decimal'] = wc_get_price_decimal_separator();
							default:
								if ( strpos( $rule, 'max_length' ) === 0 ) {
									preg_match( '/\[(.*)\]/', $rule, $matches );
									$new_validation_rules['data-parsley-maxlength'] = $matches[1];
								}
								$new_validation_rules = apply_filters( 'wcv_form_helper_custom_validation_rule', $new_validation_rules, $rule );
								break;
						}
					}
					unset( $custom_attributes['data-rules'] );
					break;
				case 'data-label':
					// Do nothing
					break;
				case 'data-error':
					$new_validation_rules['data-parsley-error-message'] = $value;
					unset( $custom_attributes['data-error'] );
					break;
				default:
					$new_validation_rules = apply_filters( 'wcv_form_helper_custom_validation', $new_validation_rules, $attribute, $value, $rule );
					// code...
					break;
			}
		}

		$new_custom_attributes = array_merge( $custom_attributes, $new_validation_rules );

		return $new_custom_attributes;
	}
}
