import './bootstrap';
import './services/api';

// Import Alpine.js for interactive components
import Alpine from 'alpinejs';

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();


// auto load auth token
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Global app utilities
window.app = {
    // Toast notification helper
    toast: (message, type = 'success') => {
        // You can integrate with toast libraries like Toastify or custom implementation
        console.log(`[${type.toUpperCase()}] ${message}`);
    },
    
    // Confirm dialog helper
    confirm: (message) => {
        return window.confirm(message); 
    },
    

    //helper buat cek auth
    isAuthenticated: () => {
        return !!localStorage.getItem('auth_token');
    },

    //helper buat logout
    logout: () => {
        localStorage.removeItem('auth_token');
        delete window.axios.defaults.headers.common['Authorization'];
        window.location.href = '/login';

    }
};
