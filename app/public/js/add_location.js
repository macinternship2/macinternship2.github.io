var user_input_marker;
var map;

function initMap() {
	// initial map location
	var initPoint = {lat: parseFloat($('#latitude').val()), lng: parseFloat($('#longitude').val()) };
	var mapDiv = document.getElementById('map');
	var options = {
	  zoom: 17,
	  center: initPoint,
	  draggable: false,
	  streetViewControl: false,
	  clickableIcons: false
	};
	//creating the map
	map = new google.maps.Map(mapDiv, options);

	user_input_marker = new google.maps.Marker({
			map: map,
			position: initPoint
		});

	// When clicking on map, move center of map there.
	google.maps.event.addListener(map, 'mousedown', function(event) {
		setLocationPosition(event.latLng);
		locationInfo(map, event.latLng);
  	});

	var address_timer = new DelayedNonEmptyTimer(1000, getAddress, processAddress);
	var name_timer = new DelayedNonEmptyTimer(300, getName, processName);
	$('#address').on('keydown keyup change blur', address_timer.delayedProcess);
	getNameElement().on('keydown keyup change blur', name_timer.delayedProcess);

	// sanitize values on blur.
	getNameElement().on('blur', singleSpaceAndTrim('name'));
	$('#address').on('blur', singleSpaceAndTrim('address'));
	$('#phone_number').on('blur', singleSpaceAndTrim('phone_number'));
	$('#external_web_url').on('blur', sanitizeExternalWebURL);

	updateNearbyLocationMarkers();
}

function sanitizeExternalWebURL() {
	var field_id = 'external_web_url';
	var $element = $('#' + field_id);
	singleSpaceAndTrim(field_id)();
	var val = $element.val();
	if ( val !== '' ) {
		var index = val.indexOf(':/');
		// Fix missing extra slash in protocol.  
		// For example, 'http:/www.google.com' instead of 'http://www.google.com'.
		if ( index < 8 && val.indexOf('://') !== index ) {
			val = val.substring(0, index) + '://' + val.substring(index + 2);
		}
		var protocol = val.split('://');
		// If no protocol specified, use http.
		if ( protocol.length === 1 ) {
			val = 'http://' + val;
		}
		else {
			protocol = protocol[0].toLowerCase();
			// Use one of the HTTP protocols instead of anything else like ftp.
			if ( ['http', 'https'].indexOf(protocol) === -1 ) {
				protocol = 'http';
			}
			val = protocol + '://' + val.substring(val.indexOf('://') + 3);
		}
		// Replace with sanitized value.
		$element.val(val);
	}
}

function singleSpaceAndTrim(field_id) {
	return function() {
		var $element = $('#' + field_id);
		$element.val($element.val().trim().replace(/\s+/g, ' '));
	};
}

function setHiddenValue(param_name, value) {
	$('#' + param_name).val(value);
}

function updateNearbyLocationMarkers() {
	nearby_locations.filter(function(location) {
		return location.shape === undefined; 
		// filter to locations that don't yet have corresponding shapes.
	}).forEach(function(location) {
		var default_options = {
            strokeColor: '#FF0000',
            strokeOpacity: 0.7,
            strokeWeight: 1,
            fillColor: '#FF0000',
            fillOpacity: 0.25,
            map: map,
            center: {lat: location.latitude, lng: location.longitude},
            radius: 5,
			title: location.name
          };
		location.shape = new google.maps.Circle(default_options);

		// Show location name while hovering.
		google.maps.event.addListener(location.shape, 'mouseover', function() {
             this.getMap().getDiv().setAttribute('title', location.name);
			 this.setOptions({
				strokeOpacity: 1.0,
				fillOpacity: 0.5
			 });
		});
		google.maps.event.addListener(location.shape, 'mouseout', function() {
			 this.setOptions(default_options);
		});
	});
}

function addLocationsNear(longitude, latitude) {
	var url = '/location/management/nearby/' + longitude + '/' + latitude;
	$.ajax({
		'method': 'GET',
		'url': url,
		'success': function(response) {
			response.forEach(function(location) {
				// Check if the location is already in nearby_locations.
				var matches = nearby_locations.filter(function(existing_nearby_location) {
					return existing_nearby_location.id === location.id;
				});

				if ( matches.length === 0 ) {
					// Not in nearby_locations so add it.
					nearby_locations.push(location);
				}
			});
			updateNearbyLocationMarkers();
		}
	});
}
/*
Maps the clicked coordinates on a map to an address and set that address as a current location
*/
function locationInfo(map, lat_lng)
{
	getAddressFromLatLng(lat_lng).then(function(address_info) {
		document.getElementById("address").value=address_info.address;
	});
}

function setLocationPosition(new_position) {
	map.setCenter(new_position);
	user_input_marker.setPosition(new_position);
	setHiddenValue('longitude', new_position.lng());
	setHiddenValue('latitude', new_position.lat());
	addLocationsNear(new_position.lng(), new_position.lat());
}

function getNameElement() {
	return $('#name');
}

function getName() {
	return getNameElement().val();
}

function processName(new_name) {
	// Avoid route match problems from having slashes in the name.
	new_name = new_name.replace(/[\/\\]/g, ' ');
	var api_route = '/location/management/suggestions/';
	$.ajax({
		'method': 'GET',
		'url': api_route + new_name,
		'success': function(response) {
			// update the location tags.
			var selected_a_location_tag = false;
			$('#location_tags option:not(#location-tag-i-do-not-know)').each(function() {
				var $this = $(this);
				$this.prop('selected', response.location_tags[$this.val()].is_matched);
				selected_a_location_tag = true;
			});
			if( selected_a_location_tag ) {
				$('#location-tag-i-do-not-know').prop('selected', false);
			}

			// update the location group.
			if( response.location_group === null ) {
				response.location_group = '-';
			}
			$('#location_group_id').val(response.location_group);
		},
		'error': function() {
			console.error('Failed to get respone from ' + api_route);
		}
	});
}

function getAddress() {
	return $('#address').val();
}

function processAddress(address) {
	//creating a geocoder object
	var geocoder = new google.maps.Geocoder();

	geocoder.geocode({'address': address}, function(results, status)
	{
		//If this was successful, then...
		if (status === 'OK')
		{
			setLocationPosition(results[0].geometry.location);
		}
		else {
			console.error('Google Maps could not find a longitude and latitude for address: ', address);
		}
	});
}