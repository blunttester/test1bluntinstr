(function($) {
	'use strict';

	/**
	 * Code required to create the charts
	 */

	$( window ).on(
		'load',
		function() {
		// Get random number between two values
		var wcv_random = function(min, max) {
				return Math.floor( Math.random() * (max - min + 1) ) + min;
		};

		// Cut # out of hexadecimal color value
		var wcv_cut_hex = function cutHex(hexcode) {
				return hexcode.charAt( 0 ) == '#' ? hexcode.substring( 1, 7 ) : hexcode;
		};

		// Convert 2 digit RGB to hexadecimal
		var wcv_rgb_to_hex = function(rgb) {
				var hex = Number( rgb ).toString( 16 );
				if (hex.length < 2) {
					hex = '0' + hex;
					}
				return hex;
		};

		// Convert RGB values to full hexadecimal color
		var wcv_full_hex_color = function(r, g, b) {
				var red   = wcv_rgb_to_hex( r );
				var green = wcv_rgb_to_hex( g );
				var blue  = wcv_rgb_to_hex( b );

				return red + green + blue;
		};

		// Get red value from hexadecimal value
		var wcv_get_red = function(hexcode) {
				return parseInt( wcv_cut_hex( hexcode ).substring( 0, 2 ), 16 );
		};

		// Get green value from hexadecimal value
		var wcv_get_green = function(hexcode) {
				return parseInt( wcv_cut_hex( hexcode ).substring( 2, 4 ), 16 );
		};

		// Get blue value from hexadecimal value
		var wcv_get_blue = function(hexcode) {
				return parseInt( wcv_cut_hex( hexcode ).substring( 4, 6 ), 16 );
		};

		// Only run on dashboard page
		if (typeof orders_chart_label !== 'undefined') {
				if ('yes' == chartjs_colors.use_random) {
					// Random RGB Fill
					var fill_red   = wcv_random( 0, 255 );
					var fill_green = wcv_random( 0, 255 );
					var fill_blue  = wcv_random( 0, 255 );

					// Random RGB Stroke
					var stroke_red   = wcv_random( 0, 255 );
					var stroke_green = wcv_random( 0, 255 );
					var stroke_blue  = wcv_random( 0, 255 );

					// Random RGB hover
					var hover_fill_red   = wcv_random( 0, 255 );
					var hover_fill_green = wcv_random( 0, 255 );
					var hover_fill_blue  = wcv_random( 0, 255 );

					// Random RGB hover Stroke
					var hover_stroke_red   = wcv_random( 0, 255 );
					var hover_stroke_green = wcv_random( 0, 255 );
					var hover_stroke_blue  = wcv_random( 0, 255 );
					} else {
					// Make the RGB fill color from hexadecimal value
					var fill_red   = wcv_get_red( chartjs_colors.fill_color );
					var fill_green = wcv_get_green( chartjs_colors.fill_color );
					var fill_blue  = wcv_get_blue( chartjs_colors.fill_color );

					// Make the RGB stroke color from hexadecimal value
					var stroke_red   = wcv_get_red( chartjs_colors.stroke_color );
					var stroke_green = wcv_get_green( chartjs_colors.stroke_color );
					var stroke_blue  = wcv_get_blue( chartjs_colors.stroke_color );

					// Make the RGB hover fill color from hexadecimal value
					var hover_fill_red   = wcv_get_red( chartjs_colors.hover_fill_color );
					var hover_fill_green = wcv_get_green( chartjs_colors.hover_fill_color );
					var hover_fill_blue  = wcv_get_blue( chartjs_colors.hover_fill_color );

					// Make the RGB hover stroke color from hexadecimal value
					var hover_stroke_red   = wcv_get_red( chartjs_colors.hover_stroke_color );
					var hover_stroke_green = wcv_get_green(
					chartjs_colors.hover_stroke_color
					);
					var hover_stroke_blue  = wcv_get_blue( chartjs_colors.hover_stroke_color );
					}

				var fill_opacity   = chartjs_colors.fill_opacity
				? chartjs_colors.fill_opacity
				: 1;
				var stroke_opacity = chartjs_colors.stroke_opacity
				? chartjs_colors.stroke_opacity
				: 1;

				var hover_fill_opacity = chartjs_colors.hover_fill_opacity
				? chartjs_colors.hover_fill_opacity
				: 1;

				var hover_stroke_opacity = chartjs_colors.hover_stroke_opacity
				? chartjs_colors.hover_stroke_opacity
				: 1;

				var orderdata = {
					labels: orders_chart_label,
					datasets: [
					{
						label: 'My First dataset',
						fillColor:
							'rgba(' +
							fill_red +
							',' +
							fill_green +
							',' +
							fill_blue +
							',' +
							fill_opacity +
							')',
						strokeColor:
							'rgba(' +
							stroke_red +
							',' +
							stroke_green +
							',' +
							stroke_blue +
							',' +
							stroke_opacity +
							')',
						hoverFill:
							'rgba(' +
							hover_fill_red +
							',' +
							hover_fill_green +
							',' +
							hover_fill_blue +
							',' +
							hover_fill_opacity +
							')',
						hoverStroke:
							'rgba(' +
							hover_stroke_red +
							',' +
							hover_stroke_green +
							',' +
							hover_stroke_blue +
							',' +
							hover_stroke_opacity +
							')',
						data: orders_chart_data
						}
					]
					};

				var orders_chart_canvas = document
				.getElementById( 'orders_chart' )
				.getContext( '2d' );
				var ordersBarChart      = new Chart( orders_chart_canvas ).Bar(
				orderdata,
				{
				responsive: true
					}
					);
		}

		// Only run on dashboard page
		if (typeof pieData !== 'undefined') {
				var products_chart_canvas = document
				.getElementById( 'products_chart' )
				.getContext( '2d' );
				window.myPie              = new Chart( products_chart_canvas ).Pie(
				pieData,
				{
				responsive: true
					}
					);
		}
	}
		);
})( jQuery );
