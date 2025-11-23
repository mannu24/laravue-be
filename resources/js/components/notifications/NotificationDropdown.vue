<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useThemeStore } from '../../stores/theme'
import { useRealtimeNotifications } from '../../composables/useRealtimeNotifications'
import { Bell, Check, X, Wifi, WifiOff } from 'lucide-vue-next'
import { Button } from '../ui/button'
import axios from 'axios'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const isOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
const dropdownRef = ref(null)

// Real-time notifications composable
const {
    notifications: realtimeNotifications,
    unreadCount: realtimeUnreadCount,
    isConnected: isRealtimeConnected,
    connectionError: realtimeError,
    markAsRead: markRealtimeAsRead,
    updateUnreadCount: updateRealtimeUnreadCount,
    resetRealtimeTracking,
} = useRealtimeNotifications(
    // onNotificationReceived callback
    (notification) => {
        // Add to local notifications if not already present
        const exists = notifications.value.some(n => n.id === notification.id)
        if (!exists) {
            notifications.value.unshift(notification)
            // Keep only latest 6 notifications in dropdown
            if (notifications.value.length > 6) {
                notifications.value = notifications.value.slice(0, 6)
            }
            
            // If notification is unread, increment count
            // But we'll sync with API on next fetch to ensure accuracy
            if (!notification.read) {
                // Don't increment here - let the realtime composable handle it
                // The count will be synced with API when fetchNotifications is called
            }
        }
    },
    // onUnreadCountUpdate callback
    (count) => {
        // Only update if count is different to prevent unnecessary updates
        // The watcher will also handle this, but this ensures consistency
        if (unreadCount.value !== count) {
            unreadCount.value = count
        }
    }
)

// Fetch notifications from API
const fetchNotifications = async () => {
    if (!authStore.isAuthenticated) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/api/v1/notifications?per_page=6&unread_only=false', authStore.config)
        if (response.data.status === 'success') {
            const apiNotifications = response.data.data.notifications || []
            
            // Merge with real-time notifications, avoiding duplicates
            const existingIds = new Set(notifications.value.map(n => n.id))
            const newNotifications = apiNotifications.filter(n => !existingIds.has(n.id))
            
            notifications.value = [...newNotifications, ...notifications.value].slice(0, 6)
            
            // Use API unread count as source of truth (it's always accurate)
            // This ensures we don't double-count notifications that were already in the API response
            const apiUnreadCount = response.data.data.pagination?.unread_count || 0
            unreadCount.value = apiUnreadCount
            
            // Sync with real-time unread count to reset it to API value
            // This prevents double-counting when realtime notifications arrive after API fetch
            updateRealtimeUnreadCount(apiUnreadCount)
            
            // Reset realtime tracking to prevent double-counting
            // When API fetch happens, all notifications in API are considered "already counted"
            resetRealtimeTracking()
        }
    } catch (error) {
        console.error('Error fetching notifications:', error)
    } finally {
        isLoading.value = false
    }
}

// Fetch unread count only
const fetchUnreadCount = async () => {
    if (!authStore.isAuthenticated) return
    
    try {
        const response = await axios.get('/api/v1/notifications/unread-count', authStore.config)
        if (response.data.status === 'success') {
            const count = response.data.data.unread_count || 0
            unreadCount.value = count
            updateRealtimeUnreadCount(count)
        }
    } catch (error) {
        console.error('Error fetching unread count:', error)
    }
}

// Watch for real-time unread count changes
// Only update if the count actually changed to prevent unnecessary updates
watch(realtimeUnreadCount, (newCount) => {
    if (newCount !== null && newCount !== undefined && newCount !== unreadCount.value) {
        unreadCount.value = newCount
    }
}, { immediate: true })

// Mark notification as read
const markAsRead = async (notificationId, event) => {
    if (event) {
        event.stopPropagation()
    }
    
    // Optimistic update
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification && !notification.read) {
        notification.read = true
        notification.read_at = new Date().toISOString()
        if (unreadCount.value > 0) {
            unreadCount.value--
        }
        // Also update real-time state
        markRealtimeAsRead(notificationId)
    }
    
    try {
        await axios.post(`/api/v1/notifications/${notificationId}/read`, {}, authStore.config)
    } catch (error) {
        console.error('Error marking notification as read:', error)
        // Revert optimistic update on error
        if (notification) {
            notification.read = false
            notification.read_at = null
            unreadCount.value++
        }
    }
}

