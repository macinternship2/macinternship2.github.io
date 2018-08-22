var reviewRedirect, title, options;
title = 'We Need Your Help!';
options = {
	body: '',
	icon: '/images/logo-192x192.png',
	sound: '/sounds/alert.wav',
	actions: [
			{
			action: 'review',
			title: 'Yes!'
			}
		],
	tag: 'help-notification',
	vibrate: [500,110,500,110,450]
	};
function helpNotification(name,id) {
	if(name=='404')
		options.body='';
	else{
		reviewRedirect='location/rating/'+id+'/5';
		options.body='Would you like to add a review to '+name+'?';
	}
	//self.registration.showNotification(title, options);
};

function sendNotification() {
	//check to send here
	sendMessage({type: 'refresh'});
	if(options.body!=='')
		self.registration.showNotification(title,options);
}

function sendMessageToClient(client, msg){
    return new Promise(function(resolve, reject){
        var msg_chan = new MessageChannel();

        msg_chan.port1.onmessage = function(event){
            if(event.data.error){
                reject(event.data.error);
            }else{
                resolve(event.data);
            }
        };

        client.postMessage(msg, [msg_chan.port2]);
    });
}

function sendMessage(content){
	clients.matchAll().then(clients => {
		clients.forEach(client => {
			sendMessageToClient(client, content);
		})
	})
}

self.addEventListener('install', function(e) {   
	e.waitUntil(self.skipWaiting());
    console.log('Service Worker Installed')
})  

self.addEventListener('activate', function(e) {
	e.waitUntil(self.clients.claim())
    console.log('Service Worker Activated');
})

self.addEventListener('fetch',function(e) {
    //console.log('fetching '+e);
})

self.addEventListener('notificationclick', function(e) {
    switch(e.action) {
    	case 'review':
    		sendMessage({
    			type: 'redirect',
    			url: reviewRedirect
    		});
    		break;
    }
    e.notification.close();
})

self.addEventListener('message', function(e) {
	helpNotification(e.data.name,e.data.id);
})

//send first notification
setTimeout(sendNotification,5000);
//Starting the push notification clock
setTimeout(setInterval(sendNotification,3600000),5000);