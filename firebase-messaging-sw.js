importScripts('https://www.gstatic.com/firebasejs/9.17.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.17.2/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyC6jHK7MpjBj412-b7wkCBQbltsVQxzBo8",
    authDomain: "truelysell-570e9.firebaseapp.com",
    projectId: "truelysell-570e9",
    storageBucket: "truelysell-570e9.firebaseapp.com",
    messagingSenderId: "4483490363",
    appId: "1:4483490363:web:6de7fab4a46d839710f21e",
    measurementId: "G-4XX1JK7KS2"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification?.title || 'Default Title';
    const notificationOptions = {
        body: payload.notification?.body || 'Default message body.',
        icon: payload.notification?.icon || '/path-to-default-icon.png',
        data: payload.data || {}, // Include custom data for notification click handling
    };

    // Show notification
    self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('push', (event) => {
    const payload = event.data.json();

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/default-icon.png',
    };

    // Show the notification
    event.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));

    // Post the message to the main thread
    const messageData = {
        data: payload.data,
        notification: payload.notification,
    };
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
        clients.forEach((client) => {
            client.postMessage(messageData); // Send the data to the main thread
        });
    });
});


