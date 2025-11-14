/**
 * Laravel Echo Configuration
 * 
 * This file sets up Laravel Echo for real-time broadcasting.
 * It supports both Reverb (self-hosted) and Pusher (managed service).
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher;

/**
 * Get authentication token from auth store or localStorage
 */
const getAuthToken = () => {
    // Try to get from auth store if available
    if (window.authStore?.token) {
        return window.authStore.token;
    }
    // Fallback to localStorage
    const authData = localStorage.getItem('auth');
    if (authData) {
        try {
            const parsed = JSON.parse(authData);
            return parsed?.token || null;
        } catch (e) {
            return null;
        }
    }
    return null;
};

/**
 * Initialize Laravel Echo instance
 * 
 * Configuration is read from environment variables:
 * - VITE_BROADCAST_DRIVER: 'reverb' or 'pusher'
 * - VITE_REVERB_APP_KEY: Reverb application key
 * - VITE_REVERB_HOST: Reverb server host
 * - VITE_REVERB_PORT: Reverb server port
 * - VITE_REVERB_SCHEME: Reverb connection scheme (http/https)
 * - VITE_PUSHER_APP_KEY: Pusher application key (alternative)
 * - VITE_PUSHER_APP_CLUSTER: Pusher cluster (alternative)
 */
const initializeEcho = () => {
    const broadcaster = import.meta.env.VITE_BROADCAST_DRIVER || 'reverb';
    const token = getAuthToken();
    
    if (broadcaster === 'reverb') {
        // Reverb configuration (self-hosted WebSocket server)
        const config = {
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
        };

        // Add auth headers if token is available
        if (token) {
            config.auth = {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            };
        }

        return new Echo(config);
    } else if (broadcaster === 'pusher') {
        // Pusher configuration (managed service)
        const config = {
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            forceTLS: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
        };

        // Add auth headers if token is available
        if (token) {
            config.auth = {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            };
        }

        return new Echo(config);
    }
    
    return null;
};

// Initialize and export Echo instance
let echo = null;

try {
    echo = initializeEcho();
    
    if (echo) {
        window.Echo = echo;
        
        // Handle connection events
        if (echo.connector?.pusher?.connection) {
            echo.connector.pusher.connection.bind('connected', () => {
                console.log('[Echo] Connected to broadcasting server');
            });
            
            echo.connector.pusher.connection.bind('disconnected', () => {
                console.warn('[Echo] Disconnected from broadcasting server');
            });
            
            echo.connector.pusher.connection.bind('error', (error) => {
                console.error('[Echo] Connection error:', error);
            });
            
            echo.connector.pusher.connection.bind('state_change', (states) => {
                console.log('[Echo] Connection state changed:', states);
            });
        }
    } else {
        console.warn('[Echo] Broadcasting not configured. Set VITE_BROADCAST_DRIVER in .env');
    }
} catch (error) {
    console.error('[Echo] Failed to initialize:', error);
}

export default echo;
