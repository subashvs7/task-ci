// Import and configure the Firebase SDK
// These scripts are compat versions because service workers require importScripts and compat is easiest to load synchronously.
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

const firebaseConfig = {
  apiKey: "AIzaSyCkURFLiJOsUgvgxv8xfB5lxwr2uq-Igec",
  authDomain: "zazu-task.firebaseapp.com",
  projectId: "zazu-task",
  storageBucket: "zazu-task.firebasestorage.app",
  messagingSenderId: "105767000196",
  appId: "1:105767000196:web:d34d6ddb1b5c28f156cad1",
  measurementId: "G-Y35F4Z0MRQ"
};

// Initialize Firebase app in the service worker
firebase.initializeApp(firebaseConfig);

// Retrieve firebase messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Background message received: ', payload);
  
  const notificationTitle = payload.notification ? payload.notification.title : 'Task Reminder';
  const notificationOptions = {
    body: payload.notification ? payload.notification.body : 'You have a scheduled task starting or ending soon.',
    icon: payload.notification && payload.notification.image ? payload.notification.image : '/favicon.ico',
    badge: '/favicon.ico',
    data: payload.data
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
