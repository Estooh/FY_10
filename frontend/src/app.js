// src/main.js

import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Make sure this path is correct and matches your project structure
// main.js
// Import the global CSS file
// import './assets/global.css';
const app = createApp(App);

// Set the Vue app instance globally to window.$vueApp
window.$vueApp = app;

app.use(router).mount('#app');
