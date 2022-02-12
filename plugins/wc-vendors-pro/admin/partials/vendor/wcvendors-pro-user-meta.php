<?php

/**
 * The extended user meta fields
 *
 * This file is used to display the pro user meta fields on the edit screen.
 *
 * @link       http://www.wcvendors.com
 * @since      1.2.3
 * @version    1.5.0
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials/vendors
 */


// Store Meta fields
foreach ( $fields as $fieldkey => $fieldset ) : ?>

	<?php $class = isset( $fieldset['field_class'] ) ? 'wcv-' . $fieldkey . ' ' . $fieldset['field_class'] : 'wcv-' . $fieldkey; ?>

	<?php do_action( 'wcv_admin_before_' . $fieldkey, $user ); ?>

	<div class="<?php echo $class; ?>">
		<h3><?php echo $fieldset['title']; ?></h3>
		<table class="form-table">
			<?php foreach ( $fieldset['fields'] as $key => $field ) : ?>

				<?php
				$default_value = isset( $field['value'] ) ? $field['value'] : '';
				$value         = esc_attr( get_user_meta( $user->ID, $key, true ) ) ? esc_attr( get_user_meta( $user->ID, $key, true ) ) : $default_value;

				if ( ! empty( $field['type'] ) && 'image' == $field['type'] ) {
					$image_id  = $value;
					$image_src = wp_get_attachment_image_src( $image_id, 'medium' );
					$has_image = is_array( $image_src );
				}

				?>

				<tr>
					<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					</th>
					<td>
						<?php if ( ! empty( $field['type'] ) && 'select' == $field['type'] ) : ?>
							<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
									class="<?php echo( ! empty( $field['class'] ) ? $field['class'] : '' ); ?>"
									style="width: 25em;">
								<?php
								foreach ( $field['options'] as $option_key => $option_value ) :
									?>
									<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $value, $option_key, true ); ?>><?php echo esc_attr( $option_value ); ?></option>
								<?php endforeach; ?>
							</select>
							<br/>
							<span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span>
						<?php elseif ( ! empty( $field['type'] ) && 'checkbox' == $field['type'] ) : ?>
							<label for="<?php echo esc_attr( $key ); ?>">
								<input type="checkbox" name="<?php echo esc_attr( $key ); ?>"
									   id="<?php echo esc_attr( $key ); ?>" <?php checked( 'yes', $value, true ); ?> />
								<?php echo esc_html( $field['description'] ); ?>
							</label>
						<?php elseif ( ! empty( $field['type'] ) && 'textarea' == $field['type'] ) : ?>
							<textarea name="<?php echo esc_attr( $key ); ?>"
									  id="<?php echo esc_attr( $key ); ?>"><?php echo wp_strip_all_tags( $value ); ?></textarea>
						<?php elseif ( ! empty( $field['type'] ) && 'image' == $field['type'] ) : ?>
							<div class="wcv-file-uploader<?php echo $key; ?> wcv-file-uploader-img">
								<?php if ( $has_image ) : ?>
									<img src="<?php echo $image_src[0]; ?>" alt="" style="max-width:100%;"/>
								<?php else : ?>
									<?php printf( __( 'Upload an image for the %s', 'wcvendors-pro' ), esc_html( $field['label'] ) ); ?>
								<?php endif; ?>
							</div>
							<br/>
							<input id="_wcv_add<?php echo $key; ?>" type="button" class="button wcv_add_image_id"
								   value="<?php _e( 'Add image', 'wcvendors-pro' ); ?>" data-key="<?php echo $key; ?>"/>
							<input id="_wcv_remove<?php echo $key; ?>" type="button" class="button wcv_remove_image_id"
								   value="<?php _e( 'Remove image', 'wcvendors-pro' ); ?>"
								   data-key="<?php echo $key; ?>"/>
							<input type="hidden" name="<?php echo $key; ?>" id="<?php echo $key; ?>"
								   data-save_button="<?php _e( 'Add image', 'wcvendors-pro' ); ?>"
								   data-window_title="<?php _e( 'Add image', 'wcvendors-pro' ); ?>"
								   data-upload_notice="<?php printf( __( 'Upload an image for the %s', 'wcvendors-pro' ), esc_html( $field['label'] ) ); ?>"
								   value="<?php echo $image_id; ?>">
							<?php
						elseif ( ! empty( $field['type'] ) && 'wp_editor' == $field['type'] ) :
							$vendor_store_html = get_user_meta( $user->ID, 'pv_shop_html_enabled', true );
							$store_wide_html   = wc_string_to_bool( get_option( 'wcvendors_display_shop_description_html', 'no' ) );
							$enable_media      = wc_string_to_bool( get_option( 'wcvendors_allow_editor_media', 'no' ) );

							wp_editor(
								wp_kses_post( html_entity_decode( $value ) ),
								$key,
								array(
									'wpautop'       => true,
									'media_buttons' => $enable_media,
									'textarea_name' => $key,
									'textarea_rows' => apply_filters( $key, 4 ),
									'tinymce'       => array(
										'setup' => 'function (editor) {
									jQuery( "#" + editor.id ).attr( "name", editor.id );

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
							?>
						<?php else : ?>
							<input type="text" name="<?php echo esc_attr( $key ); ?>"
								   id="<?php echo esc_attr( $key ); ?>" value="<?php echo $value; ?>"
								   class="<?php echo( ! empty( $field['class'] ) ? $field['class'] : 'regular-text' ); ?>"
													  <?php
														if ( isset( $field['placeholder'] ) ) {
															?>
										 placeholder="<?php echo $field['placeholder']; ?>" <?php } ?> />
							<br/>
							<span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span>
						<?php endif; ?>
						<br/>

					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>

	<?php do_action( 'wcv_admin_after_' . $fieldkey, $user ); ?>

<?php endforeach; ?>
