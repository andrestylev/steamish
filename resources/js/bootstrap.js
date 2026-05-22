import axios from 'axios';
import * as bootstrap from 'bootstrap';

// Bootstrap JS for navbar toggle, modals, etc.
window.bootstrap = bootstrap;

// Axios for HTTP requests
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
