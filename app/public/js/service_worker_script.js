var url= window.location.href;
var pos = url.search('using_pwa');

if (pos+1 && url[pos+8] && 'serviceWorker' in navigator) {
	navigator.serviceWorker
	.register('./service-worker.js', { scope: './'})
	.then((registration)=> {
      firebase.messaging().useServiceWorker(registration);
    })
	.catch(function(err) {
		console.log("Service Worker Failed to Register", err);
	})
}