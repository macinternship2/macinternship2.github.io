/*
location_report.js is used in the report on a single location, 
pages/location_report/collapsed.blade.php.
*/
function initMap() {
	var map = new google.maps.Map(document.getElementById('map'), {
	  zoom: 15,
	  center: locationPoint,
	  draggable: false,
	  streetViewControl: false
	});
	var marker = new google.maps.Marker({
	  position: locationPoint,
	  map: map
	});

	function centreLocation() {
		map.setCenter(locationPoint);
	}

	google.maps.event.addDomListener(window, 'resize', centreLocation);
	google.maps.event.addDomListener(marker, 'click', toggleRatingsPopup);
	$(window).resize(function() {
		google.maps.event.trigger(map, "resize");
	});
}

function hideLocationTagIcons() {
	$('.location-tag').attr('title', '');
	$('.location-report').removeClass('show-location-tag-icons');
}

function showLocationTagIcons() {
	$('.location-report').addClass('show-location-tag-icons');
	$('.location-tag').each(function(index, e) {
		var $e = $(e);
		$e.attr('title', $e.find('.name').text());
	});
}

var location_tag_width_when_expanded = undefined;

function updateShowLocationTagIcons() {
	if( location_tag_width_when_expanded === undefined && $('.show-location-tag-icons').length === 0 ) {
		location_tag_width_when_expanded = 0;
		var gap = 5;
		$('.location-tag').each(function(index, e) {
			location_tag_width_when_expanded += $(e).outerWidth() + gap;
		});
		// If the width is actually really small, we still want to treat it like it is at least 100 pixels
		// because it looks inconsistent to have the icons used on some location 
		// reports(with only 1 tag) but not others(with many like Devonshire Mall).
		location_tag_width_when_expanded = Math.max(200, location_tag_width_when_expanded);
	}
	var $container = $('.location-tags');
	if( location_tag_width_when_expanded !== undefined && location_tag_width_when_expanded > $container.outerWidth() ) {
		showLocationTagIcons();
	}
	else {
		hideLocationTagIcons();
	}
}

function updateHeightOfMap() {
  var $map = $('#map');
  var $copyright = $('#copyright');
  var extra_height = $map.offset().top + $copyright.height();
  var height = window.innerHeight - extra_height;
  var viewport_width = $(window).width();
  if (height < 250)
	height = 250;

  if ( viewport_width > 990 ) {
	  $map.css('height', 'calc(100vh - ' + extra_height + 'px)');
  }
  else {
	  $map.css('height', '');
  }
}

function hideRatingsPopup() {
	$('body').removeClass('show-ratings-popup');
}

function showRatingsPopup() {
	$('body').addClass('show-ratings-popup');
}

function toggleRatingsPopup() {
	$('body').toggleClass('show-ratings-popup');
}

function setupToggleRatingsPopup() {
	$('.questions-box > .text-center').click(hideRatingsPopup);
}

$(window).resize(function() {
	updateHeightOfMap();
	updateShowLocationTagIcons();
});
document.addEventListener("DOMContentLoaded", function(event) {
	updateHeightOfMap();
	updateShowLocationTagIcons();
	setupToggleRatingsPopup();
});

// function checkForm(){
	
// }

$(document).ready(function(){
	$("#suggestionFormConfirm").click(function(){
		var location_id = $("#location-id").val().trim();
		var location_name = $("#location-name").val().trim();
		var address = $("#address").val().trim();
		var phonenumber = $("#phone-number").val().trim();
		var url = $("#url").val().trim();
		var token = $('[name="_token"]').val();
		if(location_name != ''){
			$.ajax({
				'method': 'POST',
				'data': {
					'_token': token,
					'location-id': $("#location-id").val().trim(),
					'location-name': location_name,
					'phone-number': phonenumber,
					'address': address,
					'url': url
				},
				'url': '/api/add-suggestion',
				'success': function(r) {
					if(r.success == 0){
						location.href = '/signin';
					} else if(r.success == 1){
						var msg = '';
						if(typeof(r.message) == 'object'){
							for(key in r.message){
								msg += r.message[key];
								msg += '\n';
							}
						} else {
							msg = r.message;
						}
						alert(msg);
					} else 
						alert("Suggestion has been created.");
				}
			});
		} else {
			alert("location-name cannot be empty");
		}
	});
})

