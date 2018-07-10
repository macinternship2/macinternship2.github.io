/*
home.js provides various functions used in the home page.

This includes defining how the Google Map behaves and processes addresses entered by the user.
*/

//basic variables that I declared outside the function blocks to use them inside any of the two functions.
var map;
var marker;

/*
Returns the location search text entered by the user or its placeholder text if the text is otherwise empty.
*/
function getAddress()
{
	var address = $('#address').val().trim();
	if ( address === '' )
	{
		address = $('#address').attr('placeholder');
	}
	return address;
}

var  lastProcessedAddress = '';
function conditionalProcessAddress() {
	if( lastProcessedAddress.trim() !== getAddress().trim()  ) {
		lastProcessedAddress = getAddress();
		processAddress();
	}
}

var timer = undefined;
function delayedProcessAddress()
{
	if ( timer !== undefined )
	{
		clearInterval(timer);
		timer = undefined;
	}

	timer = setTimeout(conditionalProcessAddress, 1000);
}

/*
This function is where I initialized the map and then called the geocodeAddress function
perform the geocoding and display the address.
I had to set the zoom to 17 because that is the only point where I carousel-indicators
see the names of location around the users current location.
*/
function initMap()
{
    map = new google.maps.Map(document.getElementById('map'), {
		zoom: 15,
		draggable: false,
		streetViewControl: false,
		clickableIcons: false
	});
	default_location = new google.maps.LatLng(default_location.latitude, default_location.longitude);

	google.maps.event.addDomListener(window, "resize", function() {
		var center = map.getCenter();
		google.maps.event.trigger(map, "resize");
		map.setCenter(center);
	});
	google.maps.event.addListener(map, 'mousedown', function(event) {
		locationInfo(map, event.latLng);
		blinkMap($('#map')[0])
  	});
	conditionalProcessAddress();
	$('#address').bind('keyup change', delayedProcessAddress);
	$('#set-location-to-geo-location').click(setToGeoLocation);
	if (window.location.href.indexOf('setToGeoLocation') !== -1) {
		window.history.pushState("removing_setToGeoLocation", "Removing setToGeoLocation", "/");
		setToGeoLocation();
	}
}
/*
Maps the clicked coordinates on a map to an address and set that address as a current location
*/
function locationInfo(map, lat_lng)
{
	getAddressFromLatLng(lat_lng).then(function(address_info) {
		document.getElementById("address").value=address_info.address;
		conditionalProcessAddress();
	});
}

function saveUserLocation(address, latitude, longitude)
{
	saveSearchLocationWithOptionalAddress(
			new google.maps.LatLng(latitude, longitude), address);
}

/*
Processes the user-provided address by retrieving corresponding longitude, latitude and saving the
results through an asynchronous request to the server.
*/
function processAddress()
{
	var address = getAddress();

	//creating a geocoder object
	var geocoder = new google.maps.Geocoder();

	geocoder.geocode({'address': address}, function(results, status)
	{
		//If this was successful, then...
		if (status === 'OK')
		{
			//get the latitude and longitude: Note this is part of what I would store to the database or session
			var latitude = results[0].geometry.location.lat();
			var longitude = results[0].geometry.location.lng();
			setMarker(results[0].geometry.location);
			saveUserLocation(address, latitude, longitude);
		} else if (status=="ZERO_RESULTS")
		{
			//get the latitude and longitude in case when coordinates are provided in the address field and set the marker as well as string to the database or session
			var location=address.replace(/[()]/g, '').split(',');
			var latitude = location[0];
			var longitude = location[1];
			var new_location = {lat: parseFloat(latitude), lng: parseFloat(longitude)};
			setMarker(new_location);
			saveUserLocation(address, latitude, longitude);
		} else
		{
			console.error('Geocode was not successful for the following reason: ' + status);
		}
	});
}
// Places the marker on a provided location
function setMarker(location)
{
	map.setCenter(location);
	//place the marker on that location
	if ( marker === undefined )
	{
		marker = new google.maps.Marker({
			map: map
		});
	}
	marker.setPosition(location);
}

function getLocationCategoryIdFromURL(url)
{
	var index = url.indexOf('location_tag_id=');
	if ( index !== -1 )
	{
		var choppedUrl = url.substring(index + 'location_tag_id='.length);

		// Remove parameters after the location category/tag id
		index = choppedUrl.indexOf('&');
		if ( index !== -1 )
		{
			choppedUrl = choppedUrl.substring(0, index);
		}

		return choppedUrl;
	}
	else
	{
		throw new Error('Unable to find location_tag_id in URL: ' + url);
	}
}

function getKeywordsInputElement()
{
	return $('[name="keywords"]');
}

function updateCategoryLinksOffKeywords()
{
	// We want the category links on the home page
	// to filter also by keyword.
	// To accomplish this, we're adding the keywords as an encoded parameter
	// in the location search URL's of each location category.

	var keywords = getKeywordsInputElement().val().trim();
	keywords = encodeURIComponent(keywords); // encode keywords to safely use in URL.
	$('.location-tags a').each(function() {
		var $this = $(this);
		var oldUrl = $this.attr('href');
		var location_category_id = getLocationCategoryIdFromURL(oldUrl);
		var newUrl = '/location-search?location_tag_id=' + location_category_id;
		if ( keywords )
		{
			newUrl = newUrl + '&keywords=' + keywords;
		}
		$this.attr('href', newUrl);
	});
}

function redirectToHTTPSForSettingGeoLocation() {
	var new_url = window.location.href + '#setToGeoLocation';
	new_url = 'https' + new_url.substring(4); // replace protocol with https.
	window.location.href = new_url;
}

/*
Sets current search location to be whatever GPS coordinates are used.
*/
function setToGeoLocation()
{
	getCurrentGeolocation().then(function(latlon) {
		// if on desktop and not within 10km of Windsor, just use city hall.
		if (isDesktop()) {
			var distance = calculateDistance(latlon, default_location);
			if (distance > 10000) {
				latlon = default_location;
			}
		}

		locationInfo(map, latlon);
	}, function() {
		if (window.location.protocol === 'http:') {
			showMessage('<p>We need to redirect to get your physical location.</p>' +
			'<p>We do not have a signed SSL certificate yet but are working on it.  If you\'re asked if you want to accept the risk of navigating to HTTPS, please accept so we can finish getting your location.</p>').then(function() {
				redirectToHTTPSForSettingGeoLocation();
			});
		}
	});
	return false;
}

function bindCategoryLinksToKeywordInput()
{
	getKeywordsInputElement().keyup(updateCategoryLinksOffKeywords);
	updateCategoryLinksOffKeywords();
}

$(document).ready(bindCategoryLinksToKeywordInput);


function blinkMap(map1)
{
	$(map1).fadeOut(200, function(){ $(map1).fadeIn(200); });
}
