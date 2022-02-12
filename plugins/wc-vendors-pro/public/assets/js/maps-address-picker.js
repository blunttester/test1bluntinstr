/**
 * Google Maps Address Autocomplete
 * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
 */
/* global google */
/* global wcv_frontend_general */
(function($) {
	var autocomplete;

	var componentForm = {
		locality: 'long_name',
		administrative_area_level_1: 'short_name',
		country: 'long_name',
		postal_code: 'short_name'
	};

	var map_address_fields = {
		locality: '_wcv_store_city',
		administrative_area_level_1: '_wcv_store_state',
		country: '_wcv_store_country',
		postal_code: '_wcv_store_postcode'
	};

	var initializeMap = function() {
		if (typeof google != 'undefined' && google) {
			// Create the autocomplete object, restricting the search to geographical
			// location types.
			autocomplete = new google.maps.places.Autocomplete(
				/** @type {!HTMLInputElement} */ (document.getElementById(
					'_wcv_store_search_address'
				)),
				{ types: ['geocode'] }
			);

			// new map
			var map = new google.maps.Map(
				document.getElementById('wcv_location_picker'),
				{
					zoom: parseInt(wcv_frontend_general.map_zoom_level),
					center: { lat: getFormLatitude(), lng: getFormLongitude() }
				}
			);

			// new geocoder
			var geocoder = new google.maps.Geocoder();

			// new marker
			var marker = new google.maps.Marker({
				map: map,
				position: new google.maps.LatLng(getFormLatitude(), getFormLongitude()),
				draggable: true,
				animation: google.maps.Animation.DROP
			});

			initilizeGeoCoder(geocoder, marker, map);

			wcvGeoCode({
				geocoder: geocoder,
				marker: marker,
				map: map,
				what: 'address'
			});

			// When the user selects an address from the dropdown, populate the address fields in the form.
			autocomplete.addListener('place_changed', function() {
				fillInAddress(null);
				wcvGeoCode({
					geocoder: geocoder,
					marker: marker,
					map: map,
					what: 'address'
				});
			});
		} else {
			$('#wcv_location_picker').hide();
		}
	};

	/**
	 * Fill in the address fields on the form
	 */
	var fillInAddress = function(results) {
		// Get the place details from the autocomplete object or from the results of the clicked location.
		var place =
			results != null && results != undefined
				? results
				: autocomplete.getPlace();

		if (place == undefined || place == null) {
			return;
		}

		// Clear existing value
		$('#_wcv_store_city').val('');
		$('#_wcv_store_state').val('');
		$('#_wcv_store_country').val('');
		$('#_wcv_store_postcode').val('');

		// Get each component of the address from the place details and fill the corresponding field on the form.
		const findKey = key => e => e.types[0] == key;

		const formatted = place.formatted_address;

		const components = place.address_components;

		const streetComponent = components.find(findKey('route'));

		const number = components.find(findKey('street_number'))?.short_name;

		if (streetComponent && number) {
			var short = streetComponent.short_name;

			var long = streetComponent.long_name;

			const street = formatted.includes(short) ? short : long;

			const locale1 = [street, number].join(' ');

			const locale2 = [number, street].join(' ');

			var address1 = formatted.includes(locale1) ? locale1 : locale2;

			address1 = address1.trim();

			$('#_wcv_store_search_address').val(place.formatted_address);
			$('#_wcv_store_address1').val(address1);
		}

		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (map_address_fields[addressType]) {
				var val = place.address_components[i][componentForm[addressType]];
				if (addressType == 'country') {
					var country_code = place.address_components[i]['short_name'];
					$('#_wcv_store_country')
						.val(country_code)
						.trigger('change');
				} else {
					document.getElementById(map_address_fields[addressType]).value = val;
				}
			}
		}

		setLatitudeLongitude(
			place.geometry.location.lat(),
			place.geometry.location.lng()
		);
	};

	/**
	 * initialize the Google Maps geocoder and add event listeners
	 */
	var initilizeGeoCoder = function(geocoder, marker, resultsMap) {
		var address = $('#_wcv_store_search_address').val();
		geocoder.geocode({ address: address }, function(results, status) {
			if (status === 'OK') {
				resultsMap.setCenter(results[0].geometry.location);

				resultsMap.setCenter({
					lat: parseFloat(results[0].geometry.location.lat()),
					lng: parseFloat(results[0].geometry.location.lng())
				});
				// Listen for drag events!
				google.maps.event.addListener(marker, 'dragend', function(event) {
					wcvGeoCode({
						geocoder: geocoder,
						marker: marker,
						map: resultsMap,
						what: 'latlng',
						position: event.latLng
					});
					resultsMap.panTo(event.latLng);
				});

				resultsMap.addListener('click', function(event) {
					wcvGeoCode({
						geocoder: geocoder,
						marker: marker,
						map: resultsMap,
						what: 'latlng',
						position: event.latLng
					});
					resultsMap.panTo(event.latLng);
				});
			} else {
				// eslint-disable-next-line
				console.log(
					'Geocode was not successful for the following reason: ' + status
				);
			}
		});
	};

	/**
	 * Geocode address or latLng coordinates
	 */
	function wcvGeoCode(args) {
		var options;

		if (args.what == 'address') {
			var address = document.getElementById('_wcv_store_search_address').value;
			options = { address: address };
		} else if (args.what == 'latlng') {
			var latLng = {
				lat: parseFloat(args.position.lat()),
				lng: parseFloat(args.position.lng())
			};

			options = { location: latLng };
		}

		if (options == undefined) {
			return;
		}

		args.geocoder.geocode(options, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (args.what == 'address') {
					$('#_wcv_store_search_address').val(results[0].formatted_address);
				}

				args.map.setCenter({
					lat: parseFloat(results[0].geometry.location.lat()),
					lng: parseFloat(results[0].geometry.location.lng())
				});

				args.marker.setPosition({
					lat: parseFloat(results[0].geometry.location.lat()),
					lng: parseFloat(results[0].geometry.location.lng())
				});

				fillInAddress(results[0]);
				args.map.panTo(results[0].geometry.location);
			} else {
				// eslint-disable-next-line
				console.log(
					wcv_frontend_general.cannot_find_address_text + ' ' + status
				);
			}
		});
	}

	/**
	 * Fill in coordinate fields on the form
	 */
	var setLatitudeLongitude = function(latitude, longitude) {
		$('#wcv_address_latitude').val(latitude);
		$('#wcv_address_longitude').val(longitude);
	};

	/**
	 * Get the latitude from the latitude input field
	 */
	var getFormLatitude = function() {
		if (
			$('#wcv_address_latidude').val() != '' &&
			$('#wcv_address_latidude').val() != null
		) {
			return parseFloat($('#wcv_address_latitude').val());
		} else {
			return -34.397;
		}
	};

	/**
	 * Get the longitude field from the longitude field
	 */
	var getFormLongitude = function() {
		if (
			$('#wcv_address_longitude').val() != '' &&
			$('#wcv_address_longitude').val() != null
		) {
			return parseFloat($('#wcv_address_longitude').val());
		} else {
			return 29.644;
		}
	};

	/**
	 * Bias the autocomplete object to the user's geographical location,
	 * as supplied by the browser's 'navigator.geolocation' object.
	 */
	var geoLocatePosition = function() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				/* Current Coordinate */
				var lat = position.coords.latitude;
				var lng = position.coords.longitude;
				var google_map_pos = new google.maps.LatLng(lat, lng);

				/* Use Geocoder to get address */
				var google_maps_geocoder = new google.maps.Geocoder();
				google_maps_geocoder.geocode({ latLng: google_map_pos }, function(
					results,
					status
				) {
					if (status === google.maps.GeocoderStatus.OK && results[0]) {
						$('#_wcv_store_search_address')
							.val(results[0].formatted_address)
							.trigger('change');
					}
				});
			});
		}
	};

	var showHideMapText = function() {
		if ($('#wcv_location_picker').is(':visible')) {
			$('#show_location_picker').text(
				wcv_frontend_general.hide_location_picker_text
			);
		} else {
			$('#show_location_picker').text(
				wcv_frontend_general.use_location_picker_text
			);
		}
	};

	var get_current_address = function() {
		var $search_field = $('#_wcv_store_search_address');
		if ($search_field.val().length == 0) {
			return '';
		}
		var address1 = $('#_wcv_store_address1').val();
		var state = $('#_wcv_store_state').val();
		var country = $('#_wcv_store_country').val();
		var postcode = $('#_wcv_store_postcode').val();
		var current_address =
			address1 + ' ' + state + ' ' + postcode + ' ' + country;
		current_address = current_address.replace(/ +(?= )/g, '');
		current_address = current_address.trim();

		return current_address;
	};

	/**
	 * Initialize map when document is ready
	 */
	$(document).ready(function() {
		var current_address = get_current_address();
		var $search_field = $('#_wcv_store_search_address');

		$search_field.on('change', function() {
			initializeMap();
		});

		$('#show_location_picker').click(function(e) {
			e.preventDefault();

			$('#wcv_location_picker').toggle();

			showHideMapText();
		});

		$('#use_current_position').click(function(e) {
			e.preventDefault();
			geoLocatePosition();
		});

		if (current_address.length > 0) {
			$search_field.val(current_address).trigger('change');
		} else {
			geoLocatePosition();
		}
	});
})(jQuery);
