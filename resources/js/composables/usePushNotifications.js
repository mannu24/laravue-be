import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const isSupported = ref(false)
const isSubscribed = ref(false)
const subscription = ref(null)
const registration = ref(null)
const vapidPublicKey = ref(null)

// Check if push notifications are supported
const checkSupport = () => {
    if (typeof window === 'undefined') return false
    
    return (
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    )
}

// Convert VAPID key from URL-safe base64 to Uint8Array
const urlBase64ToUint8Array = (base64String) => {
    // Remove any whitespace
    base64String = base64String.trim()
    
    // Add padding if needed
    const padding = '='.repeat((4 - base64String.length % 4) % 4)
    
    // Convert URL-safe base64 to standard base64
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/')

    try {
        const rawData = window.atob(base64)
        const outputArray = new Uint8Array(rawData.length)

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i)
        }
        
        // Verify the key length (should be 65 bytes for P-256 public key)
        if (outputArray.length !== 65) {
            console.warn(`VAPID public key length is ${outputArray.length}, expected 65 bytes`)
        }
        
        return outputArray
    } catch (error) {
        console.error('Error converting VAPID key:', error)
        throw new Error('Invalid VAPID public key format')
    }
}

// Register service worker
const registerServiceWorker = async () => {
    if (!checkSupport()) {
        console.warn('Push notifications are not supported in this browser')
        return null
    }

    try {
        const reg = await navigator.serviceWorker.register('/service-worker.js', {
            scope: '/'
        })
        
        registration.value = reg
        console.log('Service Worker registered:', reg.scope)
        return reg
    } catch (error) {
        console.error('Service Worker registration failed:', error)
        return null
    }
}

// Request notification permission
const requestPermission = async () => {
    if (!('Notification' in window)) {
        throw new Error('Notifications are not supported')
    }

    const permission = await Notification.requestPermission()
    
    if (permission !== 'granted') {
        throw new Error('Notification permission denied')
    }

    return permission
}

// Subscribe to push notifications
const subscribe = async (authStore) => {
    if (!checkSupport()) {
        throw new Error('Push notifications are not supported')
    }

    // Register service worker if not already registered
    if (!registration.value) {
        await registerServiceWorker()
    }

    if (!registration.value) {
        throw new Error('Service Worker registration failed')
    }

    // Request permission
    await requestPermission()

    // Get VAPID public key from backend
    if (!vapidPublicKey.value) {
        try {
            const response = await axios.get('/api/v1/push-subscriptions/vapid-key')
            if (response.data.status === 'success' && response.data.data.public_key) {
                vapidPublicKey.value = response.data.data.public_key
            } else {
                throw new Error('Failed to get VAPID key: Invalid response')
            }
        } catch (error) {
            console.error('Error fetching VAPID key:', error)
            throw new Error('Failed to get VAPID public key: ' + (error.message || 'Network error'))
        }
    }

    // Convert and validate the key
    let applicationServerKey
    try {
        applicationServerKey = urlBase64ToUint8Array(vapidPublicKey.value)
        console.log('VAPID key converted successfully, length:', applicationServerKey.length)
    } catch (error) {
        console.error('Error converting VAPID key:', error)
        throw new Error('Invalid VAPID public key format: ' + error.message)
    }

    // Subscribe to push manager
    const pushSubscription = await registration.value.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })

    subscription.value = pushSubscription

    // Send subscription to backend
    const subscriptionData = {
        endpoint: pushSubscription.endpoint,
        keys: {
            p256dh: btoa(String.fromCharCode(...new Uint8Array(pushSubscription.getKey('p256dh')))),
            auth: btoa(String.fromCharCode(...new Uint8Array(pushSubscription.getKey('auth')))),
        },
        content_encoding: pushSubscription.options?.applicationServerKey ? 'aesgcm' : 'aes128gcm',
    }

    try {
        const response = await axios.post('/api/v1/push-subscriptions', subscriptionData, authStore.config)
        if (response.data.status === 'success') {
            isSubscribed.value = true
            return true
        }
    } catch (error) {
        console.error('Error subscribing to push notifications:', error)
        throw error
    }

    return false
}

// Unsubscribe from push notifications
const unsubscribe = async (authStore) => {
    if (!subscription.value) {
        return
    }

    try {
        // Remove from backend
        await axios.delete('/api/v1/push-subscriptions', {
            data: { endpoint: subscription.value.endpoint },
            ...authStore.config
        })

        // Unsubscribe from push manager
        const unsubscribed = await subscription.value.unsubscribe()
        if (unsubscribed) {
            subscription.value = null
            isSubscribed.value = false
            return true
        }
    } catch (error) {
        console.error('Error unsubscribing from push notifications:', error)
        throw error
    }

    return false
}

// Check current subscription status
const checkSubscription = async (authStore) => {
    if (!checkSupport() || !registration.value) {
        return false
    }

    try {
        const pushSubscription = await registration.value.pushManager.getSubscription()
        if (pushSubscription) {
            subscription.value = pushSubscription
            isSubscribed.value = true
            
            // Verify subscription exists on backend
            try {
                const response = await axios.get('/api/v1/push-subscriptions', authStore.config)
                if (response.data.status === 'success' && response.data.data.subscriptions.length > 0) {
                    return true
                }
            } catch (error) {
                // If backend doesn't have it, unsubscribe locally
                await pushSubscription.unsubscribe()
                subscription.value = null
                isSubscribed.value = false
            }
        } else {
            isSubscribed.value = false
        }
    } catch (error) {
        console.error('Error checking subscription:', error)
        isSubscribed.value = false
    }

    return isSubscribed.value
}

// Initialize push notifications
const initialize = async (authStore) => {
    if (!checkSupport()) {
        isSupported.value = false
        return false
    }

    isSupported.value = true

    // Register service worker
    await registerServiceWorker()

    // Check existing subscription
    if (authStore.isAuthenticated) {
        await checkSubscription(authStore)
    }

    return true
}

export function usePushNotifications() {
    onMounted(() => {
        const authStore = useAuthStore()
        if (authStore.isAuthenticated) {
            initialize(authStore)
        }
    })

    return {
        isSupported,
        isSubscribed,
        subscription,
        subscribe: (authStore) => subscribe(authStore),
        unsubscribe: (authStore) => unsubscribe(authStore),
        checkSubscription: (authStore) => checkSubscription(authStore),
        initialize: (authStore) => initialize(authStore),
    }
}

