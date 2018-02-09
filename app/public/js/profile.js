/*
profile.js is used in the profile.blade.php view.
*/

var regions = [];

function updateButtonCaption($category_element)
{
	// Sanitize $category_element if it is a 
	// descendent element of the category.
	if ( !$category_element.hasClass('category') )
	{
		$category_element = $category_element.closest('.category');
	}
	var numberOfCheckboxes = $category_element.find("div.questions input:checkbox").length;
	var numberOfcheckedCheckboxes = $category_element.find("div.questions input:checked").length;
	var isAllChecked = ( numberOfCheckboxes === numberOfcheckedCheckboxes );
	if ( isAllChecked )
	{
		$category_element.find("button.select-all").text("Unselect All");
	}
	else
	{
		$category_element.find("button.select-all").text("Select All");
	}
}

// initializes the Select All/Unselect All buttons
function initSelectAllText()
{
	$.each($("div.category"), function(index, element)
	{
		updateButtonCaption($(element));
	});
	selectAllToggle();
	bindCheckboxes();
}

function bindCheckboxes() {
	$("div.questions input:checkbox").change(function()
	{
		updateButtonCaption($(this));
	});
}

// Toggles between Select All and Unselect All
function selectAllToggle()
{
	$('button.select-all').click(function(){
		var $checkboxes = $(this).closest("div.category").find("input:checkbox");
		if ( $(this).text() === "Select All" ) {
			$checkboxes.prop('checked', true);
			$(this).text('Unselect All');
		} else {
			$checkboxes.removeAttr('checked');
			$(this).text('Select All');
		}
	})
}
// uploads the profile photo by submitting the image upload form
// Called after selecting a photo and hitting "Open"
function upload()
{
	// indicate that the uploading is starting.
	$element = $('.photo-display .progress-element').addClass('uploading');
	
    $("#photo-upload").submit();
}

// Opens a dialog for the user to select an image
function selectImageFile()
{
    $(".hidden-uploader").click();
}

function randomizePhotoURL()
{
	var deferred = $.Deferred();
	$element = $('.photo-display .uploaded-photo');
	// If a photo was uploaded for the current user
	if ( $element.length !== 0 )
	{
		var src = "/profile-photo?t=" + (new Date().getTime());
		var $img = $( '<img src="' + src + '">' );
		$img.bind( 'load', function(){
			$element.css( 'background-image', 'url(' + src + ')' );
			deferred.resolve();
		} );
		if( $img[0].width ){ $img.trigger( 'load' ); }
		
		// Set a new URL so cache won't interfere with refreshing newly uploaded photos.
		$element.css({
			'background-image': "url(\'" + src + "\')"
		});
	}
	else {
		deferred.resolve();
	}
	return deferred.promise();
}

function getCountryElement()
{
	return $('#home_country_id');
}

function updateRegionOptions()
{
	var country_id = parseInt(getCountryElement().val());
	var $datalist = $('#regions');
	$datalist.empty();
	regions.forEach(function(region) {
		if ( region.country_id === country_id )
			$datalist.append($('<option />').text(region.name));
	});
}

function rotateImage()
{
	var token = $('[name="_token"]').val();
	$.ajax({
		'method': 'POST',
		'data': {
			'_token': token
		},
		'url': '/profile-photo-rotate',
		'success': function() {
			randomizePhotoURL().then(showRotateFeature);
		}
	});
}

// Used for the State/Province datalist
function downloadRegions()
{
	return $.ajax({
		'method': 'GET',
		'url': '/api/regions',
		'success': function(response) {
			regions = response;
		}
	});
}

var rotate_feature_timer = false;

function showRotateFeature() {
	if ( rotate_feature_timer ) {
		clearTimeout(rotate_feature_timer);
	}
	var $profile_photo_rotate = $('#profile-photo-rotate');
	$profile_photo_rotate.addClass('overlay').css('display', 'block');
	// profile photo rotate icon will disappear in 20 secs
    rotate_feature_timer = setTimeout(function() {
		$profile_photo_rotate.removeClass('overlay');
		rotate_feature_timer = setTimeout(function() {
			$profile_photo_rotate.css('display', 'none');
			rotate_feature_timer = false;
		}, 2000);
    }, 8000);
}

$( function() {
	$( "#accordion" ).accordion({
		heightStyle: "content" 
	});
	initSelectAllText();
	randomizePhotoURL();
	getCountryElement().change(updateRegionOptions);
	downloadRegions().then(updateRegionOptions);
	if ( window.location.href.indexOf('show_rotate_feature') !== -1 ) {
		window.history.pushState("removing_rotate_feature", "Removing Rotate Feature", "/profile");
		showRotateFeature();
	}
} );