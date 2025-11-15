<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useThemeStore } from '../stores/theme'
import { Bell, Check, CheckCheck, Trash2, Square, CheckSquare2 } from 'lucide-vue-next'
import { Button } from '../components/ui/button'
import { Card, CardContent } from '../components/ui/card'
import { Skeleton } from '../components/ui/skeleton'
import EmptyState from '@/components/ui/EmptyState.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import axios from 'axios'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const notifications = ref([])
const isLoading = ref(false)
const pageNo = ref(1)
const hasMore = ref(true)
const unreadCount = ref(0)
const observer = ref(null)

// Selection state
const selectedNotifications = ref(new Set())
const isSelectionMode = ref(false)
const isDeleting = ref(false)
const showDeleteDialog = ref(false)

// Fetch notifications
const fetchNotifications = async () => {
    if (isLoading.value || !hasMore.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get(`/api/v1/notifications?page=${pageNo.value}&per_page=20`, authStore.config)
        if (response.data.status === 'success') {
            const newNotifications = response.data.data.notifications || []
            notifications.value.push(...newNotifications)
            unreadCount.value = response.data.data.pagination?.unread_count || 0
            hasMore.value = newNotifications.length === 20
        }
    } catch (error) {
        console.error('Error fetching notifications:', error)
    } finally {
        isLoading.value = false
    }
}

// Fetch unread count
const fetchUnreadCount = async () => {
    try {
        const response = await axios.get('/api/v1/notifications/unread-count', authStore.config)
        if (response.data.status === 'success') {
            unreadCount.value = response.data.data.unread_count || 0
        }
    } catch (error) {
        console.error('Error fetching unread count:', error)
    }
}

