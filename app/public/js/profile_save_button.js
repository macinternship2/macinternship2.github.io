var defaultFormValue;

// submitted is true if the user profile form is being submitted.
var submitted = false;

// Adjusts away some meaningless differences that could be in formData.
function sanitizeFormData(formData)
{
	var result = {};
	formData.forEach(function(name_value_pair) {
		result[name_value_pair.name] = name_value_pair.value;
	});
	// If the location_search_text is undefined, just treat it the same as empty.
	if (result.location_search_text === undefined) {
		result.location_search_text = '';
	}
	// If the location_search_radius_km was something like "1", treat the same as "1.00".
	result.search_radius_km = parseFloat(result.search_radius_km).toFixed(2);
	return result;
}

function isProfileChanged()
{
	function areObjectsEqual(obj1, obj2)
	{
		if (Object.keys(obj1).length !== Object.keys(obj2).length)
			return false;
		var result = true;
		Object.keys(obj1).forEach(function(key) {
			if ( obj1[key] !== obj2[key] ) {
				result = false;
			}
		});
		return result;
	}
	
	return !areObjectsEqual(sanitizeFormData($("#profileForm").serializeArray()), defaultFormValue);
}

function initProfileSaveButton() {
  	$("#profileForm").change(formChange).submit(function() {
		submitted = true;
	});
  	$("#profileForm input").change(formChange);
  	defaultFormValue = sanitizeFormData($("#profileForm").serializeArray());

	function formChange() {
		$("#submitButton").prop("disabled", !isProfileChanged());
	}
}
