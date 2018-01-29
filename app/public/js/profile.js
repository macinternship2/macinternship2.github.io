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
	$element = $('.photo-display .uploaded-photo');
	// If a photo was uploaded for the current user
	if ( $element.length !== 0 )
	{
		// Set a new URL so cache won't interfere with refreshing newly uploaded photos.
		$element.css({
			'background-image': "url(\'/profile-photo?t=" + (new Date().getTime()) + "\')"
		});
	}
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
	$.ajax({
		'method': 'GET',
		'url': '/profile-photo-rotate',
	}).then(randomizePhotoURL);
	
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

$( function() {
	$( "#accordion" ).accordion({
		heightStyle: "content" 
	});
	initSelectAllText();
	randomizePhotoURL();
	getCountryElement().change(updateRegionOptions);
	downloadRegions().then(updateRegionOptions);
} );

// profile photo rotate icon will disappear in 20 secs
 $(document).ready(function () {          
    setTimeout(function() {
        $('#profile-photo-rotate').slideUp("slow");
    }, 20000);
});