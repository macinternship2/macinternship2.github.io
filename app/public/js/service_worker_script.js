if ('serviceWorker' in navigator) {
	navigator.serviceWorker
	.register('./service-worker.js', { scope: './'})
	.then((registration)=> {
      firebase.messaging().useServiceWorker(registration);
    })
	.catch(function(err) {
		console.log("Service Worker Failed to Register", err);
	})
}