/**
 * Notification Polling Composable
 * 
 * Handles notification updates via periodic API polling.
 * Replaces the previous WebSocket-based real-time implementation
 * for shared hosting compatibility.
 */

import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useAuthStore } from '../stores/auth'

/**
 * Shared notification state
 */
const notifications = ref([])
const unreadCount = ref(0)

/**
 * Initialize notification polling
 * 
 * @param {Function} onNotificationReceived - Callback when a new notification is detected
 * @param {Function} onUnreadCountUpdate - Callback when unread count changes
 * @returns {Object} Composable methods and state
 */
export function useRealtimeNotifications(
    onNotificationReceived = null,
    onUnreadCountUpdate = null
) {
    const authStore = useAuthStore()

    // Polling is always "connected" when authenticated
    const isConnected = ref(false)
    const connectionError = ref(null)

    /**
     * Update unread count
     */
    const updateUnreadCount = (count) => {
        if (unreadCount.value !== count) {
            unreadCount.value = count
            if (onUnreadCountUpdate && typeof onUnreadCountUpdate === 'function') {
                onUnreadCountUpdate(count)
            }
        }
    }

    /**
     * Reset tracking (no-op, kept for API compatibility)
     */
    const resetRealtimeTracking = () => {
        // No-op: polling doesn't need tracking resets
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
            isConnected.value = isAuthenticated
            if (!isAuthenticated) {
                notifications.value = []
                unreadCount.value = 0
            }
        },
        { immediate: true }
    )

    onMounted(() => {
        isConnected.value = authStore.isAuthenticated
    })

    return {
        notifications,
        unreadCount,
        isConnected,
        connectionError,
        connect: () => { isConnected.value = authStore.isAuthenticated },
        disconnect: () => { isConnected.value = false },
        updateUnreadCount,
        markAsRead,
        removeNotification,
        resetRealtimeTracking,
    }
}