// Mark notification as read
const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/api/v1/notifications/${notificationId}/read`, {}, authStore.config)
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification) {
            notification.read = true
            notification.read_at = new Date().toISOString()
            if (unreadCount.value > 0) {
                unreadCount.value--
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error)
    }
}

// Mark all as read
const markAllAsRead = async () => {
    try {
        await axios.post('/api/v1/notifications/read-all', {}, authStore.config)
        notifications.value.forEach(n => {
            n.read = true
            n.read_at = new Date().toISOString()
        })
        unreadCount.value = 0
    } catch (error) {
        console.error('Error marking all as read:', error)
    }
}

// Delete notification
const deleteNotification = async (notificationId) => {
    try {
        await axios.delete(`/api/v1/notifications/${notificationId}`, authStore.config)
        notifications.value = notifications.value.filter(n => n.id !== notificationId)
        selectedNotifications.value.delete(notificationId)
        if (unreadCount.value > 0) {
            unreadCount.value--
        }
    } catch (error) {
        console.error('Error deleting notification:', error)
    }
}

// Toggle selection mode
const toggleSelectionMode = () => {
    isSelectionMode.value = !isSelectionMode.value
    if (!isSelectionMode.value) {
        selectedNotifications.value.clear()
    }
}

// Toggle notification selection
const toggleNotificationSelection = (notificationId) => {
    if (selectedNotifications.value.has(notificationId)) {
        selectedNotifications.value.delete(notificationId)
    } else {
        selectedNotifications.value.add(notificationId)
    }
}

// Select all visible notifications
const selectAll = () => {
    if (selectedNotifications.value.size === notifications.value.length) {
        selectedNotifications.value.clear()
    } else {
        notifications.value.forEach(n => selectedNotifications.value.add(n.id))
    }
}

// Computed for selected count
const selectedCount = computed(() => selectedNotifications.value.size)

// Computed for all selected
const allSelected = computed(() => {
    return notifications.value.length > 0 && selectedNotifications.value.size === notifications.value.length
})

// Bulk delete selected notifications
const showBulkDeleteConfirmation = () => {
    if (selectedNotifications.value.size === 0) return
    showDeleteDialog.value = true
}

const bulkDelete = async () => {
    isDeleting.value = true
    try {
        const ids = Array.from(selectedNotifications.value)
        const response = await axios.post(
            '/api/v1/notifications/bulk-delete',
            { ids },
            authStore.config
        )

        if (response.data.status === 'success') {
            // Count deleted unread notifications before removing them
            const deletedUnreadCount = notifications.value.filter(
                n => selectedNotifications.value.has(n.id) && !n.read
            ).length
            
            // Remove deleted notifications from the list
            notifications.value = notifications.value.filter(
                n => !selectedNotifications.value.has(n.id)
            )
            
            // Update unread count
            unreadCount.value = Math.max(0, unreadCount.value - deletedUnreadCount)
            
            // Clear selection
            selectedNotifications.value.clear()
            isSelectionMode.value = false
        }
    } catch (error) {
        console.error('Error bulk deleting notifications:', error)
        alert('Failed to delete notifications. Please try again.')
    } finally {
        isDeleting.value = false
    }
}

// Handle notification click
const handleNotificationClick = (notification) => {
    if (!notification.read) {
        markAsRead(notification.id)
    }
    
    if (notification.data?.url) {
        router.push(notification.data.url)
    }
}

// Setup intersection observer for infinite scroll
const setupIntersectionObserver = () => {
    if (observer.value) {
        observer.value.disconnect()
    }

    const options = {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    }

    observer.value = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore.value && !isLoading.value) {
                pageNo.value++
            }
        })
    }, options)

    // Observe last notification after DOM update
    setTimeout(() => {
        const lastNotification = document.querySelector('.notifications-container .last-notification')
        if (lastNotification && observer.value) {
            observer.value.observe(lastNotification)
        }
    }, 100)
}

// Get notification icon
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

// Get notification color
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
    
    if (days > 7) {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }
    if (days > 0) return `${days}d ago`
    if (hours > 0) return `${hours}h ago`
    if (minutes > 0) return `${minutes}m ago`
    return 'Just now'
}

onMounted(() => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }
    
    fetchNotifications().then(() => {
        // Setup observer after notifications are loaded
        setupIntersectionObserver()
    })
    fetchUnreadCount()
})

onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect()
    }
})

// Watch pageNo for pagination
watch(pageNo, async () => {
    if (pageNo.value > 1) {
        await fetchNotifications()
        // Re-setup observer after new notifications are loaded
        setupIntersectionObserver()
    }
})
</script>

<template>
    <div :class="[
        'min-h-screen transition-colors duration-300',
        themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
    ]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold mb-2" :class="[
                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                    ]">
                        Notifications
                    </h1>
                    <p class="text-sm" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">
                        <span v-if="isSelectionMode && selectedCount > 0">
                            {{ selectedCount }} notification{{ selectedCount !== 1 ? 's' : '' }} selected
                        </span>
                        <span v-else>
                            {{ unreadCount > 0 ? `${unreadCount} unread notification${unreadCount !== 1 ? 's' : ''}` : 'All caught up!' }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="isSelectionMode"
                        variant="destructive"
                        @click="showBulkDeleteConfirmation"
                        :disabled="selectedCount === 0 || isDeleting"
                        class="gap-2"
                    >
                        <Trash2 class="h-4 w-4" />
                        Delete Selected ({{ selectedCount }})
                    </Button>
                    <Button
                        v-if="isSelectionMode"
                        variant="outline"
                        @click="toggleSelectionMode"
                        class="gap-2"
                    >
                        Cancel
                    </Button>
                    <Button
                        v-if="!isSelectionMode && unreadCount > 0"
                        variant="outline"
                        @click="markAllAsRead"
                        class="gap-2"
                    >
                        <CheckCheck class="h-4 w-4" />
                        Mark All Read
                    </Button>
                    <Button
                        v-if="!isSelectionMode && notifications.length > 0"
                        variant="outline"
                        @click="toggleSelectionMode"
                        class="gap-2"
                    >
                        Select
                    </Button>
                </div>
            </div>

            <!-- Selection Mode Header -->
            <div v-if="isSelectionMode && notifications.length > 0" class="mb-4 p-3 rounded-lg border" :class="[
                themeStore.isDark ? 'bg-gray-800/50 border-gray-700' : 'bg-white/50 border-gray-200'
            ]">
                <div class="flex items-center justify-between">
                    <Button
                        variant="ghost"
                        @click="selectAll"
                        class="gap-2"
                    >
                        <CheckSquare2 v-if="allSelected" class="h-4 w-4" />
                        <Square v-else class="h-4 w-4" />
                        {{ allSelected ? 'Deselect All' : 'Select All' }}
                    </Button>
                    <span class="text-sm" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">
                        {{ selectedCount }} of {{ notifications.length }} selected
                    </span>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notifications-container space-y-3">
                <TransitionGroup name="fade" appear>
                    <Card
                        v-for="(notification, index) in notifications"
                        :key="notification.id"
                        :class="[
                            'transition-all duration-200 border-0 shadow-sm',
                            isSelectionMode ? 'cursor-default' : 'cursor-pointer',
                            selectedNotifications.has(notification.id)
                                ? (themeStore.isDark ? 'bg-blue-900/20 border-2 border-blue-500' : 'bg-blue-50 border-2 border-blue-500')
                                : notification.read
                                    ? (themeStore.isDark ? 'bg-gray-800/50 hover:bg-gray-800/70' : 'bg-white/50 hover:bg-white/80')
                                    : (themeStore.isDark ? 'bg-blue-900/10 hover:bg-blue-900/20 border-l-4 border-blue-500' : 'bg-blue-50/50 hover:bg-blue-50 border-l-4 border-blue-500'),
                            index === notifications.length - 1 ? 'last-notification' : ''
                        ]"
                        @click="isSelectionMode ? toggleNotificationSelection(notification.id) : handleNotificationClick(notification)"
                    >
                        <CardContent class="p-4">
                            <div class="flex items-start gap-4">
                                <!-- Checkbox (Selection Mode) -->
                                <div v-if="isSelectionMode" class="flex-shrink-0 pt-1" @click.stop="toggleNotificationSelection(notification.id)">
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center cursor-pointer transition-colors" :class="[
                                        selectedNotifications.has(notification.id)
                                            ? (themeStore.isDark ? 'bg-blue-500 border-blue-500' : 'bg-blue-500 border-blue-500')
                                            : (themeStore.isDark ? 'border-gray-600 hover:border-gray-400' : 'border-gray-300 hover:border-gray-400')
                                    ]">
                                        <Check v-if="selectedNotifications.has(notification.id)" class="h-3 w-3 text-white" />
                                    </div>
                                </div>

                                <!-- Icon -->
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center text-xl" :class="getNotificationColor(notification.type)">
                                    {{ getNotificationIcon(notification.type) }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-base font-semibold mb-1" :class="[
                                                themeStore.isDark ? 'text-white' : 'text-gray-900'
                                            ]">
                                                {{ notification.title }}
                                            </h3>
                                            <p class="text-sm mb-2" :class="[
                                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                            ]">
                                                {{ notification.message }}
                                            </p>
                                        </div>
                                        <div v-if="!isSelectionMode" class="flex items-center gap-2 flex-shrink-0">
                                            <Button
                                                v-if="!notification.read"
                                                variant="ghost"
                                                size="icon"
                                                @click.stop="markAsRead(notification.id)"
                                                class="h-8 w-8"
                                            >
                                                <Check class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                @click.stop="deleteNotification(notification.id)"
                                                class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
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
                        </CardContent>
                    </Card>
                </TransitionGroup>

                <!-- Loading State -->
                <Transition name="fade">
                    <div v-if="isLoading" class="space-y-3">
                        <Card v-for="i in 3" :key="i" :class="[
                            'border-0 shadow-sm',
                            themeStore.isDark ? 'bg-gray-800/50' : 'bg-white/50'
                        ]">
                            <CardContent class="p-4">
                                <div class="flex items-start gap-4">
                                    <Skeleton class="w-12 h-12 rounded-lg" />
                                    <div class="flex-1 space-y-2">
                                        <Skeleton class="h-4 w-3/4" />
                                        <Skeleton class="h-3 w-full" />
                                        <Skeleton class="h-3 w-1/2" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </Transition>

                <!-- Empty State -->
                <EmptyState
                    v-if="!isLoading && notifications.length === 0"
                    icon="Bell"
                    title="No notifications yet"
                    subtitle="You'll see notifications here when someone interacts with your content"
                    size="default"
                />

                <!-- End of List -->
                <div v-if="!hasMore && notifications.length > 0" class="text-center py-8">
                    <p class="text-sm" :class="[
                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                    ]">No more notifications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model:open="showDeleteDialog"
      :title="`Delete ${selectedNotifications.size} Notification(s)`"
      :description="`Are you sure you want to delete ${selectedNotifications.size} notification(s)? This action cannot be undone.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      @confirm="bulkDelete"
    />
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>

