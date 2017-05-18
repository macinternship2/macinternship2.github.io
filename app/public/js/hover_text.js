
var time_limit = 5; // seconds
var timer;
var location_tag_descriptions = {};

function goHidden() {
	var $e = getHoverTextElement();
	$e.remove();
	clearTimeout(timer);
	timer = undefined;
}

// hides hover text message immediately 
// and erases any timer ids.
// Called only by a setTimeout
function hideHoverText()
{
	var $e = getHoverTextElement();
	if ( $e.length !== 0 )
	{
		$e.addClass('hiding');
		clearTimeout(timer);
		timer = setTimeout(goHidden, 1000);
	}
}

// shows text that gets hidden after a few seconds
function delayedHide(messageToShow)
{
	if ( timer )
	{
		// cancel older timeout that would 
		// otherwise hide the hover text.
		clearTimeout(timer);
	}
	showHoverText(messageToShow);
	timer = setTimeout(function() {
		timer = setTimeout(hideHoverText, time_limit * 1000);
	}, 1300);
}

function getLocationTagId($e)
{
	var href = $e.attr('href');
	var index = href.indexOf('location_tag_id=');
	href = href.substring(index + 'location_tag_id='.length);
	index = href.indexOf('&');
	if ( index > -1 ) {
		href = href.substring(0, index);
	}
	
	return parseInt(href);
}

$(document).ready(function()
{
	$location_tag_elements = $(".location-tag a");
	$location_tag_elements.each(function() {
		var $this = $(this);
		var title = $this.attr('title');
		var location_tag_id = getLocationTagId($this);
		location_tag_descriptions[location_tag_id] = title;
		$this.attr('title', '');
	});
	$location_tag_elements.hover(function(){
		delayedHide(location_tag_descriptions[getLocationTagId($(this))]);
	}); 
});

function getHoverTextElement()
{
	return $('#hover-text-display');
}

// shows specified text immediately
function showHoverText(messageToShow)
{
	var newDivForMessage = getHoverTextElement();
	if ( newDivForMessage.length === 0 )
	{
		newDivForMessage = $('<div />').addClass('hiding');
		newDivForMessage.attr('id', 'hover-text-display');
		$('#main').append(newDivForMessage);
		setTimeout(function() {
			newDivForMessage.removeClass('hiding');
		}, 20);
	}
	else {
		newDivForMessage.removeClass('hiding');
	}
	newDivForMessage.text(messageToShow);
};

