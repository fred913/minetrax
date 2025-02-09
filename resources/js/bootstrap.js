import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
import { VITE_PUSHER_APP_CLUSTER, VITE_PUSHER_APP_KEY, VITE_PUSHER_HOST, VITE_PUSHER_PORT, VITE_PUSHER_SCHEME } from './constants';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: VITE_PUSHER_APP_KEY,
    wsHost: VITE_PUSHER_HOST ?? `ws-${VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: VITE_PUSHER_PORT ?? 80,
    wssPort: VITE_PUSHER_PORT ?? 443,
    forceTLS: (VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    cluster: VITE_PUSHER_APP_CLUSTER,
});
