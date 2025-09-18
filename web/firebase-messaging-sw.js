importScripts('https://www.gstatic.com/firebasejs/9.2.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.2.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker
firebase.initializeApp({
 apiKey: 'AIzaSyCQ45FatclTAFkf8m8J-LgHFStyiaPuP4c',
    appId: '1:117453057789:web:ac9bd3d9594a8992a71894',
    messagingSenderId: '117453057789',
    projectId: 'lloyalty-application',
    authDomain: 'lloyalty-application.firebaseapp.com',
    storageBucket: 'lloyalty-application.firebasestorage.app',
    measurementId: 'G-54VC83N2PY',
});

// Retrieve an instance of Firebase Messaging so that it can handle background messages
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/icons/Icon-192.png' // Adjust path as needed
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});