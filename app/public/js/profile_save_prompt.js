$(window).on('beforeunload', function(e) {
	if ( submitted ) {
		return;
	}	
	if ( e === undefined ) {
		e = window.event;
	}
	return undefined;
});
