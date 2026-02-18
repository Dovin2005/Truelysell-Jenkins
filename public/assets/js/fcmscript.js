  // Your Firebase configuration
  const firebaseConfig = {
    apiKey: "AIzaSyC6jHK7MpjBj412-b7wkCBQbltsVQxzBo8",
    authDomain: "truelysell-570e9.firebaseapp.com",
    projectId: "truelysell-570e9",
    storageBucket: "truelysell-570e9.firebasestorage.app",
    messagingSenderId: "4483490363",
    appId: "1:4483490363:web:6de7fab4a46d839710f21e",
    measurementId: "G-4XX1JK7KS2"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Initialize Firebase Messaging
const messaging = firebase.messaging();


    // Request Notification Permission and Get Token
    function requestPermissionAndGetToken() {
        if (Notification.permission === 'default') {
            // Request permission using the Notification API
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    // Permission granted, get the FCM token
                    getFCMToken();
                } else {
                }
            });
        } else if (Notification.permission === 'granted') {
            // Permission already granted, get the token
            getFCMToken();
        } else {
        }
    }

    // Function to retrieve FCM token
    function getFCMToken() {
        messaging.getToken({ vapidKey: "BLSNScsmncBHHuSm8bYiYsBS3OeQSST7UMNWOPNDmIMsbt55q1VPJ1qGkRYeRxdQLtK8fv04Ndu-fbvS4DPYXUI" })
            .then((currentToken) => {
                if (currentToken) {
                    saveTokenToServer(currentToken); // Save token to your backend
                } else {
                }
            })
            .catch((error) => {

            });
    }

    // Save the token to the server
    function saveTokenToServer(token) {
        const path = window.location.pathname;
        fetch('/api/notification/savefcmtoken', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ token: token,type:path })
        })
        .then(response => response.json())
        .then(data => console.log('Token saved:', data))
        .catch(error => console.error('Error saving token:', error));
    }
     // Optional: Handle incoming messages in the foreground
     firebase.messaging().onMessage((payload) => {
       // Extract notification and data
        const { title, body } = payload.notification || {};
        const { from_user, from_user_id, to_user, to_user_id, auth_user_id,profileimage, timestamp } = payload.data || {};
        // Find the chat window
        const chatWindow = document.getElementById("chat-window");
        if (!chatWindow) {
            console.warn(`Chat window for user ID ${to_user_id} not found`);
            return;
        }

        // Determine message alignment
        const userId = auth_user_id;
        const alignment = from_user_id === userId ? 'incoming' : 'outgoing';

        // Create and append the chat message
        const html = `
            <div class="message ${alignment}">
                <p>${body}</p>
             </div>
        `;
        chatWindow.scrollTop = chatWindow.scrollHeight;
        // Update the user list
        const userlist = $('.user-list');
        const existingUser = userlist.find(`[data-user-id="${to_user_id}"]`);
        const defaultImagePath = 'asset/images/default-profile.png';
        const profileImagePath = profileimage && profileimage !== 'N/A'
                            ? `/storage/profile/${profileimage}`
                            : defaultImagePath;
        if(from_user_id!=userId){
            if (existingUser.length === 0) {
                var userhtml=`<li class="user-list-item" data-user-id="${to_user_id}" data-user="${to_user}" data-authuserid="${userId}">
                        <a href="javascript:void(0);" class="p-2 border rounded d-block mb-2 userlist">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg avatar-online me-2 flex-shrink-0">`;
                                    userhtml +=`<img src="${profileImagePath}" class="rounded-circle" alt="image">`;
                               userhtml +=`</div>
                                <div class="flex-grow-1 overflow-hidden me-2">
                                    <h6 class="mb-1 text-truncate">${to_user}</h6>
                                    <p class="text-truncate msgbody msgbody${to_user_id}" data-user-id="${to_user_id}">${body}</p>
                                </div>
                            </div>
                        </a>
                    </li>`;
            }
        }else{
            const existingmsg = $('.msgbody'+to_user_id).find();
            if (existingmsg.length === 0) {
            }
        }
    });

// // Register Service Worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker
    .register('/firebase-messaging-sw.js', { scope: '/' })
        .then((registration) => {


        })
        .catch((error) => {

        });
} else {

}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (event) => {
        const { data, notification } = event.data;

        if (notification) {
            // Extract notification details
            const { title, body } = notification;

            // Extract additional data
            const {
                from_user,
                from_user_id,
                to_user,
                to_user_id,
                auth_user_id,
                profileimage,
                timestamp,
            } = data || {};
            // Update the chat window
            const chatWindow = document.getElementById('chat-window');
            const userId = auth_user_id;
            if (chatWindow) {
                const alignment = from_user_id === userId ? 'incoming' : 'outgoing';

                const html = `
                    <div class="message ${alignment}">
                        <p>${body}</p>
                    </div>
                `;
                $('#chat-window').append(html);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            } else {

            }
            chatWindow.scrollTop = chatWindow.scrollHeight;
        // Update the user list
        const userlist = $('.user-list');
        const existingUser = userlist.find(`[data-user-id="${to_user_id}"]`);
        const defaultImagePath = 'asset/images/default-profile.png';
        const profileImagePath = profileimage && profileimage !== 'N/A'
                            ? `/storage/profile/${profileimage}`
                            : defaultImagePath;
        if(from_user_id!=userId){
            if (existingUser.length === 0) {
                var userhtml=`<li class="user-list-item" data-user-id="${to_user_id}" data-user="${to_user}" data-authuserid="${userId}" data-profileimage="${profileimage}">
                        <a href="javascript:void(0);" class="p-2 border rounded d-block mb-2 userlist">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg avatar-online me-2 flex-shrink-0">`;
                                    userhtml +=`<img src="${profileImagePath}" class="rounded-circle" alt="image">`;
                               userhtml +=`</div>
                                <div class="flex-grow-1 overflow-hidden me-2">
                                    <h6 class="mb-1 text-truncate">${to_user}</h6>
                                    <p class="text-truncate msgbody msgbody${to_user_id}" data-user-id="${to_user_id}">${body}</p>
                                </div>
                            </div>
                        </a>
                    </li>`;
                    userlist.append(userhtml);
            }
        }else{
            const existingmsg = $('.msgbody'+to_user_id).find();
            if (existingmsg.length === 0) {
                $('.msgbody'+to_user_id).text(body);
            }
        }
        } else {

        }
    });
} else {

}


