import './bootstrap';
import QRCode from 'qrcode';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.QRCode = QRCode;
window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT || 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        },
    });
}
