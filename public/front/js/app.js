import './bootstrap';
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";

const firebaseConfig = {
    apiKey: "AIzaSyC6jHK7MpjBj412-b7wkCBQbltsVQxzBo8",
    authDomain: "truelysell-570e9.firebaseapp.com",
    projectId: "truelysell-570e9",
    storageBucket: "truelysell-570e9.firebasestorage.app",
    messagingSenderId: "4483490363",
    appId: "1:4483490363:web:6de7fab4a46d839710f21e",
    measurementId: "G-4XX1JK7KS2"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);
const analytics = getAnalytics(app);

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then((registration) => {
            console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch((err) => {
            console.log('Service Worker registration failed:', err);
        });
}

// Function to request FCM token permission and retrieve token
function requestFCMToken() {
    messaging.requestPermission()
        .then(() => messaging.getToken())
        .then((token) => {
            if (token) {
                saveTokenToServer(token); // Send token to your server
                console.log('FCM Token:', token);
            } else {
                console.log('No registration token available. Request permission to generate one.');
            }
        })
        .catch((error) => {
            console.error('Unable to get permission to notify.', error);
        });
}

// Listen for token refresh and handle it
messaging.onTokenRefresh(() => {
    messaging.getToken()
        .then((refreshedToken) => {
            saveTokenToServer(refreshedToken);
        })
        .catch((error) => {
            console.error('Unable to retrieve refreshed token ', error);
        });
});
function saveTokenToServer(token) {
    fetch('/communication/notification/savefcmtoken', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ token: token })
    });
}
