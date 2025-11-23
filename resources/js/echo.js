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
    
    // Don't initialize Echo if no token is available (prevents 302 redirects)
    if (!token) {
        console.warn('[Echo] No auth token available, skipping initialization');
        return null;
    }
    
    let config = null;
    if (broadcaster === 'reverb') {
        const scheme = (import.meta.env.VITE_REVERB_SCHEME || 'http').toLowerCase()
        const useTLS = scheme === 'https'
        const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? (useTLS ? 443 : 8080))

        // Reverb configuration (self-hosted WebSocket server)
        config = {
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: useTLS ? null : reverbPort,
            wssPort: useTLS ? reverbPort : null,
            forceTLS: useTLS,
            encrypted: useTLS,
            enabledTransports: useTLS ? ['ws', 'wss'] : ['ws'],
            disableStats: false,
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
    } else if (broadcaster === 'pusher') {
        // Pusher configuration (managed service)
        config = {
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
    }
    return new Echo(config);
};

// Initialize and export Echo instance
let echo = null;

/**
 * Setup Echo connection event handlers
 */
const setupEchoHandlers = (echoInstance) => {
    if (echoInstance?.connector?.pusher?.connection) {
        echoInstance.connector.pusher.connection.bind('connected', () => {
            console.log('[Echo] Connected to broadcasting server');
        });
        
        echoInstance.connector.pusher.connection.bind('disconnected', () => {
            console.warn('[Echo] Disconnected from broadcasting server');
        });
        
        echoInstance.connector.pusher.connection.bind('error', (error) => {
            console.error('[Echo] Connection error:', error);
        });
        
        echoInstance.connector.pusher.connection.bind('state_change', (states) => {
            console.log('[Echo] Connection state changed:', states);
        });
    }
};

/**
 * Disconnect and cleanup existing Echo instance
 */
const disconnectEcho = () => {
    if (echo) {
        try {
            // Leave all channels
            if (echo.connector?.pusher) {
                echo.connector.pusher.allChannels().forEach(channel => {
                    echo.leave(channel.name);
                });
            }
            // Disconnect
            echo.disconnect();
        } catch (error) {
            console.warn('[Echo] Error disconnecting:', error);
        }
        echo = null;
        window.Echo = null;
    }
};

/**
 * Reinitialize Echo with updated auth token
 * This should be called when user logs in or token changes
 */
export const reinitializeEcho = () => {
    // Disconnect existing connection first
    disconnectEcho();
    
    // Only reinitialize if user is authenticated
    const token = getAuthToken();
    if (!token) {
        console.log('[Echo] No auth token, skipping reinitialization');
        return;
    }
    
    // Reinitialize with new token
    try {
        echo = initializeEcho();
        
        if (echo) {
            window.Echo = echo;
            setupEchoHandlers(echo);
            console.log('[Echo] Reinitialized with new auth token');
        } else {
            console.warn('[Echo] Broadcasting not configured. Set VITE_BROADCAST_DRIVER in .env');
        }
    } catch (error) {
        console.error('[Echo] Failed to reinitialize:', error);
    }
};

// Initial Echo setup
try {
    echo = initializeEcho();
    
    if (echo) {
        window.Echo = echo;
        setupEchoHandlers(echo);
    } else {
        console.warn('[Echo] Broadcasting not configured. Set VITE_BROADCAST_DRIVER in .env');
    }
} catch (error) {
    console.error('[Echo] Failed to initialize:', error);
}

export default echo;
