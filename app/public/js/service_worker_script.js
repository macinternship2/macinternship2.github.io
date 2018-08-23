var url= window.location.href;
var pos = url.search('using_pwa');

function initServiceWorker(){
	if (pos+1 && url[pos+8] && 'serviceWorker' in navigator) {
		navigator.serviceWorker
		.register('service-worker.js')
		.then(function(registration) {
			if (!navigator.serviceWorker.controller){
				location.reload();
			}
			sendNewLocation();
		})
		.catch(function(err) {
			console.log("Service Worker Failed to Register", err);
		});
	}
}

function initListener(){
	if ('serviceWorker' in navigator){
		navigator.serviceWorker.addEventListener('message', function(e) {
			if(e.data.type=='redirect') {
				location.replace(e.data.url);
			} else if(e.data.type='refresh') {
				sendNewLocation();
			}
		});
	}
}


initServiceWorker();
initListener();

function sendNewLocation() {
	getCurrentGeolocation().then( function(latlng){
		lng=latlng.lng();
		lat=latlng.lat();
		var urlGet = '/api/location/nearby/'+lng+'/'+lat;
		var ajaxReq = $.ajax({
			type: 'get',
			url: urlGet,
			dataType: 'json',
			success: function(data, status, xhr){
				navigator.serviceWorker.controller.postMessage(data);
			},
			error: function(xhr, reason, ex){
				var sampleData={
					name: '404',
					id:'0'
				};
				navigator.serviceWorker.controller.postMessage(sampleData);
				console.log("No nearby locations found at "+urlGet);
			}
		});
	});
};