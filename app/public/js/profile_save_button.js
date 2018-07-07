$(function () {
	var $profileForm = $("#profileForm");
    var initialFormState = $profileForm.serialize();
    var submitted = false;
    var changed = false;
	$profileForm.change(function () {
		changed = $profileForm.serialize() !== initialFormState;
		$("#submitButton").prop('disabled', !changed);
    }).submit(function () {
		submitted = true;
    });

   	window.onbeforeunload = function load() {
        var $submitBtn = $('#submitButton');
        if (!$submitBtn.is(':disabled') && !submitted) {
			return true;
		}
		return;
    };
});
