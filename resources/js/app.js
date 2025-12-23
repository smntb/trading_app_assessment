
// 2️⃣ Tailwind CSS AFTER Vuetify
import '../css/app.css';

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// --- Echo setup (unchanged) ---
window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
    },
})

const app = createApp(Dashboard)
app.mount('#app')
