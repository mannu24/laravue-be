/**
 * Real-time Notifications Composable
 * 
 * This composable handles real-time notification updates via WebSocket.
 * It integrates with Laravel Echo to listen for broadcast events.
 */

import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

/**
 * Real-time notification state
 */
const notifications = ref([])
const unreadCount = ref(0)
const isConnected = ref(false)
const connectionError = ref(null)
let channel = null
let reconnectAttempts = 0
const MAX_RECONNECT_ATTEMPTS = 5
const RECONNECT_DELAY = 3000 // 3 seconds

/**
 * Initialize real-time notification listener
 * 
 * @param {Function} onNotificationReceived - Callback when notification is received
 * @param {Function} onUnreadCountUpdate - Callback when unread count updates
 * @returns {Object} Composable methods and state
 */
export function useRealtimeNotifications(
    onNotificationReceived = null,
    onUnreadCountUpdate = null
) {
    const authStore = useAuthStore()
    const router = useRouter()

    /**
     * Connect to notification channel
     */
    const connect = () => {
        if (!window.Echo) {
            console.warn('[RealtimeNotifications] Echo not initialized')
            connectionError.value = 'Broadcasting not configured'
            return false
        }

        if (!authStore.isAuthenticated || !authStore.user?.id) {
            console.warn('[RealtimeNotifications] User not authenticated')
            return false
        }

        try {
            const userId = authStore.user.id

            // Disconnect existing channel if any
            disconnect()

            // Subscribe to user's private notification channel
            channel = window.Echo.private(`user.${userId}`)

            // Listen for notification.created event (broadcast as 'notification.created')
            channel.listen('.notification.created', (notification) => {
                handleNotificationReceived(notification)
            })

            // Listen for connection events
            channel.subscribed(() => {
                isConnected.value = true
                connectionError.value = null
                reconnectAttempts = 0
                console.log('[RealtimeNotifications] Connected to notification channel')
            })

            channel.error((error) => {
                console.error('[RealtimeNotifications] Channel error:', error)
                connectionError.value = error.message || 'Connection error'
                isConnected.value = false
                attemptReconnect()
            })

            return true
        } catch (error) {
            console.error('[RealtimeNotifications] Connection failed:', error)
            connectionError.value = error.message || 'Failed to connect'
            isConnected.value = false
            return false
        }
    }

    /**
     * Disconnect from notification channel
     */
    const disconnect = () => {
        if (channel) {
            try {
                window.Echo.leave(`user.${authStore.user?.id}`)
                channel = null
                isConnected.value = false
                console.log('[RealtimeNotifications] Disconnected from notification channel')
            } catch (error) {
                console.error('[RealtimeNotifications] Disconnect error:', error)
            }
        }
    }

    /**
     * Handle received notification
     */
    const handleNotificationReceived = (notification) => {
        try {
            // Add notification to the beginning of the list
            notifications.value.unshift(notification)

            // Update unread count if notification is unread
            if (!notification.read) {
                unreadCount.value = (unreadCount.value || 0) + 1
            }

            // Call custom callback if provided
            if (onNotificationReceived && typeof onNotificationReceived === 'function') {
                onNotificationReceived(notification)
            }

            // Show browser notification if permission granted
            if (Notification.permission === 'granted') {
                showBrowserNotification(notification)
            }

            console.log('[RealtimeNotifications] Notification received:', notification)
        } catch (error) {
            console.error('[RealtimeNotifications] Error handling notification:', error)
        }
    }

    /**
     * Show browser notification
     */
    const showBrowserNotification = (notification) => {
        try {
            const browserNotification = new Notification(notification.title, {
                body: notification.message,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: `notification-${notification.id}`,
                data: notification.data,
                requireInteraction: false,
            })

            // Handle notification click
            browserNotification.onclick = () => {
                browserNotification.close()
                if (notification.data?.url) {
                    router.push(notification.data.url)
                }
            }

            // Auto-close after 5 seconds
            setTimeout(() => {
                browserNotification.close()
            }, 5000)
        } catch (error) {
            console.error('[RealtimeNotifications] Error showing browser notification:', error)
        }
    }

    /**
     * Attempt to reconnect with exponential backoff
     */
    const attemptReconnect = () => {
        if (reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
            console.error('[RealtimeNotifications] Max reconnection attempts reached')
            connectionError.value = 'Failed to reconnect. Please refresh the page.'
            return
        }

        reconnectAttempts++
        const delay = RECONNECT_DELAY * reconnectAttempts

        console.log(`[RealtimeNotifications] Attempting to reconnect (${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS}) in ${delay}ms`)

        setTimeout(() => {
            if (authStore.isAuthenticated) {
                connect()
            }
        }, delay)
    }

    /**
     * Update unread count
     */
    const updateUnreadCount = (count) => {
        unreadCount.value = count
        if (onUnreadCountUpdate && typeof onUnreadCountUpdate === 'function') {
            onUnreadCountUpdate(count)
        }
    }

    /**
     * Mark notification as read (optimistic update)
     */
    const markAsRead = (notificationId) => {
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.read) {
            notification.read = true
            notification.read_at = new Date().toISOString()
            if (unreadCount.value > 0) {
                unreadCount.value--
            }
        }
    }

    /**
     * Remove notification from list
     */
    const removeNotification = (notificationId) => {
        const index = notifications.value.findIndex(n => n.id === notificationId)
        if (index !== -1) {
            const notification = notifications.value[index]
            notifications.value.splice(index, 1)
            if (!notification.read && unreadCount.value > 0) {
                unreadCount.value--
            }
        }
    }

    // Watch for authentication changes
    watch(
        () => authStore.isAuthenticated,
        (isAuthenticated) => {
            if (isAuthenticated) {
                connect()
            } else {
                disconnect()
                notifications.value = []
                unreadCount.value = 0
            }
        },
        { immediate: true }
    )

    // Connect on mount
    onMounted(() => {
        if (authStore.isAuthenticated) {
            connect()
        }
    })

    // Disconnect on unmount
    onBeforeUnmount(() => {
        disconnect()
    })

    return {
        notifications,
        unreadCount,
        isConnected,
        connectionError,
        connect,
        disconnect,
        updateUnreadCount,
        markAsRead,
        removeNotification,
    }
}