// Handle notification click
const handleNotificationClick = (notification) => {
    if (!notification.read) {
        markAsRead(notification.id, { stopPropagation: () => {} })
    }
    
    // Navigate based on notification type and data
    if (notification.data?.url) {
        router.push(notification.data.url)
        isOpen.value = false
    }
}

// Toggle dropdown
const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        fetchNotifications()
    }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

// View all notifications
const viewAll = () => {
    router.push('/notifications')
    isOpen.value = false
}

// Get notification icon based on type
const getNotificationIcon = (type) => {
    const icons = {
        'post_liked': '❤️',
        'post_commented': '💬',
        'comment_liked': '👍',
        'question_liked': '👍',
        'question_upvoted': '⬆️',
        'question_answered': '💡',
        'answer_upvoted': '⬆️',
        'answer_replied': '💬',
        'answer_accepted': '✅',
        'mentioned': '🔔',
        'followed': '👤',
    }
    return icons[type] || '🔔'
}

// Get notification color based on type
const getNotificationColor = (type) => {
    const colors = {
        'post_liked': themeStore.isDark ? 'bg-red-900/30 text-red-400' : 'bg-red-100 text-red-600',
        'post_commented': themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600',
        'comment_liked': themeStore.isDark ? 'bg-yellow-900/30 text-yellow-400' : 'bg-yellow-100 text-yellow-600',
        'question_liked': themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600',
        'question_upvoted': themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600',
        'question_answered': themeStore.isDark ? 'bg-indigo-900/30 text-indigo-400' : 'bg-indigo-100 text-indigo-600',
        'answer_upvoted': themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600',
        'answer_replied': themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600',
        'answer_accepted': themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600',
        'mentioned': themeStore.isDark ? 'bg-orange-900/30 text-orange-400' : 'bg-orange-100 text-orange-600',
        'followed': themeStore.isDark ? 'bg-pink-900/30 text-pink-400' : 'bg-pink-100 text-pink-600',
    }
    return colors[type] || (themeStore.isDark ? 'bg-gray-800 text-gray-400' : 'bg-gray-100 text-gray-600')
}

// Format time
const formatTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diff = now - date
    const seconds = Math.floor(diff / 1000)
    const minutes = Math.floor(seconds / 60)
    const hours = Math.floor(minutes / 60)
    const days = Math.floor(hours / 24)
    
    if (days > 0) return `${days}d ago`
    if (hours > 0) return `${hours}h ago`
    if (minutes > 0) return `${minutes}m ago`
    return 'Just now'
}

// Polling control (fallback when realtime unavailable)
let pollingIntervalId = null
const POLLING_INTERVAL = 10000

const startPolling = () => {
    if (pollingIntervalId || !authStore.isAuthenticated) {
        return
    }
    // Initial fetch when polling starts
    fetchNotifications()
    pollingIntervalId = setInterval(() => {
        fetchNotifications()
    }, POLLING_INTERVAL)
}

const stopPolling = () => {
    if (pollingIntervalId) {
        clearInterval(pollingIntervalId)
        pollingIntervalId = null
    }
}

watch(isRealtimeConnected, (connected) => {
    if (connected) {
        stopPolling()
    } else {
        startPolling()
    }
}, { immediate: true })

