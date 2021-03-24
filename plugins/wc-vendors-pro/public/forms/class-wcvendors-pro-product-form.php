<?php

/**
 * The WCVendors Pro Product Form class
 *
 * This is the order form class
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/forms
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Product_Form {

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
	 *  Output required form data
	 *
	 * @since    1.0.0
	 * @version  1.4.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function form_data( $post_id, $post_status, $template = '' ) {

		$template = get_query_var( 'template' );

		if ( $post_id != null ) {

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_post_id',
					array(
						'post_id' => $post_id,
						'type'    => 'hidden',
						'id'      => 'post_id',
						'value'   => $post_id,
					)
				)
			);

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_post_status',
					array(
						'post_id' => $post_id,
						'type'    => 'hidden',
						'id'      => 'post_status',
						'value'   => $post_status,
					)
				)
			);

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_page_number',
					array(
						'post_id' => $post_id,
						'type'    => 'hidden',
						'id'      => 'page_number',
						'value'   => ( isset( $_GET['wcv_paged_id'] ) && ! empty( $_GET['wcv_paged_id'] ) ) ? $_GET['wcv_paged_id'] : '',
					)
				)
			);
		}

		// If the template variable has been defined then save this with the product
		if ( ! empty( $template ) ) {

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_form_template',
					array(
						'post_id' => $post_id,
						'type'    => 'hidden',
						'id'      => '_wcv_product_form_template',
						'value'   => $template,
					)
				)
			);

		}

		wp_nonce_field( 'wcv-save_product', '_wcv-save_product' );

	}

	/**
	 *  Output product title
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function title( $post_id, $product_title ) {

		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_product_title',
				array(
					'post_id'           => $post_id,
					'id'                => 'post_title',
					'label'             => __( 'Product name', 'wcvendors-pro' ),
					'value'             => $product_title,
					'custom_attributes' => array(
						'required'                   => '',
						'data-parsley-maxlength'     => '100',
						'data-parsley-error-message' => __( 'Product name is required or too long.', 'wcvendors-pro' ),
					),
				)
			)
		);

	} // title()

	/**
	 *  Output product description
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function description( $post_id, $product_description ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_description', 'no' ) ) {

			$required     = wc_string_to_bool( get_option( 'wcvendors_required_product_basic_description', 'no' ) );
			$enable_media = wc_string_to_bool( get_option( 'wcvendors_allow_product_description_media', 'no' ) );

			if ( wc_string_to_bool( get_option( 'wcvendors_allow_product_html', 'no' ) ) ) {

				if ( $required ) {
					add_filter( 'the_editor', 'WCVendors_Pro_Store_Form::wp_editor_required' );
					add_filter( 'tiny_mce_before_init', 'WCVendors_Pro_Store_Form::wp_tinymce_required' );
					add_filter( 'teeny_mce_before_init', 'WCVendors_Pro_Store_Form::wp_tinymce_required' );
				}

				$required_class = $required ? 'wcv-required' : '';

				$settings = apply_filters(
					'wcv_product_description_editor_settings',
					array(
						'editor_height' => 200,
						'media_buttons' => $enable_media,
						'teeny'         => true,
						'tinymce'       => true,
						'editor_class'  => $required_class,
						'tinymce'       => array(
							'setup' => 'function (editor) {
							editor.on("change", function () {
								var content = tinyMCE.activeEditor.getContent( {format : "raw"} )
									.replace( \'<p><br data-mce-bogus="1"></p>\', "" );

								if ( content != undefined && content != "" ) {
									jQuery( "#" + editor.id ).html( content );
								}
							});
						}',
						),
					)
				);

				echo '<div class="control-group">';
				echo '<label>' . __( 'Product description', 'wcvendors-pro' ) . '</label>';
				wp_editor( $product_description, 'post_content', $settings );
				echo '</div>';
			} else {
				$custom_attributes = $required ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Product description is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::textarea(
					apply_filters(
						'wcv_product_description',
						array(
							'post_id'           => $post_id,
							'id'                => 'post_content',
							'label'             => __( 'Product description', 'wcvendors-pro' ),
							'value'             => $product_description,
							'placeholder'       => __( 'Please add a full description of your product here', 'wcvendors-pro' ),
							'custom_attributes' => $custom_attributes,
						)
					)
				);
			}
		}

	} // description()

	/**
	 *  Output product short_description
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function short_description( $post_id, $product_short_description ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_short_description', 'no' ) ) {

			$required     = wc_string_to_bool( get_option( 'wcvendors_required_product_basic_short_description', 'no' ) );
			$enable_media = wc_string_to_bool( get_option( 'wcvendors_allow_product_description_media', 'no' ) );

			if ( wc_string_to_bool( get_option( 'wcvendors_allow_product_html', 'no' ) ) ) {

				if ( $required ) {
					add_filter( 'the_editor', 'WCVendors_Pro_Store_Form::wp_editor_required' );
					add_filter( 'tiny_mce_before_init', 'WCVendors_Pro_Store_Form::wp_tinymce_required' );
					add_filter( 'teeny_mce_before_init', 'WCVendors_Pro_Store_Form::wp_tinymce_required' );
				}

				$required_class = $required ? 'wcv-required' : '';

				$settings = apply_filters(
					'wcv_product_short_description_editor_settings',
					array(
						'editor_height' => 200,
						'media_buttons' => $enable_media,
						'teeny'         => true,
						'tinymce'       => true,
						'editor_class'  => $required_class,
						'tinymce'       => array(
							'setup' => 'function (editor) {
							editor.on("change", function () {
								var content = tinyMCE.activeEditor.getContent( {format : "raw"} )
									.replace( \'<p><br data-mce-bogus="1"></p>\', "" );

								if ( content != undefined && content != "" ) {
									jQuery( "#" + editor.id ).html( content );
								}
							});
						}',
						),
					)
				);

				echo '<div class="control-group">';
				echo '<label>' . __( 'Product short description', 'wcvendors-pro' ) . '</label>';
				wp_editor( $product_short_description, 'post_excerpt', $settings );
				echo '</div>';

			} else {
				$custom_attributes = $required ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Product short description is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::textarea(
					apply_filters(
						'wcv_product_short_description',
						array(
							'post_id'           => $post_id,
							'id'                => 'post_excerpt',
							'label'             => __( 'Product short description', 'wcvendors-pro' ),
							'placeholder'       => __( 'Please add a brief description of your product here', 'wcvendors-pro' ),
							'value'             => $product_short_description,
							'custom_attributes' => $custom_attributes,
						)
					)
				);
			}
}

	} // short_description()

	/**
	 *  Output save button
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function save_button( $button_text ) {

		$can_edit        = 'yes' === get_option( 'wcvendors_capability_products_edit', 'no' ) ? true : false;
		$can_submit_live = 'yes' === get_option( 'wcvendors_capability_products_live', 'no' ) ? true : false;

		if ( ! $can_submit_live && ! $can_edit ) {
			$button_text = __( 'Save Pending', 'wcvendors-pro' );
		}

		WCVendors_Pro_Form_helper::submit(
			apply_filters(
				'wcv_product_save_button',
				array(
					'id'    => 'product_save_button',
					'value' => $button_text,
					'class' => '',
				)
			)
		);

	} // save_button()

	/**
	 *  Output save button
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function draft_button( $button_text ) {

		WCVendors_Pro_Form_helper::submit(
			apply_filters(
				'wcv_product_draft_button',
				array(
					'id'    => 'draft_button',
					'value' => $button_text,
					'class' => '',
				)
			)
		);

	} // save_button()

	/**
	 *  Output product categories
	 *
	 * @since    1.0.0
	 *
	 * @param     int  $post_id  post_id for this meta if any
	 * @param     bool $multiple allow mupltiple selection
	 */
	public static function categories( $post_id, $multiple = false ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_categories', 'no' ) ) {

			if ( get_option( 'wcvendors_category_display', 'select' ) == 'select' ) {
				self::categories_dropdown( $post_id, true );
			} elseif ( get_option( 'wcvendors_category_display', 'select' ) == 'single_select' ) {
				self::categories_dropdown( $post_id, false );
			} else {
				self::categories_checklist( $post_id );
			}
		}

	} // categories()

	/**
	 *  Output product categories drop down
	 *
	 * @since    1.0.0
	 * @version  1.6.2
	 *
	 * @param     int  $post_id   post_id for this meta if any
	 * @param     bool $multiple allow mupltiple selection
	 */
	public static function categories_dropdown( $post_id, $multiple = false ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_categories', 'no' ) ) {

			$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_basic_categories', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Please select a category.', 'wcvendors-pro' ),
			) : array();

			if ( $multiple && $category_limit = get_option( 'wcvendors_category_limit', '' ) ) {
				$custom_attributes['data-parsley-maxcheck'] = $category_limit;
			}

			$hide_categories_list = get_option( 'wcvendors_hide_categories_list', '' );
			$show_option_none     = ( $multiple ) ? '' : __( 'Select a Category', 'wcvendors-pro' );
			$exclude              = array();

			if ( ! empty( $hide_categories_list ) ) {
				$exclude = explode( ',', str_replace( ' ', '', $hide_categories_list ) );
			}

			$categories     = wp_get_post_terms( $post_id, 'product_cat' );
			$categories_ids = array();

			foreach ( $categories as $category ) {
				$categories_ids[ $category->term_id ] = wp_kses_post( html_entity_decode( $category->name ) );
			}

			$field_value = array_keys( $categories_ids );

			if ( ! $multiple ) {
				$field_value = reset( $field_value );
			}

			// Product Category Drop down
			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_categories',
					array(
						'post_id'           => $post_id,
						'id'                => 'product_cat',
						'name'              => 'product_cat[]',
						'taxonomy'          => 'product_cat',
						'class'             => 'category-select2',
						'value'             => $field_value,
						'show_option_none'  => $show_option_none,
						'taxonomy_args'     => array(
							'hide_empty' => 0,
							'orderby'    => 'order',
							'exclude'    => $exclude,
						),
						'multiple'          => $multiple,
						'label'             => ( $multiple ) ? __( 'Categories', 'wcvendors-pro' ) : __( 'Category', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // categories()

	/**
	 *  Output product categories check list
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function categories_checklist( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_categories', 'no' ) ) {

			$exclude              = array();
			$hide_categories_list = get_option( 'wcvendors_hide_categories_list', '' );

			if ( ! empty( $hide_categories_list ) ) {
				$exclude = explode( ',', str_replace( ' ', '', $hide_categories_list ) );
			}

			$args = array(
				'taxonomy' => 'product_cat',
				'exclude'  => $exclude,
			);

			$field = array(
				'id'    => 'product_cat_list',
				'label' => __( 'Categories', 'wcvendors-pro' ),
				'class' => 'product_cat_checklist',
			);

			WCVendors_Pro_Form_Helper::wcv_terms_checklist( $post_id, $args, $field );
		}

	} // categories_checklist()

	/**
	 * DEPRECATED This function has been replaced - Output a woocommerce attribute selects
	 *
	 * @since      1.0.0
	 *
	 * @param      array $field Array defining all field attributes
	 *
	 * @todo       add filters to allow the field to be hooked into this should not echo html but return it.
	 */
	public static function attributes( $post_id, $multiple = false ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_attributes', 'no' ) ) {

			// Array of defined attribute taxonomies
			$attribute_taxonomies = wc_get_attribute_taxonomies();

			// If there are any defined attributes display them
			if ( ! empty( $attribute_taxonomies ) ) {

				$i = 0;
				// Get any set attributes for the product
				$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

				foreach ( $attribute_taxonomies as $product_attribute ) {

					if ( in_array( $product_attribute->attribute_id, explode( ',', get_option( 'wcvendors_hide_attributes_list', '' ) ) ) ) {
						continue;
					}

					$current_attribute = '';
					$is_variation      = 'no';
					// $custom_attributes 	= ( $multiple ) ? array( 'multiple' => 'multiple' ) : array();
					// If the attributes aren't empty, extract the attribute value for the current product
					if ( ! empty( $attributes ) && array_key_exists( wc_attribute_taxonomy_name( $product_attribute->attribute_name ), $attributes ) ) {
						// get all terms
						$current_attribute = wp_get_post_terms( $post_id, wc_attribute_taxonomy_name( $product_attribute->attribute_name ) );
						$is_variation      = $attributes[ wc_attribute_taxonomy_name( $product_attribute->attribute_name ) ]['is_variation'] ? 'yes' : 'no';
						$current_attribute = reset( $current_attribute );
						$current_attribute = $current_attribute->slug;
					}

					// Output attribute select
					WCVendors_Pro_Form_Helper::select(
						array(
							'id'               => 'attribute_values[' . $i . '][]',
							'post_id'          => $post_id,
							'label'            => ucfirst( $product_attribute->attribute_label ),
							'value'            => $current_attribute,
							'show_option_none' => __( 'Select a ', 'wcvendors-pro' ) . ucfirst( $product_attribute->attribute_label ),
							'taxonomy'         => wc_attribute_taxonomy_name( $product_attribute->attribute_name ),
							'is_attribute'     => true,
							'taxonomy_args'    => array(
								'hide_empty' => 0,
								'orderby'    => 'order',
							),
							// 'custom_attributes' => $custom_attributes,
						)
					);

					// Output attribute name hidden
					WCVendors_Pro_Form_Helper::input(
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
			do_action( 'wcv_product_options_attributes' );

		}

	} //attribute()

	/**
	 *  Output product tags multi select
	 *
	 * @since    1.3.0
	 *
	 * @param     int  $post_id  post_id for this meta if any
	 * @param     bool $multiple allow mupltiple selection
	 */
	public static function tags( $post_id, $multiple = false ) {

		if ( wc_string_to_bool( get_option( 'wcvendors_hide_product_basic_tags', 'no' ) ) ) {
			return;
		}

		$tags    = wp_get_post_terms( $post_id, 'product_tag' );
		$tag_ids = array();

		foreach ( $tags as $tag ) {
			$tag_ids[ $tag->term_id ] = wp_kses_post( html_entity_decode( $tag->name ) );
		}

		$required_field = 'yes' === get_option( 'wcvendors_required_product_basic_tags', 'no' ) ? array(
			'required'                   => '',
			'data-parsley-error-message' => __( 'Please select a tag.', 'wcvendors-pro' ),
		) : array();

		$custom_attributes = array(
			'data-placeholder' => __( 'Search or add a tag&hellip;', 'wcvendors-pro' ),
			'data-action'      => 'wcv_json_search_tags',
			'data-tags'        => 'true',
		);

		$tag_limit = get_option( 'wcvendors_tag_limit', '' );
		if ( '' !== $tag_limit ) {
			$custom_attributes['data-parsley-maxcheck'] = intval( $tag_limit );
		}

		if ( get_option( 'wcvendors_tag_display', '' ) === 'select_limited' ) {
			$custom_attributes['data-placeholder'] = __( 'Search for a tag&hellip;', 'wcvendors-pro' );
			$custom_attributes['data-tags']        = 'false';
		}

		$custom_attributes = array_merge( $custom_attributes, $required_field );

		foreach ( $tags as $tag ) {
			$tag_ids[ $tag->term_id ] = wp_kses_post( html_entity_decode( $tag->name ) );
		}

		WCVendors_Pro_Form_Helper::select(
			apply_filters(
				'wcv_product_tags',
				array(
					'id'                => 'product_tags',
					'label'             => __( 'Tags', 'wcvendors-pro' ),
					'value'             => implode( ',', array_keys( $tag_ids ) ),
					'style'             => 'width: 100%;',
					'class'             => 'wcv-tag-search tag-select2',
					'show_label'        => 'true',
					'custom_attributes' => $custom_attributes,
					'options'           => $tag_ids,
					'multiple'          => true,
				)
			)
		);

	} // tags

	/**
	 *  Output product type
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 *
	 * @todo     remove all echo statements and html
	 */
	public static function product_type( $post_id ) {

		if ( apply_filters( 'wcv_disable_product_type', false ) ) {
			return;
		}

		$product = ( is_numeric( $post_id ) ) ? wc_get_product( $post_id ) : null;

		if ( $product != null ) {
			if ( $terms = wp_get_object_terms( $post_id, 'product_type' ) ) {
				$product_type = sanitize_title( current( $terms )->name );
			} else {
				$product_type = apply_filters( 'wcv_default_product_type', 'simple' );
			}
		} else {
			$product_type = apply_filters( 'wcv_default_product_type', 'simple' );
		}

		$product_type_selector = wcv_get_product_types();

		// Disable capabitilies based on settings
		$product_type_settings = get_option( 'wcvendors_capability_product_types', array() );

		foreach ( $product_type_settings as $product_type_setting ) {

			if ( array_key_exists( $product_type_setting, $product_type_selector ) ) {
				unset( $product_type_selector[ $product_type_setting ] );
			}
		}

		$type_box  = '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-50 small-100">';
		$type_box .= '<div class="control-group">';
		$type_box .= '<label>' . __( 'Product type', 'wcvendors-pro' ) . '</label>';
		$type_box .= '<div class="control select">';
		$type_box .= '<select id="product-type" name="product-type" class="select2">';

		foreach ( $product_type_selector as $value => $label ) {
			$type_box .= '<option value="' . esc_attr( $value ) . '" ' . selected( $product_type, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$type_box .= '</select>';
		$type_box .= '</div>'; // control
		$type_box .= '</div>'; // control-group
		$type_box .= '</div>'; // grid

		$product_type_options = apply_filters(
			'product_type_options',
			array(
				'virtual'      => array(
					'id'            => '_virtual',
					'wrapper_class' => 'show_if_simple',
					'label'         => __( 'Virtual', 'wcvendors-pro' ),
					'description'   => __( 'Virtual products are intangible and aren\'t shipped.', 'wcvendors-pro' ),
					'default'       => 'no',
				),
				'downloadable' => array(
					'id'            => '_downloadable',
					'wrapper_class' => 'show_if_simple',
					'label'         => __( 'Downloadable', 'wcvendors-pro' ),
					'description'   => __( 'Downloadable products give access to a file upon purchase.', 'wcvendors-pro' ),
					'default'       => 'no',
				),
			)
		);

		// Disable capabitilies based on settings
		$product_type_options_settings = get_option( 'wcvendors_capability_product_type_options', array() );

		foreach ( $product_type_options as $key => $option ) {
			if ( in_array( $key, $product_type_options_settings ) ) {
				unset( $product_type_options[ $key ] );
			}
		}

		$type_box .= '<div class="all-50 small-100">';
		$type_box .= '<div class="control-group"> <br />';

		// Only output the list if there is options
		if ( ! empty( $product_type_options ) ) {
			$type_box .= '<ul class="control unstyled inline" style="padding: 0; margin:0;">';

			foreach ( $product_type_options as $key => $option ) {

				if ( metadata_exists( 'post', $post_id, '_' . $key ) ) {
					$selected_value = is_callable(
						array(
							$product,
							"is_$key",
						)
					) ? $product->{"is_$key"}() : 'yes' === get_post_meta( $post_id, '_' . $key, true );
				} else {
					$selected_value = 'yes' === ( isset( $option['default'] ) ? $option['default'] : 'no' );
				}

				$type_box .= '<li class="' . esc_attr( $option['wrapper_class'] ) . ' "><input type="checkbox" name="' . esc_attr( $option['id'] ) . '" id="' . esc_attr( $option['id'] ) . '" ' . checked( $selected_value, true, false ) . ' /><label for="' . esc_attr( $option['id'] ) . '" class="' . esc_attr( $option['wrapper_class'] ) . ' " data-tip="' . esc_attr( $option['description'] ) . '">' . esc_html( $option['label'] ) . '</label></li>';
			}

			$type_box .= '</ul>';
		}

		$type_box .= '</div>';  // control
		$type_box .= '</div>';  // control-group
		$type_box .= '</div>';  // grid

		echo $type_box;

	}

	/**
	 *  Output a hidden product type input for use with different templates
	 *
	 * @since    1.4.0
	 *
	 * @param     int    $post_id      post_id for this meta if any
	 * @param     string $product_type product_type to set
	 */
	public static function product_type_hidden( $post_id, $product_type = 'simple' ) {

		// Product Type
		WCVendors_Pro_Form_Helper::input(
			array(
				'post_id'    => $post_id,
				'type'       => 'hidden',
				'id'         => 'product-type',
				'value'      => $product_type,
				'show_label' => false,
			)
		);

	} // hidden_product_type()

	/**
	 *  Output a hidden virtual product input for use with different templates
	 *
	 * @since    1.4.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function virtual_product_hidden( $post_id ) {

		// Virtual Product
		WCVendors_Pro_Form_Helper::input(
			array(
				'post_id'    => $post_id,
				'type'       => 'hidden',
				'id'         => '_virtual',
				'value'      => 'yes',
				'show_label' => false,
			)
		);

	} // virtual_product()

	/**
	 *  Output a hidden downloadable product input for use with different templates
	 *
	 * @since    1.4.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function downloadable_product_hidden( $post_id ) {

		// Downloadable Product
		WCVendors_Pro_Form_Helper::input(
			array(
				'post_id'    => $post_id,
				'type'       => 'hidden',
				'id'         => '_downloadable',
				'value'      => 'yes',
				'show_label' => false,
			)
		);

	} // downloadable_product()

	/**
	 *  Output product price
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function price( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_price', 'no' ) ) {

			$required_field    = 'yes' === get_option( 'wcvendors_required_product_general_price', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Price is required', 'wcvendors-pro' ),
			) : array();
			$custom_attributes = array();
			$custom_attributes = array_merge( $custom_attributes, $required_field );

			$wrapper_start = 'yes' != get_option( 'wcvendors_hide_product_general_sale_price', 'no' ) ? '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-50 small-100">' : '<div class="all-100">';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_price',
					array(
						'post_id'           => $post_id,
						'id'                => '_regular_price',
						'label'             => __( 'Regular price', 'wcvendors-pro' ) . ' (' . get_woocommerce_currency_symbol() . ')',
						'data_type'         => 'price',
						'wrapper_start'     => $wrapper_start,
						'wrapper_end'       => '</div>',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}
	}

	/**
	 *  Output product sale price
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function sale_price( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_price', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_general_sale_price', 'no' ) ) {

			$required_field    = 'yes' === get_option( 'wcvendors_required_product_general_sale_price', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Sale price is required', 'wcvendors-pro' ),
			) : array();
			$custom_attributes = array();
			$custom_attributes = array_merge( $custom_attributes, $required_field );

			// Special Price - ends columns and row
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_sale_price',
					array(
						'post_id'           => $post_id,
						'id'                => '_sale_price',
						'data_type'         => 'price',
						'label'             => __( 'Sale Price', 'wcvendors-pro' ) . ' (' . get_woocommerce_currency_symbol() . ')',
						'desc_tip'          => 'true',
						'description'       => '<a href="#" class="sale_schedule right">' . __( 'Schedule', 'wcvendors-pro' ) . '</a>',
						'wrapper_start'     => '<div class="all-50 small-100">',
						'wrapper_end'       => '</div></div>',
						'custom_attributes' => $custom_attributes,
					)
				)
			);

			// Special Price date range
			$sale_price_dates_from = $post_id ? ( ( $date = get_post_meta( $post_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '' ) : '';
			$sale_price_dates_to   = $post_id ? ( ( $date = get_post_meta( $post_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '' ) : '';

			// From Sale Date
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_sale_price_date_from',
					array(
						'post_id'           => $post_id,
						'id'                => '_sale_price_dates_from',
						'label'             => __( 'From', 'wcvendors-pro' ),
						'class'             => 'wcv-datepicker',
						'value'             => esc_attr( $sale_price_dates_from ),
						'placeholder'       => ( '' == $sale_price_dates_from ) ? __( 'From&hellip; YYYY-MM-DD', 'placeholder', 'wcvendors-pro' ) : '',
						'wrapper_start'     => '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-50 small-100 sale_price_dates_fields">',
						'wrapper_end'       => '</div>',
						'custom_attributes' => array(
							'data-close-text' => __( 'Close', 'wcvendors-pro' ),
							'data-clean-text' => __( 'Clear', 'wcvendors-pro' ),
							'data-of-text'    => __( ' of ', 'wcvendors-pro' ),
						),
					)
				)
			);

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_sale_price_date_to',
					array(
						'post_id'           => $post_id,
						'id'                => '_sale_price_dates_to',
						'label'             => __( 'To', 'wcvendors-pro' ),
						'class'             => 'wcv-datepicker',
						'placeholder'       => ( '' == $sale_price_dates_to ) ? __( 'To&hellip; YYYY-MM-DD', 'placeholder', 'wcvendors-pro' ) : '',
						'wrapper_start'     => '<div class="all-50 small-100 sale_price_dates_fields">',
						'wrapper_end'       => '</div></div>',
						'value'             => esc_attr( $sale_price_dates_to ),
						'desc_tip'          => true,
						'description'       => __( 'The sale will end at the beginning of the set date.', 'wcvendors-pro' ) . '<a href="#" class="cancel_sale_schedule right">' . __( 'Cancel', 'wcvendors-pro' ) . '</a>',
						'custom_attributes' => array(
							'data-start-date' => '',
							'data-close-text' => __( 'Close', 'wcvendors-pro' ),
							'data-clean-text' => __( 'Clear', 'wcvendors-pro' ),
							'data-of-text'    => __( ' of ', 'wcvendors-pro' ),
						),
					)
				)
			);
		}

	} // sale_price()

	/**
	 *  Output product price and sale price
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function prices( $post_id ) {

		self::price( $post_id );
		self::sale_price( $post_id );

	}

	/**
	 *  Output downloadable files fields
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function download_files( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_download_files', 'no' ) ) {

			$readonly          = get_option( 'wcvendors_hide_product_general_download_file_url', 'no' ) ? 'readonly' : '';
			$file_display_type = get_option( 'wcvendors_file_display', '' );

			include_once apply_filters( 'wcvendors_pro_product_form_download_files_path', 'partials/wcvendors-pro-downloadable-files.php' );
		}

	} // download_files()

	/**
	 *  Output downloadable files fields
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function product_attributes( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_basic_attributes', 'no' ) ) {

			$attribute_terms_allowed = wc_string_to_bool( get_option( 'wcvendors_allow_vendor_attribute_terms', 'no' ) );

			include_once apply_filters( 'wcvendors_pro_product_form_product_attributes_path', 'partials/wcvendors-pro-attributes.php' );
		}

	} // download_files()

	/**
	 *  Output product download limit
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function download_limit( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_download_files', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_general_download_limit', 'no' ) ) {

			$product           = ( is_numeric( $post_id ) ) ? wc_get_product( $post_id ) : null;
			$required_field    = 'yes' === get_option( 'wcvendors_required_product_general_download_limit', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Download limit is required', 'wcvendors-pro' ),
			) : array();
			$custom_attributes = array( 'data-parsley-decimal' => wc_get_price_decimal_separator() );
			$custom_attributes = array_merge( $custom_attributes, $required_field );
			$value             = ( is_a( $product, 'WC_Product' ) ) ? - 1 === $product->get_download_limit( 'edit' ) ? '' : $product->get_download_limit( 'edit' ) : '';
			$wrapper_start     = 'yes' === get_option( 'wcvendors_hide_product_general_download_expiry', 'no' ) ? '<div class="all-100">' : '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-50 small-100">';

			// Download Limit
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_dowlnoad_limit',
					array(
						'post_id'           => $post_id,
						'id'                => '_download_limit',
						'label'             => __( 'Download limit', 'wcvendors-pro' ),
						'placeholder'       => __( 'Unlimited', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => __( 'Leave blank for unlimited re-downloads.', 'wcvendors-pro' ),
						'type'              => 'text',
						'wrapper_start'     => $wrapper_start,
						'wrapper_end'       => '</div>',
						'value'             => $value,
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // download_limit()

	/**
	 *  Output product download expiry
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function download_expiry( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_download_files', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_general_download_expiry', 'no' ) ) {

			$product           = ( is_numeric( $post_id ) ) ? wc_get_product( $post_id ) : null;
			$required_field    = 'yes' === get_option( 'wcvendors_required_product_general_download_expiry', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Download expiry is required.', 'wcvendors-pro' ),
			) : array();
			$custom_attributes = array( 'data-parsley-decimal' => wc_get_price_decimal_separator() );
			$custom_attributes = array_merge( $custom_attributes, $required_field );
			$value             = ( is_a( $product, 'WC_Product' ) ) ? - 1 === $product->get_download_expiry( 'edit' ) ? '' : $product->get_download_expiry( 'edit' ) : '';
			$wrapper_start     = ( 'yes' != get_option( 'wcvendors_hide_product_general_download_limit', 'no' ) ) ? '<div class="all-50 small-100">' : '<div class="all-100">';
			$wrapper_end       = ( 'yes' != get_option( 'wcvendors_hide_product_general_download_limit', 'no' ) ) ? '</div></div>' : '</div>';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_download_expiry',
					array(
						'post_id'           => $post_id,
						'id'                => '_download_expiry',
						'label'             => __( 'Download expiry', 'wcvendors-pro' ),
						'placeholder'       => __( 'Never', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => __( 'Enter the number of days before a download link expires, or leave blank.', 'wcvendors-pro' ),
						'type'              => 'text',
						'value'             => $value,
						'wrapper_start'     => $wrapper_start,
						'wrapper_end'       => $wrapper_end,
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // download_expiry()

	/**
	 *  Output product download type
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function download_type( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_download_files', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_general_download_type', 'no' ) ) {

			$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_general_download_type', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Download type is required.', 'wcvendors-pro' ),
			) : array();

			// Download Type
			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_download_type',
					array(
						'post_id'           => $post_id,
						'id'                => '_download_type',
						'class'             => 'select',
						'label'             => __( 'Download type', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => sprintf( __( 'Choose a download type - this controls the <a href="%s">http://schema.org</a>.', 'wcvendors-pro' ), 'http://schema.org/' ),
						'wrapper_start'     => '<div class="all-100">',
						'wrapper_end'       => '</div>',
						'options'           => array(
							'standard'    => __( 'Standard product', 'wcvendors-pro' ),
							'application' => __( 'Application/Software', 'wcvendors-pro' ),
							'music'       => __( 'Music', 'wcvendors-pro' ),
						),
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // download_type()

	/**
	 *  Output product sku
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function sku( $post_id ) {

		$hide_sku = wc_string_to_bool( get_option( 'wcvendors_capability_product_sku', 'no' ) );

		if ( ! $hide_sku && ! wc_string_to_bool( get_option( 'wcvendors_hide_product_general_sku', 'no' ) ) ) {

			if ( wc_product_sku_enabled() ) {

				$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_general_sku', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'SKU is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_sku',
						array(
							'post_id'           => $post_id,
							'id'                => '_sku',
							'label'             => '<abbr title="' . __( 'Stock keeping unit', 'wcvendors-pro' ) . '">' . __( 'SKU', 'wcvendors-pro' ) . '</abbr>',
							'desc_tip'          => 'true',
							'description'       => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wcvendors-pro' ),
							'custom_attributes' => $custom_attributes,
						)
					)
				);
			} else {

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_sku',
						array(
							'post_id' => $post_id,
							'type'    => 'hidden',
							'id'      => '_sku',
							'value'   => esc_attr( get_post_meta( $post_id, '_sku', true ) ),
						)
					)
				);
			}
		}

		do_action( 'wcv_product_options_sku' );

	} // sku()

	/**
	 *  Output private listing checkbox
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function private_listing( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_private_listing', 'no' ) ) {

			$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_general_private_listing', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Private listing is required.', 'wcvendors-pro' ),
			) : array();

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_private_listing',
					array(
						'post_id'           => $post_id,
						'id'                => '_private_listing',
						'wrapper_class'     => '',
						'label'             => __( 'Private listing, hide this product from the catalog.', 'wcvendors-pro' ),
						'type'              => 'checkbox',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // private_listing()

	/**
	 *  Output external url for external products
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function external_url( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_external_url', 'no' ) ) {

			$custom_attributes = get_option( 'wcvendors_required_product_general_external_url', '' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'External URL is required.', 'wcvendors-pro' ),
			) : array();

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_product_url',
					array(
						'post_id'           => $post_id,
						'id'                => '_product_url',
						'label'             => __( 'Product URL', 'wcvendors-pro' ),
						'type'              => 'url',
						'placeholder'       => 'http://',
						'desc_tip'          => 'true',
						'description'       => __( 'Enter the external URL to the product.', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // external_url()

	/**
	 *  Output button text for external products
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function button_text( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_button_text', 'no' ) ) {

			$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_general_button_text', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Button text is required.', 'wcvendors-pro' ),
			) : array();

			// Button text
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_button_text',
					array(
						'post_id'           => $post_id,
						'id'                => '_button_text',
						'label'             => __( 'Button text', 'wcvendors-pro' ),
						'placeholder'       => _x( '', 'placeholder', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => __( 'This text will be shown on the button linking to the external product.', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // button_text()

	/**
	 *  Output tax information
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function tax( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_general_tax', 'no' ) ) {

			if ( wc_tax_enabled() ) {

				$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_general_tax', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Tax is required.', 'wcvendors-pro' ),
				) : array();

				// Tax
				WCVendors_Pro_Form_Helper::select(
					apply_filters(
						'wcv_product_tax_status',
						array(
							'post_id'           => $post_id,
							'id'                => '_tax_status',
							'label'             => __( 'Tax status', 'wcvendors-pro' ),
							'wrapper_start'     => '<div class="all-100">',
							'wrapper_end'       => '</div>',
							'custom_attributes' => $custom_attributes,
							'options'           => array(
								'taxable'  => __( 'Taxable', 'wcvendors-pro' ),
								'shipping' => __( 'Shipping only', 'wcvendors-pro' ),
								'none'     => _x( 'None', 'Tax status', 'wcvendors-pro' ),
							),
						)
					)
				);

				$tax_classes                 = WC_Tax::get_tax_classes();
				$classes_options             = array();
				$classes_options['standard'] = __( 'Standard', 'wcvendors-pro' );

				if ( $tax_classes ) {

					foreach ( $tax_classes as $class ) {
						$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
					}
				}

				WCVendors_Pro_Form_Helper::select(
					apply_filters(
						'wcv_product_tax_class',
						array(
							'post_id'       => $post_id,
							'id'            => '_tax_class',
							'label'         => __( 'Tax class', 'wcvendors-pro' ),
							'options'       => $classes_options,
							'wrapper_start' => '<div class="all-100">',
							'wrapper_end'   => '</div>',
						)
					)
				);

				do_action( 'wcv_product_options_tax', $post_id );

			}
		}

	} // tax()

	/**
	 *  Output enable reviews
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function reviews( $post_id ) {

		$product        = ( is_numeric( $post_id ) ) ? wc_get_product( $post_id ) : null;
		$comment_status = ( $product != null ) ? esc_attr( $product->comment_status ) : 0;

		WCVendors_Pro_Form_Helper::input(
			apply_filters(
				'wcv_product_reviews',
				array(
					'post_id' => $post_id,
					'id'      => 'comment_status',
					'label'   => __( 'Enable reviews', 'wcvendors-pro' ),
					'type'    => 'checkbox',
				)
			)
		);

		do_action( 'wcv_product_options_reviews' );

	} // reviews()

	/**
	 *  Output manage stock
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function manage_stock( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) ) {

			$custom_attributes = wc_string_to_bool( get_option( 'wcvendors_required_product_inventory_manage_inventory', 'no' ) ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Manage stock is required.', 'wcvendors-pro' ),
			) : array();

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_manage_stock',
					array(
						'post_id'           => $post_id,
						'id'                => '_manage_stock',
						'wrapper_class'     => 'show_if_simple show_if_variable',
						'label'             => __( 'Manage stock?', 'wcvendors-pro' ),
						'description'       => __( 'Enable stock management at product level', 'wcvendors-pro' ),
						'type'              => 'checkbox',
						'custom_attributes' => $custom_attributes,
					)
				)
			);

		}

	} // manage_stock()

	/**
	 *  Output stock qty
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function stock_qty( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_inventory_stock_qty', 'no' ) ) {

			$custom_attributes = wc_string_to_bool( get_option( 'wcvendors_required_product_inventory_stock_qty', 'no' ) ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Stock QTY is required.', 'wcvendors-pro' ),
			) : array();

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_stock_qty',
					array(
						'post_id'           => $post_id,
						'id'                => '_stock',
						'label'             => __( 'Stock qty', 'wcvendors-pro' ),
						'wrapper_start'     => '<div class="all-100">',
						'wrapper_end'       => '</div>',
						'desc_tip'          => true,
						'description'       => __( 'Stock quantity.', 'wcvendors-pro' ),
						'type'              => 'number',
						'data_type'         => 'stock',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // stock_qty()

	/**
	 *  Output backorder select
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function backorders( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_inventory_backorders', 'no' ) ) {

			$custom_attributes = wc_string_to_bool( get_option( 'wcvendors_required_product_inventory_backorders', 'no' ) ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Allow backorders is required.', 'wcvendors-pro' ),
			) : array();

			// Backorders?
			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_backorders',
					array(
						'post_id'           => $post_id,
						'id'                => '_backorders',
						'label'             => __( 'Allow backorders?', 'wcvendors-pro' ),
						'wrapper_start'     => '<div class="all-100">',
						'wrapper_end'       => '</div>',
						'desc_tip'          => true,
						'description'       => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
						'options'           => array(
							'no'     => __( 'Do not allow', 'wcvendors-pro' ),
							'notify' => __( 'Allow, but notify customer', 'wcvendors-pro' ),
							'yes'    => __( 'Allow', 'wcvendors-pro' ),
						),
					)
				)
			);
		}
	}

	/**
	 *  Output stock status
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function stock_status( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_inventory_stock_status', 'no' ) ) {

			$custom_attributes = 'yes' != get_option( 'wcvendors_required_product_inventory_stock_status', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Stock status is required.', 'wcvendors-pro' ),
			) : array();

			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_stock_status',
					array(
						'post_id'           => $post_id,
						'id'                => '_stock_status',
						'wrapper_class'     => 'hide_if_variable',
						'label'             => __( 'Stock status', 'wcvendors-pro' ),
						'wrapper_start'     => '<div class="all-100 stock_status_field hide_if_variable hide_if_external hide_if_grouped">',
						'wrapper_end'       => '</div>',
						'desc_tip'          => true,
						'description'       => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
						'options'           => array(
							'instock'    => __( 'In stock', 'wcvendors-pro' ),
							'outofstock' => __( 'Out of stock', 'wcvendors-pro' ),
						),
					)
				)
			);
		}
	}

	/**
	 *  Output sold individually checkbox
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function sold_individually( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_inventory_sold_individually', 'no' ) ) {

			$require_sold_individually = wc_string_to_bool( get_option( 'wcvendors_required_product_inventory_sold_individually', 'no' ) );
			$custom_attributes         = $require_sold_individually ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Sold individually is required.', 'wcvendors-pro' ),
			) : array();

			if ( $require_sold_individually ) {
				$custom_attributes['checked'] = 'checked';
			}

			// sold individually
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_sold_individually',
					array(
						'post_id'           => $post_id,
						'id'                => '_sold_individually',
						'wrapper_class'     => 'show_if_simple show_if_variable',
						'label'             => __( 'Sold individually', 'wcvendors-pro' ),
						'desc_tip'          => true,
						'description'       => __( 'Enable this to only allow one of this item to be bought in a single order', 'wcvendors-pro' ),
						'type'              => 'checkbox',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}
	}

	/**
	 * Output the low stock threshold input
	 *
	 * @since 1.5.8
	 */
	public static function low_stock_threshold( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_inventory_manage_inventory', 'no' ) && 'yes' != get_option( 'wcvendors_hide_product_inventory_low_stock_threshold', 'no' ) ) {

			$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_inventory_low_stock_threshold', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Low stock threshold is required.', 'wcvendors-pro' ),
			) : array();

			// Low stock threshold
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_sold_individually',
					array(
						'post_id'           => $post_id,
						'id'                => '_low_stock_amount',
						'wrapper_start'     => '<div class="all-100">',
						'wrapper_end'       => '</div>',
						'label'             => __( 'Low stock threshold', 'wcvendors-pro' ),
						'desc_tip'          => true,
						'description'       => __( 'When product stock reaches this amount you will be notified by email', 'wcvendors-pro' ),
						'type'              => 'number',
						'data_type'         => 'stock',
						'custom_attributes' => $custom_attributes,
					)
				)
			);

		}
	}

	/**
	 *  Output weight input
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function weight( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_weight', 'no' ) ) {

			if ( wc_product_weight_enabled() ) {

				$custom_attributes = 'yes' === get_option( 'wcvendors_required_product_shipping_weight', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Weight is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_weight',
						array(
							'post_id'           => $post_id,
							'id'                => '_weight',
							'label'             => __( 'Weight', 'wcvendors-pro' ) . ' (' . get_option( 'woocommerce_weight_unit' ) . ')',
							'placeholder'       => wc_format_localized_decimal( 0 ),
							'desc_tip'          => 'true',
							'description'       => __( 'Weight in decimal form', 'wcvendors-pro' ),
							'type'              => 'text',
							'data_type'         => 'decimal',
							'custom_attributes' => $custom_attributes,
						)
					)
				);
			}
		}

	}

	/**
	 *  Output dimensions inputs
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function dimensions( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_dimensions' ) ) {

			if ( wc_product_dimensions_enabled() ) {

				$length_custom_attributes = 'yes' === get_option( 'wcvendors_required_product_shipping_dimensions', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Length is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_length',
						array(
							'post_id'           => $post_id,
							'id'                => '_length',
							'label'             => __( 'Dimensions', 'wcvendors-pro' ) . ' (' . get_option( 'woocommerce_dimension_unit' ) . ')',
							'placeholder'       => __( 'Length', 'wcvendors-pro' ),
							'type'              => 'text',
							'data_type'         => 'decimal',
							'wrapper_start'     => '<div class="wcv-cols-group wcv-horizontal-gutters"><div class="all-33">',
							'wrapper_end'       => '</div>',
							'custom_attributes' => $length_custom_attributes,
						)
					)
				);

				$width_custom_attributes = 'yes' === get_option( 'wcvendors_required_product_shipping_dimensions', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Width is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_width',
						array(
							'post_id'           => $post_id,
							'id'                => '_width',
							'placeholder'       => __( 'Width', 'wcvendors-pro' ),
							'type'              => 'text',
							'data_type'         => 'decimal',
							'wrapper_start'     => '<div class="all-33">',
							'wrapper_end'       => '</div>',
							'custom_attributes' => $width_custom_attributes,
						)
					)
				);

				$height_custom_attributes = 'yes' === get_option( 'wcvendors_required_product_shipping_dimensions', 'no' ) ? array(
					'required'                   => '',
					'data-parsley-error-message' => __( 'Height is required.', 'wcvendors-pro' ),
				) : array();

				WCVendors_Pro_Form_Helper::input(
					apply_filters(
						'wcv_product_height',
						array(
							'post_id'           => $post_id,
							'id'                => '_height',
							'placeholder'       => __( 'Height', 'wcvendors-pro' ),
							'type'              => 'text',
							'data_type'         => 'decimal',
							'wrapper_start'     => '<div class="all-33">',
							'wrapper_end'       => '</div></div>',
							'desc_tip'          => true,
							'description'       => __( 'Dimensions in decimal form.', 'wcvendors-pro' ),
							'custom_attributes' => $height_custom_attributes,
						)
					)
				);
			}
		}

	} //dimensions()

	/**
	 *  Output shipping class details
	 *
	 * @since    1.0.0
	 * @version  1.4.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function shipping_class( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_shipping_class', 'no' ) ) {

			// $custom_attributes 	= 'yes' != get_option( 'wcvendors_required_product_shipping_shipping_class' ) ? array( 'required' => '', 'data-parsley-error-message' => __( 'Shipping class is required.', 'wcvendors-pro' )  ) : array();
			$classes = ( $post_id ) ? get_the_terms( $post_id, 'product_shipping_class' ) : '';

			if ( $classes && ! is_wp_error( $classes ) ) {
				$current_shipping_class = current( $classes )->term_id;
			} else {
				$current_shipping_class = '';
			}

			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_shipping_classes',
					array(
						'post_id'          => $post_id,
						'class'            => 'select',
						'id'               => 'product_shipping_class',
						'label'            => __( 'Shipping class', 'wcvendors-pro' ),
						'show_option_none' => __( 'No shipping class', 'wcvendors-pro' ),
						'value'            => $current_shipping_class,
						'taxonomy'         => 'product_shipping_class',
						'taxonomy_field'   => 'term_id',
						'desc_tip'         => true,
						'description'      => __( 'Shipping classes are used by certain shipping methods to group similar products.', 'wcvendors-pro' ),
						// 'custom_attributes' 	=> $custom_attributes,
						'taxonomy_args'    => array(
							'hide_empty' => 0,
						),
					)
				)
			);

		}

	}  //shipping_class()

	/**
	 *  Output upsell select2
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function up_sells( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_upsells_up_sells', 'no' ) ) {

			$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post_id, '_upsell_ids', true ) ) );
			$upsell_ids  = array();
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( is_object( $product ) ) {
					$upsell_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
				}
			}

			$required_field = 'yes' === get_option( 'wcvendors_required_product_upsells_up_sells', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Please select a product.', 'wcvendors-pro' ),
			) : array();

			$custom_attributes = array(
				'data-placeholder' => __( 'Search for a product&hellip;', 'wcvendors-pro' ),
				'data-action'      => 'wcv_json_search_products',
			);

			$custom_attributes = array_merge( $custom_attributes, $required_field );

			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_upsells',
					array(
						'id'                => 'upsell_ids',
						'label'             => __( 'Up-Sells', 'wcvendors-pro' ),
						'value'             => implode( ',', array_keys( $upsell_ids ) ),
						'style'             => 'width: 100%;',
						'class'             => 'wc-product-search',
						'desc_tip'          => false, // tool tip messes with styling of drop down
						'description'       => __( 'Up-sells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'wcvendors-pro' ),
						'multiple'          => true,
						'options'           => $upsell_ids,
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} //up_sells()

	/**
	 *  Output crosssell select2
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function crosssells( $post_id ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_upsells_crosssells', 'no' ) ) {

			$product_ids   = array_filter( array_map( 'absint', (array) get_post_meta( $post_id, '_crosssell_ids', true ) ) );
			$crosssell_ids = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( is_object( $product ) ) {
					$crosssell_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
				}
			}

			$required_field = 'yes' === get_option( 'wcvendors_required_product_upsells_crosssells', 'no' ) ? array(
				'required'                   => '',
				'data-parsley-error-message' => __( 'Please select a product.', 'wcvendors-pro' ),
			) : array();

			$custom_attributes = array(
				'data-placeholder' => __( 'Search for a product&hellip;', 'wcvendors-pro' ),
				'data-action'      => 'wcv_json_search_products',
			);

			$custom_attributes = array_merge( $custom_attributes, $required_field );

			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_crosssells',
					array(
						'id'                => 'crosssell_ids',
						'label'             => __( 'Cross-Sells', 'wcvendors-pro' ),
						'value'             => implode( ',', array_keys( $crosssell_ids ) ),
						'style'             => 'width: 100%;',
						'class'             => 'wc-product-search',
						'desc_tip'          => false, // tool tip messes with styling of drop down
						'description'       => __( 'Cross-sells are products which you promote in the cart, based on the current product.', 'wcvendors-pro' ),
						'custom_attributes' => $custom_attributes,
						'multiple'          => true,
						'options'           => $crosssell_ids,
					)
				)
			);
		}

	} // crosssells()

	/**
	 *  Output grouped_products select2
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function grouped_products( $post_id, $product = false ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_upsells_grouped_products', 'no' ) ) {

			$product_object = $post_id ? wc_get_product( $post_id ) : new WC_Product();
			$product_ids    = $product_object->is_type( 'grouped' ) ? $product_object->get_children( 'edit' ) : array();

			$grouped_products = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( is_object( $product ) ) {
					$grouped_products[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
				}
			}

			$custom_attributes = array(
				'data-placeholder' => __( 'Search for a product&hellip;', 'wcvendors-pro' ),
				'data-action'      => 'wcv_json_search_products',
			);

			WCVendors_Pro_Form_Helper::select(
				apply_filters(
					'wcv_product_grouped_products',
					array(
						'id'                => 'grouped_products',
						'label'             => __( 'Grouped products', 'wcvendors-pro' ),
						'value'             => implode( ',', array_keys( $grouped_products ) ),
						'style'             => 'width: 100%;',
						'class'             => 'wc-product-search',
						'desc_tip'          => false, // tool tip messes with styling of drop down
						'description'       => __( 'This lets you choose which products are part of this group.', 'wcvendors-pro' ),
						'multiple'          => true,
						'options'           => $grouped_products,
						'custom_attributes' => $custom_attributes,
					)
				)
			);

		}

	} // grouped_product()

	/**
	 *  Output Product meta tab information
	 *
	 * @version 1.7.7
	 * @since   1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function product_meta_tabs() {

		$wcv_product_panel       = get_option( 'wcvendors_capability_product_data_tabs', array() );
		$css_classes             = apply_filters( 'wcv_product_meta_tabs_class', array( 'tabs-nav' ) );
		$shipping_methods        = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
		$shipping_method_enabled = ( array_key_exists( 'wcv_pro_vendor_shipping', $shipping_methods ) && $shipping_methods['wcv_pro_vendor_shipping']->enabled == 'yes' ) ? true : false;

		$product_meta_tabs = apply_filters(
			'wcv_product_meta_tabs',
			array(
				'general'        => array(
					'label'  => __( 'General', 'wcvendors-pro' ),
					'target' => 'general',
					'class'  => array( 'hide_if_grouped' ),
				),
				'inventory'      => array(
					'label'  => __( 'Inventory', 'wcvendors-pro' ),
					'target' => 'inventory',
					'class'  => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped' ),
				),
				'shipping'       => array(
					'label'  => __( 'Shipping', 'wcvendors-pro' ),
					'target' => 'shipping',
					'class'  => array( 'hide_if_virtual', 'hide_if_grouped', 'hide_if_external' ),
				),
				'linked_product' => array(
					'label'  => __( 'Linked Products', 'wcvendors-pro' ),
					'target' => 'linked_product',
					'class'  => array(),
				),
				'attribute'      => array(
					'label'  => __( 'Attributes', 'wcvendors-pro' ),
					'target' => 'attributes',
					'class'  => array(),
				),
				'seo'            => array(
					'label'  => __( 'SEO', 'wcvendors-pro' ),
					'target' => 'seo',
					'class'  => array(),
				),
				'variations'     => array(
					'label'  => __( 'Variations', 'wcvendors-pro' ),
					'target' => 'variations',
					'class'  => array( 'show_if_variable' ),
				),
			)
		);

		foreach ( $wcv_product_panel as $panel ) {

			if ( array_key_exists( $panel, $product_meta_tabs ) ) {
				unset( $product_meta_tabs[ $panel ] );
			}
		}

		// Disable inventory tab if stock management is disabeld at the WooCommerce level
		if ( 'no' === get_option( 'woocommerce_manage_stock' ) ) {
			unset( $product_meta_tabs['inventory'] );
		}

		// Hide Shipping tab if disable from woocommerce
		if ( ! $shipping_method_enabled ) {
			unset( $product_meta_tabs['shipping'] );
		}

		// Hide Linked Product tab if it's set to hidden.
		if ( wc_string_to_bool( get_option( 'wcvendors_hide_product_upsells_up_sells', 'no' ) ) && wc_string_to_bool( get_option( 'wcvendors_hide_product_upsells_crosssells', 'no' ) ) && wc_string_to_bool( get_option( 'wcvendors_hide_product_upsells_grouped_products', 'no' ) ) ) {
			unset( $product_meta_tabs['linked_product'] );
		}

		// Hide SEO tab if it's set to hidden.
		if ( wc_string_to_bool( get_option( 'wcvendors_hide_product_seo', 'no' ) ) ) {
			unset( $product_meta_tabs['seo'] );
		}

		// Hide Attributes tab if it's set to hidden.
		if ( wc_string_to_bool( get_option( 'wcvendors_hide_product_basic_attributes', 'no' ) ) ) {
			unset( $product_meta_tabs['attribute'] );
			unset( $product_meta_tabs['variations'] );
		}

		$css_class = implode( ' ', $css_classes );

		include apply_filters( 'wcvendors_pro_product_form_product_meta_tabs_path', 'partials/wcvendors-pro-product-meta-tabs.php' );

	} //product_meta_tabs

	/**
	 *  Output national shipping fee field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_rates( $post_id ) {

		global $wcvendors_pro;

		$shipping_settings       = get_option( 'woocommerce_wcv_pro_vendor_shipping_settings', wcv_get_default_vendor_shipping() );
		$store_shipping_type     = get_user_meta( get_current_user_id(), '_wcv_shipping_type', true );
		$shipping_type           = ( $store_shipping_type != '' ) ? $store_shipping_type : $shipping_settings['shipping_system'];
		$shipping_details        = (array) get_post_meta( $post_id, '_wcv_shipping_details', true );
		$shipping_methods        = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
		$shipping_method_enabled = ( array_key_exists( 'wcv_pro_vendor_shipping', $shipping_methods ) && $shipping_methods['wcv_pro_vendor_shipping']->enabled == 'yes' ) ? true : false;
		$national_disable        = wc_string_to_bool( $shipping_settings['national_disable'] );
		$international_disable   = wc_string_to_bool( $shipping_settings['international_disable'] );

		array_walk( $shipping_details, 'wcv_format_shipping_data' );

		if ( $shipping_method_enabled ) {

			do_action( 'wcv_before_shipping_rates', $post_id );

			if ( $shipping_type == 'flat' ) {

				echo '<div class="wcv-cols-group wcv-horizontal-gutters">';

				if ( ! $national_disable ) {
					if ( $international_disable ) {
						echo '<div class="all-100 small-100">';
					} else {
						echo '<div class="all-50 small-100">';
					}
					self::shipping_fee_national( $shipping_details );
					self::shipping_fee_national_free( $shipping_details );
					self::shipping_fee_national_qty( $shipping_details );
					self::shipping_fee_national_disable( $shipping_details );
					echo '</div>';

				}

				if ( ! $international_disable ) {
					if ( $national_disable ) {
						echo '<div class="all-100 small-100">';
					} else {
						echo '<div class="all-50 small-100">';
					}
					self::shipping_fee_international( $shipping_details );
					self::shipping_fee_international_free( $shipping_details );
					self::shipping_fee_international_qty( $shipping_details );
					self::shipping_fee_international_disable( $shipping_details );
					echo '</div>';

				}

				echo '</div>';

			} else {

				self::shipping_rate_table( $post_id );

			}

			do_action( 'wcv_after_shipping_rates', $post_id );

			self::handling_fee( $shipping_details );

			printf(
				'<hr><p><b>%s</b></p>',
				__( 'Notice: These settings will soon be removed and replaced with the new settings above in version 1.8.0', 'wcvendors-pro' )
			);

			self::max_charge( $shipping_details );
			self::free_shipping_product( $shipping_details );

		}

	} //shipping_rates()

	/**
	 *  Output national shipping fee field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_national( $shipping_details ) {

		// National shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_national_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'National shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'national', $shipping_details ) ) ? $shipping_details['national'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_national',
					array(
						'id'                => '_shipping_fee_national',
						'label'             => __( 'National shipping fee', 'wcvendors-pro' ),
						'placeholder'       => __( 'Change to override store defaults.', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => __( 'The cost to ship this product within your country.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// National minimum shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_minimum_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_national_minimum_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'National minimum shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'national_minimum_shipping_fee', $shipping_details ) ) ? $shipping_details['national_minimum_shipping_fee'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_national_minimum_shipping_fee',
					array(
						'id'                => '_national_minimum_shipping_fee',
						'label'             => __( 'National minimum shipping fee', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'The minimum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// National maximum shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_maximum_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_national_maximum_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'National maximum shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'national_maximum_shipping_fee', $shipping_details ) ) ? $shipping_details['national_maximum_shipping_fee'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_national_maximum_shipping_fee',
					array(
						'id'                => '_national_maximum_shipping_fee',
						'label'             => __( 'National maximum shipping fee', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'The maximum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// National free shipping product.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_free_shipping_product', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_national_free_shipping_product', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'National free shipping product is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'national_free_shipping_product', $shipping_details ) ) ? $shipping_details['national_free_shipping_product'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_national_free_shipping_product',
					array(
						'id'                => '_national_free_shipping_product',
						'label'             => __( 'National free shipping product', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'Free shipping if the spend per product is over this amount. This will override the max shipping charge above.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // shipping_fee_national()

	/**
	 *  Output national shipping fee qty override field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_national_qty( $shipping_details ) {

		// National charge once per product.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_charge_once_per_product', 'no' ) ) {

			$qty_value = ( is_array( $shipping_details ) && array_key_exists( 'national_qty_override', $shipping_details ) ) ? $shipping_details['national_qty_override'] : 0;

			// QTY Override
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_national_qty',
					array(
						'id'    => '_shipping_fee_national_qty',
						'label' => __( 'Charge once per product for national shipping, even if more than one is purchased.', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'value' => $qty_value,
						'class' => 'wcv-disable-national-input',
					)
				)
			);
		}

	} // shipping_fee_national_qty()

	/**
	 *  Output national shipping fee disable field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_national_disable( $shipping_details ) {

		// National free shipping.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_shipping_disable', 'no' ) ) {

			$disabled = ( is_array( $shipping_details ) && array_key_exists( 'national_disable', $shipping_details ) ) ? $shipping_details['national_disable'] : 0;

			// QTY Override
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_national_disable',
					array(
						'id'    => '_shipping_fee_national_disable',
						'label' => __( 'Disable national shipping for this product.', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'value' => $disabled,
					)
				)
			);
		}

	} // shipping_fee_national_disable()

	/**
	 *  Output national shipping fee free shipping field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_national_free( $shipping_details ) {

		// National free shipping.
		if ( 'yes' != get_option( 'wcvendors_hide_product_national_free_shipping', 'no' ) ) {

			$free = ( is_array( $shipping_details ) && array_key_exists( 'national_free', $shipping_details ) ) ? $shipping_details['national_free'] : 0;

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_national_free',
					array(
						'id'    => '_shipping_fee_national_free',
						'label' => __( 'Free national shipping', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'class' => 'wcv-disable-national-input',
						'value' => $free,
					)
				)
			);
		}

	} // shipping_fee_national_free()

	/**
	 *  Output international shipping fee field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_international( $shipping_details ) {

		// International shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_international_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_international_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'International shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'international', $shipping_details ) ) ? $shipping_details['international'] : '';

			// Shipping international Fee
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_international',
					array(
						'id'                => '_shipping_fee_international',
						'label'             => __( 'International shipping fee', 'wcvendors-pro' ),
						'placeholder'       => __( 'Change to override store defaults.', 'wcvendors-pro' ),
						'desc_tip'          => 'true',
						'description'       => __( 'The cost to ship this product outside your country.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-international-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// International minimum shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_international_minimum_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_international_minimum_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'International minimum shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'international_minimum_shipping_fee', $shipping_details ) ) ? $shipping_details['international_minimum_shipping_fee'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_international_minimum_shipping_fee',
					array(
						'id'                => '_international_minimum_shipping_fee',
						'label'             => __( 'International minimum shipping fee', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'The minimum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// International maximum shipping fee.
		if ( 'yes' != get_option( 'wcvendors_hide_product_international_maximum_shipping_fee', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_international_maximum_shipping_fee', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'International maximum shipping fee is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'international_maximum_shipping_fee', $shipping_details ) ) ? $shipping_details['international_maximum_shipping_fee'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_international_maximum_shipping_fee',
					array(
						'id'                => '_international_maximum_shipping_fee',
						'label'             => __( 'International maximum shipping fee', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'The maximum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

		// International free shipping product.
		if ( 'yes' != get_option( 'wcvendors_hide_product_international_free_shipping_product', 'no' ) ) {

			$custom_attributes = 'yes' == get_option( 'wcvendors_required_product_international_free_shipping_product', 'no' ) ? array(
				'data-rules' => 'required',
				'data-error' => __( 'International free shipping product is required.', 'wcvendors-pro' ),
			) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'international_free_shipping_product', $shipping_details ) ) ? $shipping_details['international_free_shipping_product'] : '';

			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_international_free_shipping_product',
					array(
						'id'                => '_international_free_shipping_product',
						'label'             => __( 'International free shipping product', 'wcvendors-pro' ),
						'placeholder'       => '0',
						'desc_tip'          => 'true',
						'description'       => __( 'Free shipping if the spend per product is over this amount. This will override the max shipping charge above.', 'wcvendors-pro' ),
						'data_type'         => 'price',
						'value'             => $value,
						'class'             => 'wcv-disable-national-input',
						'custom_attributes' => $custom_attributes,
					)
				)
			);
		}

	} // shipping_fee_international()

	/**
	 *  Output international shipping fee qty override field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_international_qty( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_international_charge_once_per_product', 'no' ) ) {
			$qty_value = ( is_array( $shipping_details ) && array_key_exists( 'international_qty_override', $shipping_details ) ) ? $shipping_details['international_qty_override'] : 0;

			// QTY Override
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_international_qty',
					array(
						'id'    => '_shipping_fee_international_qty',
						'label' => __( 'Charge once per product for international shipping, even if more than one is purchased.', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'value' => $qty_value,
						'class' => 'wcv-disable-international-input',
					)
				)
			);
		}

	} // shipping_fee_international_qty()

	/**
	 *  Output international shipping fee qty override field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_international_disable( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_international_shipping_disable', 'no' ) ) {

			$disabled = ( is_array( $shipping_details ) && array_key_exists( 'international_disable', $shipping_details ) ) ? $shipping_details['international_disable'] : 0;

			// QTY Override
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_international_disable',
					array(
						'id'    => '_shipping_fee_international_disable',
						'label' => __( 'Disable international shipping for this product.', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'value' => $disabled,
					)
				)
			);
		}

	} // shipping_fee_international_qty()

	/**
	 *  Output international shipping fee free shipping field
	 *
	 * @version 1.7.6
	 * @since   1.0.0
	 */
	public static function shipping_fee_international_free( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_international_free_shipping', 'no' ) ) {

			$free = ( is_array( $shipping_details ) && array_key_exists( 'international_free', $shipping_details ) ) ? $shipping_details['international_free'] : 0;

			// QTY Override
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_shipping_fee_international_free',
					array(
						'id'    => '_shipping_fee_international_free',
						'label' => __( 'Free international shipping', 'wcvendors-pro' ),
						'type'  => 'checkbox',
						'value' => $free,
						'class' => 'wcv-disable-international-input',
					)
				)
			);
		}

	} // shipping_fee_international_qty()

	/**
	 *  Output product handling fee field
	 *
	 * @since    1.0.0
	 */
	public static function handling_fee( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_handling_fee', 'no' ) ) {

			$custom_attributes = '';// 'yes' != get_option( 'wcvendors_required_product_shipping_handling_fee' ) ? array(  'data-rules' => 'required', 'data-error' => __( 'Product handling fee is required.', 'wcvendors-pro' ) ) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'handling_fee', $shipping_details ) ) ? $shipping_details['handling_fee'] : '';

			// Product handling Fee
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_handling_fee',
					array(
						'id'          => '_handling_fee',
						'label'       => __( 'Product handling fee', 'wcvendors-pro' ),
						'placeholder' => __( '0', 'wcvendors-pro' ),
						'desc_tip'    => 'true',
						'description' => __( 'The product handling fee. Amount (5.00) or Percentage (5%).', 'wcvendors-pro' ),
						'type'        => 'text',
						'value'       => $value,
					)
				)
			);

		}

	} // product_handling_fee()

	/**
	 *  Output product max shipping charge field
	 *
	 * @since    1.0.0
	 */
	public static function max_charge( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_max_charge', 'no' ) ) {

			$custom_attributes = ''; // 'yes' != get_option( 'wcvendors_required_product_shipping_max_charge' ) ? array(  'data-rules' => 'required', 'data-error' => __( 'Maximum shipping charge is required.', 'wcvendors-pro' ) ) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'max_charge_product', $shipping_details ) ) ? $shipping_details['max_charge_product'] : '';

			// Product handling Fee
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_max_charge',
					array(
						'id'          => '_max_charge_product',
						'label'       => __( 'Maximum shipping charge', 'wcvendors-pro' ),
						'placeholder' => __( '0', 'wcvendors-pro' ),
						'desc_tip'    => 'true',
						'description' => __( 'The maximum shipping charged per product no matter the quantity.', 'wcvendors-pro' ),
						'data_type'   => 'price',
						'value'       => $value,
					)
				)
			);

		}

	} // max_charge()

	/**
	 *  Output product max shipping charge field
	 *
	 * @since    1.0.0
	 */
	public static function free_shipping_product( $shipping_details ) {

		if ( 'yes' != get_option( 'wcvendors_hide_product_shipping_free_shipping_product', 'no' ) ) {

			$custom_attributes = ''; // 'yes' != get_option( 'wcvendors_required_product_shipping_free_shipping_product' ) ? array(  'data-rules' => 'required', 'data-error' => __( 'Free shipping product is required.', 'wcvendors-pro' ) ) : array();

			$value = ( is_array( $shipping_details ) && array_key_exists( 'free_shipping_product', $shipping_details ) ) ? $shipping_details['free_shipping_product'] : '';

			// Product handling Fee
			WCVendors_Pro_Form_Helper::input(
				apply_filters(
					'wcv_product_free_shipping_product',
					array(
						'id'          => '_free_shipping_product',
						'label'       => __( 'Free Shipping Product', 'wcvendors-pro' ),
						'placeholder' => __( '0', 'wcvendors-pro' ),
						'desc_tip'    => 'true',
						'description' => __( 'Free shipping if the spend per product is over this amount. This will override the max shipping charge above.', 'wcvendors-pro' ),
						'data_type'   => 'price',
						'value'       => $value,
					)
				)
			);
		}

	} // max_charge()

	/**
	 *  Output shipping rate table
	 *
	 * @since    1.0.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function shipping_rate_table( $post_id ) {

		$helper_text = apply_filters( 'wcv_store_shipping_rate_table_msg', __( 'Countries must use the international standard for two letter country codes. eg. AU for Australia.', 'wcvendors-pro' ) );

		$shipping_rates = get_post_meta( $post_id, '_wcv_shipping_rates', true );

		if ( empty( $shipping_rates ) ) {
			$shipping_rates = get_user_meta( get_current_user_id(), '_wcv_shipping_rates', true );
		}

		include_once apply_filters( 'wcvendors_pro_product_form_shipping_rate_table_path', 'partials/wcvendors-pro-shipping-table.php' );

	} // download_files()

	/**
	 *  Output product variations
	 *
	 * @since    1.2.0
	 *
	 * @param     int $post_id post_id for this meta if any
	 */
	public static function product_variations( $post_id ) {

		global $wpdb;

		// Get attributes
		$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

		$basic_options     = (array) get_option( 'wcvendors_hide_product_basic', array() );
		$media_options     = (array) get_option( 'wcvendors_hide_product_media', array() );
		$general_options   = (array) get_option( 'wcvendors_hide_product_general', array() );
		$inventory_options = (array) get_option( 'wcvendors_hide_product_inventory', array() );
		$shipping_options  = (array) get_option( 'wcvendors_hide_product_shipping', array() );
		$upsell_options    = (array) get_option( 'wcvendors_hide_product_upsells', array() );

		// See if any are set
		$variation_attribute_found = false;

		if ( $attributes ) {
			foreach ( $attributes as $attribute ) {
				if ( ! empty( $attribute['is_variation'] ) ) {
					$variation_attribute_found = true;
					break;
				}
			}
		}

		$variations_count = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'product_variation' AND post_status IN ('publish', 'private')", $post_id ) ) );

		include_once apply_filters( 'wcvendors_pro_product_form_product_variations_path', 'partials/wcvendors-pro-product-variations.php' );

	} // product_variations()

	/**
	 * Product SEO
	 *
	 * @param int $product_id
	 *
	 * @return void
	 * @since 1.5.8
	 */
	public static function product_seo( $product_id ) {

		$hide_seo = wc_string_to_bool( get_option( 'wcvendors_hide_product_seo', 'no' ) );

		if ( ! $hide_seo ) {

			if ( $product_id ) {
				$seo_title       = get_post_meta( $product_id, 'wcv_product_seo_title', true );
				$seo_description = get_post_meta( $product_id, 'wcv_product_seo_description', true );
				$seo_keywords    = get_post_meta( $product_id, 'wcv_product_seo_keywords', true );

				$seo_opengraph    = get_post_meta( $product_id, 'wcv_product_seo_opengraph', true );
				$seo_twitter_card = get_post_meta( $product_id, 'wcv_product_seo_twitter_card', true );

				$product = wc_get_product( $product_id );

				$seo_title       = empty( $seo_title ) ? wcv_strip_html( $product->get_title() ) : wcv_strip_html( $seo_title );
				$seo_description = empty( $seo_description ) ? wcv_strip_html( substr( $product->get_description(), 0, apply_filters( 'wcv_seo_description_length', 155 ) ) ) : wcv_strip_html( $seo_description );

				if ( ! $seo_keywords && is_a( $product, 'WC_Product' ) ) {
					$categories   = wcv_strip_html( wc_get_product_category_list( $product->get_id() ) );
					$seo_keywords = ! empty( $categories ) ? $categories : '';
				}
			} else {
				$seo_title        = '';
				$seo_description  = '';
				$seo_keywords     = '';
				$seo_opengraph    = false;
				$seo_twitter_card = false;
			}

			include_once apply_filters( 'wcvendors_pro_product_seo_form', 'partials/wcvendors-pro-product-seo.php' );
		}

	} // product_seo()

}
