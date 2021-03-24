<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store Address and Map Widget.
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/widgets
 * @author     Lindeni Mahlalela
 * @version    1.5.4
 * @extends    WC_Widget
 */
class WCV_Widget_Store_Address_And_Map extends WC_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'wcv widget_store_map_and_address';
		$this->widget_description = __( 'Shows the address and map of the store.', 'wcvendors-pro' );
		$this->widget_id          = 'wcv_store_address_and_map';
		$this->widget_name        = __( 'WC Vendors Pro Store Address and Map', 'wcvendors-pro' );
		$this->settings           = array(
			'title'                => array(
				'type'  => 'text',
				'std'   => __( 'Store address and map', 'wcvendors-pro' ),
				'label' => __( 'Title', 'wcvendors-pro' ),
			),
			'address_heading'      => array(
				'type'  => 'text',
				'std'   => __( 'Our office address', 'wcvendors-pro' ),
				'label' => __( 'Address heading', 'wcvendors-pro' ),
			),
			'show_address_heading' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show address Hheading', 'wcvendors-pro' ),
			),
			'map_heading'          => array(
				'type'  => 'text',
				'std'   => __( 'See maps for directions', 'wcvendors-pro' ),
				'label' => __( 'Map heading', 'wcvendors-pro' ),
			),
			'show_map_heading'     => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show map heading', 'wcvendors-pro' ),
			),
			'maps_api_key'         => array(
				'type'  => 'google_maps_api',
				'std'   => apply_filters( 'wcvendors_pro_google_maps_api_key', get_option( 'wcvendors_pro_google_maps_api_key', '' ) ),
				'label' => __( 'Google maps API key', 'wcvendors-pro' ),
			),
			'map_height'           => array(
				'type'  => 'text',
				'std'   => __( '300px', 'wcvendors-pro' ),
				'label' => __( 'Map height', 'wcvendors-pro' ),
			),
			'map_width'            => array(
				'type'  => 'text',
				'std'   => __( '100%', 'wcvendors-pro' ),
				'label' => __( 'Map width', 'wcvendors-pro' ),
			),
			'zoom_level'           => array(
				'type'  => 'number',
				'std'   => apply_filters( 'wcv_google_maps_zoom_level', get_option( 'wcvendors_pro_google_maps_zoom_level', '' ) ),
				'min'   => 0,
				'max'   => 25,
				'step'  => 1,
				'label' => __( 'Default zoom level', 'wcvendors-pro' ),
			),
			'hide_address'         => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide address', 'wcvendors-pro' ),
			),
			'hide_map'             => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide map', 'wcvendors-pro' ),
			),
		);

		add_action( 'woocommerce_widget_field_google_maps_api', array( $this, 'google_maps_field' ), 10, 4 );

		parent::__construct();
	}

	/**
	 * Output the address and map widget.
	 *
	 * @see   WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.5.4
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! is_woocommerce() ) {
			return;
		}

		if ( ! $post ) {
return;
		}

		if ( ! WCV_Vendors::is_vendor_page() && ! WCV_Vendors::is_vendor_product_page( $post->post_author ) ) {
			return;
		}

		$hide_address = isset( $instance['hide_address'] ) ? $instance['hide_address'] : $this->settings['hide_address']['std'];
		$hide_map     = isset( $instance['hide_map'] ) ? $instance['hide_map'] : $this->settings['hide_map']['std'];

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

		if ( ! isset( $vendor_id ) ) {
			return;
		}

		$vendor_settings = array_map(
			function ( $a ) {
					return $a[0];
			},
			get_user_meta( $vendor_id )
		);

		$address_line1 = isset( $vendor_settings['_wcv_store_address1'] ) ? $vendor_settings['_wcv_store_address1'] : '';
		$city          = isset( $vendor_settings['_wcv_store_city'] ) ? $vendor_settings['_wcv_store_city'] : '';
		$state         = isset( $vendor_settings['_wcv_store_state'] ) ? $vendor_settings['_wcv_store_state'] : '';
		$post_code     = isset( $vendor_settings['_wcv_store_postcode'] ) ? $vendor_settings['_wcv_store_postcode'] : '';

		$this->widget_start( $args, $instance );

		if ( ! $hide_address ) {
			$show_address_heading = isset( $instance['show_address_heading'] ) ? $instance['show_address_heading'] : $instance['show_address_heading']['std'];

			$address_heading = isset( $instance['address_heading'] ) ? $instance['address_heading'] : $instance['address_heading']['std'];

			echo $show_address_heading && isset( $address_heading ) ? '<p class="wcv-widget-map-heading">' . esc_attr( $address_heading ) . '</p>' : '';

			echo '<ul class="contact-card">';
			echo isset( $vendor_settings['pv_shop_name'] ) ? '<li class="wcv-widget-shop-name">' . $vendor_settings['pv_shop_name'] . '</li>' : '';
			echo ! empty( $address_line1 ) ? '<li class="wcv-widget-store-address1">' . $address_line1 . '</li>' : '';
			echo ! empty( $city ) ? '<li class="wcv-widget-store-city">' . esc_attr( $city ) . '</li>' : '';
			echo ! empty( $state ) ? '<li class="wcv-widget-store-state">' . esc_attr( $state ) . '</li>' : '';
			echo ! empty( $post_code ) ? '<li class="wcv-widget-store-post-code">' . esc_attr( $post_code ) . '</li>' : '';
			echo '</ul>';
		}

		$maps_api_key = isset( $instance['maps_api_key'] ) ? $instance['maps_api_key'] : $instance['maps_api_key']['std'];

		if ( ! $hide_map && ! empty( $maps_api_key ) ) {

			$show_map_heading = isset( $instance['show_map_heading'] ) ? $instance['show_map_heading'] : $instance['show_map_heading']['std'];

			$map_heading = isset( $instance['map_heading'] ) ? $instance['map_heading'] : $instance['map_heading']['std'];
			$map_width   = isset( $instance['map_width'] ) ? $instance['map_width'] : $instance['map_width']['std'];
			$map_height  = isset( $instance['map_height'] ) ? $instance['map_height'] : $instance['map_height']['std'];
			$zoom_level  = isset( $instance['zoom_level'] ) ? $instance['zoom_level'] : $this->settings['zoom_level']['std'];

			?>
			<style>
				#wcvendors_pro_map_widget {
					width: <?php echo esc_attr( $map_width ); ?>;
					height: <?php echo esc_attr( $map_height ); ?>;
				}
			</style>
			<?php echo $show_map_heading && ! empty( $map_heading ) ? '<p class="wcv-widget-map-heading">' . esc_attr( $map_heading ) . '</p>' : ''; ?>

			<div id="wcvendors_pro_map_widget"></div>
			<script>
				function initMap() {
					var map = new google.maps.Map(document.getElementById('wcvendors_pro_map_widget'), {
						zoom: <?php echo $zoom_level; ?>,
						center: {lat: -34.397, lng: 150.644}
					});
					var geocoder = new google.maps.Geocoder();
					jQuery(document).ready(function () {
						geocodeAddress(geocoder, map);
					});
				}


				function geocodeAddress(geocoder, resultsMap) {
					var address = '<?php echo esc_attr( $address_line1 ) . ', ' . esc_attr( $city ); ?>';
					geocoder.geocode({'address': address}, function (results, status) {
						if (status === 'OK') {
							resultsMap.setCenter(results[0].geometry.location);
							var marker = new google.maps.Marker({
								map: resultsMap,
								position: results[0].geometry.location
							});
						} else {
							console.log('Geocode was not successful for the following reason: ' + status);
						}
					});
				}
			</script>
			<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $maps_api_key ); ?>&callback=initMap"></script>
			<?php
		}

		$this->widget_end( $args );
	}

	/**
	 * Custom google maps field so that it can be formatted correctly.
	 */
	public function google_maps_field( $key, $value, $setting, $instance ) {

		$class = isset( $setting['class'] ) ? $setting['class'] : '';
		$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo $setting['label']; ?></label>
								   <?php
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									?>
			<input class="widefat <?php echo esc_attr( $class ); ?>"
				   id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text"
				   value="<?php echo esc_attr( $value ); ?>"/>
			<strong><?php _e( 'Required for your map to work', 'wcvendors-pro' ); ?></strong>
		</p>
		<?php if ( empty( $value ) ) : ?>
			<p>
				<a class="button" target="_blank"
				   href="<?php echo admin_url( 'admin.php?page=wcv-settings&tab=forms' ); ?>">
					<?php _e( 'Setup Google MAPS API Key', 'wcvendors-pro' ); ?>
				</a>
				<?php _e( 'or', 'wcvendors-pro' ); ?>
				<a class="button" target="_blank"
				   href="https://developers.google.com/maps/documentation/javascript/get-api-key">
					<?php _e( 'Get Google MAPS API Key', 'wcvendors-pro' ); ?>
				</a>
			</p>
			<p><?php _e( 'Select the maps and places options when creating your key', 'wcvendors-pro' ); ?></p>
		<?php endif; ?>

		<?php
	}

	/**
	 * Widget settings form
	 *
	 * @return void
	 * @since 1.5.4
	 */
	public function form( $instance ) {
		parent::form( $instance );
	}
}