onMounted(() => {
    if (authStore.isAuthenticated) {
        // fetchNotifications already returns unread_count, so no need for separate fetchUnreadCount call
        if (!isRealtimeConnected.value) {
            startPolling()
        }
    }
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    stopPolling()
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div v-if="authStore.isAuthenticated" ref="dropdownRef" class="relative">
        <!-- Bell Icon Button -->
        <Button
            variant="ghost"
            size="icon"
            @click="toggleDropdown"
            class="relative hover:bg-transparent hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300 text-gray-700 dark:text-gray-300 dark:hover:text-white"
        >
            <Bell class="h-5 w-5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 h-5 w-5 rounded-full flex items-center justify-center text-xs font-bold text-white"
                :class="themeStore.isDark ? 'bg-red-600' : 'bg-red-500'"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </Button>

        <!-- Dropdown -->
        <Transition name="dropdown">
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-96 max-h-[500px] rounded-xl shadow-2xl border backdrop-blur-xl overflow-hidden z-50"
                :class="[
                    themeStore.isDark
                        ? 'bg-gray-900/95 border-gray-800'
                        : 'bg-white/95 border-gray-200'
                ]"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-2 border-b" :class="[
                    themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
                ]">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-lg" :class="[
                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                        ]">
                            Notifications
                        </h3>
                        <!-- Connection Status Indicator -->
                        <div class="flex items-center gap-1" :title="isRealtimeConnected ? 'Real-time connected' : 'Real-time disconnected'">
                            <Wifi 
                                v-if="isRealtimeConnected" 
                                class="h-3 w-3" 
                                :class="themeStore.isDark ? 'text-green-400' : 'text-green-600'"
                            />
                            <WifiOff 
                                v-else 
                                class="h-3 w-3" 
                                :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'"
                            />
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="isOpen = false"
                        class="h-8 w-8"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-[400px] overflow-y-auto">
                    <div v-if="isLoading" class="p-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 mx-auto mb-2" :class="[
                            themeStore.isDark ? 'border-white' : 'border-gray-900'
                        ]"></div>
                        <p class="text-sm" :class="[
                            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                        ]">Loading...</p>
                    </div>

                    <div v-else-if="notifications.length === 0" class="p-8 text-center">
                        <Bell class="h-12 w-12 mx-auto mb-3 opacity-50" :class="[
                            themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                        ]" />
                        <p class="text-sm font-medium mb-1" :class="[
                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                        ]">No notifications</p>
                        <p class="text-xs" :class="[
                            themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                        ]">You're all caught up!</p>
                    </div>

                    <div v-else class="divide-y" :class="[
                        themeStore.isDark ? 'divide-gray-800' : 'divide-gray-100'
                    ]">
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="px-4 py-3 cursor-pointer transition-colors relative group"
                            :class="[
                                notification.read
                                    ? (themeStore.isDark ? 'hover:bg-gray-800/50' : 'hover:bg-gray-50')
                                    : (themeStore.isDark ? 'bg-blue-900/10 hover:bg-blue-900/20' : 'bg-blue-50/50 hover:bg-blue-50')
                            ]"
                        >
                            <!-- Unread Indicator -->
                            <div
                                v-if="!notification.read"
                                class="absolute left-0 top-0 bottom-0 w-1"
                                :class="themeStore.isDark ? 'bg-blue-500' : 'bg-blue-500'"
                            ></div>

                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-lg" :class="getNotificationColor(notification.type)">
                                    {{ getNotificationIcon(notification.type) }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <p class="text-sm font-medium line-clamp-2" :class="[
                                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                                        ]">
                                            {{ notification.title }}
                                        </p>
                                        <Button
                                            v-if="!notification.read"
                                            variant="ghost"
                                            size="icon"
                                            @click.stop="markAsRead(notification.id, $event)"
                                            class="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                        >
                                            <Check class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <p class="text-xs mb-2 line-clamp-2" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                    ]">
                                        {{ notification.message }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs" :class="[
                                            themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                                        ]">
                                            {{ formatTime(notification.created_at) }}
                                        </span>
                                        <span
                                            v-if="notification.notifiable"
                                            class="text-xs font-medium"
                                            :class="[
                                                themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                            ]"
                                        >
                                            @{{ notification.notifiable.username }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="notifications.length > 0" class="px-4 py-2 border-t" :class="[
                    themeStore.isDark ? 'border-gray-800 bg-gray-900/50' : 'border-gray-200 bg-gray-50/50'
                ]">
                    <Button
                        variant="ghost"
                        class="w-full justify-center text-sm"
                        @click="viewAll"
                    >
                        View All Notifications
                    </Button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}

.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

