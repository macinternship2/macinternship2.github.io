$(window).on("beforeunload", function(e) {
	var dialogtxt = 'do you want to save your data?';
	if(isProfileChanged) {
		e.returnValue = dialogtxt;
		return dialogtxt;
	}
});
