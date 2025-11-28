<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useThemeStore } from '../stores/theme'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card'
import { Button } from '../components/ui/button'
import { Switch } from '../components/ui/switch'
import { Label } from '../components/ui/label'
import { Bell, Mail, Smartphone, ArrowLeft, Loader2, ChevronDown, ChevronUp, AlertCircle, Sun, Moon } from 'lucide-vue-next'
import { usePushNotifications } from '../composables/usePushNotifications'
import axios from 'axios'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const pushNotificationsComposable = usePushNotifications()

const isLoading = ref(false)
const isSaving = ref(false)
const isNotificationCardExpanded = ref(true)
const emailNotifications = ref(true)
const pushNotifications = ref(true)
const pushSubscriptionStatus = ref('checking') // 'checking', 'subscribed', 'not_subscribed', 'unsupported', 'blocked'
const pushSubscriptionError = ref(null)
const notificationPreferences = ref({
    post_liked: true,
    post_commented: true,
    comment_liked: true,
    question_liked: true,
    question_upvoted: true,
    question_answered: true,
    answer_upvoted: true,
    answer_replied: true,
    mentioned: true,
    followed: true,
})

// Notification preference labels
const preferenceLabels = {
    post_liked: 'Post Likes',
    post_commented: 'Post Comments',
    comment_liked: 'Comment Likes',
    question_liked: 'Question Likes',
    question_upvoted: 'Question Upvotes',
    question_answered: 'Question Answers',
    answer_upvoted: 'Answer Upvotes',
    answer_replied: 'Answer Replies',
    mentioned: 'Mentions',
    followed: 'New Followers',
}

// Fetch notification settings
const fetchSettings = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    isLoading.value = true
    try {
        const response = await axios.get('/api/v1/settings/notifications', authStore.config)
        if (response.data.status === 'success') {
            emailNotifications.value = response.data.data.email_notifications ?? true
            pushNotifications.value = response.data.data.push_notifications ?? true
            notificationPreferences.value = response.data.data.notification_preferences ?? notificationPreferences.value
        }
        
        // Check push subscription status
        await checkPushSubscriptionStatus()
    } catch (error) {
        console.error('Error fetching notification settings:', error)
    } finally {
        isLoading.value = false
    }
}

// Check push subscription status
const checkPushSubscriptionStatus = async () => {
    if (!pushNotificationsComposable.isSupported) {
        pushSubscriptionStatus.value = 'unsupported'
        return
    }

    try {
        const isSubscribed = await pushNotificationsComposable.checkSubscription(authStore)
        pushSubscriptionStatus.value = isSubscribed ? 'subscribed' : 'not_subscribed'
    } catch (error) {
        console.error('Error checking push subscription:', error)
        pushSubscriptionStatus.value = 'not_subscribed'
    }
}

// Handle push notification toggle
const handlePushNotificationToggle = async () => {
    if (isSaving.value) return
    
    const newValue = !pushNotifications.value
    pushNotifications.value = newValue
    
    // Save setting
    await saveSettings()
    
    // If enabling, try to subscribe
    if (newValue && pushSubscriptionStatus.value === 'not_subscribed') {
        await subscribeToPush()
    } else if (!newValue && pushSubscriptionStatus.value === 'subscribed') {
        await unsubscribeFromPush()
    }
}

// Subscribe to push notifications
const subscribeToPush = async () => {
    if (!pushNotificationsComposable.isSupported) {
        pushSubscriptionStatus.value = 'unsupported'
        return
    }

    pushSubscriptionStatus.value = 'checking'
    pushSubscriptionError.value = null

    try {
        const result = await pushNotificationsComposable.subscribe(authStore)
        if (result) {
            pushSubscriptionStatus.value = 'subscribed'
        } else {
            pushSubscriptionStatus.value = 'not_subscribed'
        }
    } catch (error) {
        console.error('Error subscribing to push notifications:', error)
        pushSubscriptionStatus.value = 'not_subscribed'
        
        if (error.message?.includes('denied') || error.message?.includes('permission')) {
            pushSubscriptionStatus.value = 'blocked'
            pushSubscriptionError.value = 'Notification permission was denied. Please enable it in your browser settings.'
        } else {
            pushSubscriptionError.value = error.message || 'Failed to subscribe to push notifications'
        }
    }
}

// Unsubscribe from push notifications
const unsubscribeFromPush = async () => {
    try {
        await pushNotificationsComposable.unsubscribe(authStore)
        pushSubscriptionStatus.value = 'not_subscribed'
        pushSubscriptionError.value = null
    } catch (error) {
        console.error('Error unsubscribing from push notifications:', error)
        pushSubscriptionError.value = error.message || 'Failed to unsubscribe from push notifications'
    }
}

// Save notification settings
const saveSettings = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }

    isSaving.value = true
    try {
        const response = await axios.put('/api/v1/settings/notifications', {
            email_notifications: emailNotifications.value,
            push_notifications: pushNotifications.value,
            notification_preferences: notificationPreferences.value,
        }, authStore.config)

        if (response.data.status === 'success') {
            // Show success message (you can add toast notification here)
            console.log('Notification settings saved successfully')
        }
    } catch (error) {
        console.error('Error saving notification settings:', error)
    } finally {
        isSaving.value = false
    }
}

// Handle preference toggle
const togglePreference = async (key) => {
    if (isSaving.value) return
    notificationPreferences.value[key] = !notificationPreferences.value[key]
    // Auto-save on toggle
    await saveSettings()
}

// Handle main notification toggles
const toggleEmailNotifications = async () => {
    if (isSaving.value) return
    const newValue = !emailNotifications.value
    emailNotifications.value = newValue
    await saveSettings()
}


onMounted(() => {
    fetchSettings()
})
</script>

<template>
    <div class="min-h-screen">
        <div class="max-w-4xl mx-auto py-5">
            <!-- Header -->
            <div class="flex items-center gap-5 mb-5">
                <Button
                    variant="ghost"
                    size="icon"
                    @click="router.back()"
                    class="flex-shrink-0"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold" :class="[
                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                    ]">
                        Settings
                    </h1>
                    <p class="text-sm mt-1" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">
                        Manage your account settings and preferences
                    </p>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex items-center justify-center py-16">
                <div class="text-center">
                    <Loader2 class="h-8 w-8 animate-spin mx-auto mb-4" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]" />
                    <p :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">Loading settings...</p>
                </div>
            </div>

            <!-- Settings Content -->
            <div v-else class="space-y-5">
                <!-- App Preferences -->
                <Card :class="[
                    'border-0 shadow-sm',
                    themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
                ]">
                    <CardHeader>
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <CardTitle :class="[themeStore.isDark ? 'text-white' : 'text-gray-900']">
                                    App Preferences
                                </CardTitle>
                                <CardDescription :class="[themeStore.isDark ? 'text-gray-400' : 'text-gray-600']">
                                    Choose how Laravue looks on this device
                                </CardDescription>
                            </div>
                            <Button
                                variant="outline"
                                class="flex items-center gap-2 rounded-full border-2 transition-all duration-300"
                                :class="themeStore.isDark
                                    ? 'border-white/20 text-white hover:bg-white/5'
                                    : 'border-gray-200 text-gray-800 hover:bg-gray-50'"
                                @click="themeStore.toggleTheme()"
                            >
                                <Sun v-if="themeStore.isDark" class="h-4 w-4" />
                                <Moon v-else class="h-4 w-4" />
                                <span class="text-sm font-semibold">
                                    {{ themeStore.isDark ? 'Light Mode' : 'Dark Mode' }}
                                </span>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm" :class="[themeStore.isDark ? 'text-gray-400' : 'text-gray-600']">
                            Theme preference syncs across the app after you toggle it here. Use this to switch between light and dark experiences.
                        </div>
                    </CardContent>
                </Card>
                <!-- Notification Preferences Card -->
                <Card :class="[
                    'border-0 shadow-sm',
                    themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
                ]">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="[
                                    themeStore.isDark ? 'bg-blue-900/30' : 'bg-blue-100'
                                ]">
                                    <Bell class="h-5 w-5" :class="[
                                        themeStore.isDark ? 'text-blue-400' : 'text-blue-600'
                                    ]" />
                                </div>
                                <div>
                                    <CardTitle :class="[
                                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                                    ]">
                                        Notification Preferences
                                    </CardTitle>
                                    <CardDescription :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                    ]">
                                        Control how and when you receive notifications
                                    </CardDescription>
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="isNotificationCardExpanded = !isNotificationCardExpanded"
                                class="flex-shrink-0"
                            >
                                <ChevronDown 
                                    v-if="isNotificationCardExpanded"
                                    class="h-5 w-5 transition-transform"
                                />
                                <ChevronUp 
                                    v-else
                                    class="h-5 w-5 transition-transform"
                                />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent v-show="isNotificationCardExpanded" class="space-y-6">
                        <!-- Email Notifications Toggle -->
                        <div class="flex items-center justify-between p-4 rounded-lg border" :class="[
                            themeStore.isDark 
                                ? 'bg-gray-800/30 border-gray-700' 
                                : 'bg-gray-50 border-gray-200'
                        ]">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="[
                                    themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                                ]">
                                    <Mail class="h-4 w-4" :class="[
                                        themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                    ]" />
                                </div>
                                <div>
                                    <Label :class="[
                                        themeStore.isDark ? 'text-white' : 'text-gray-900'
                                    ]" class="text-base font-medium">
                                        Email Notifications
                                    </Label>
                                    <p class="text-sm" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                    ]">
                                        Receive email notifications for important updates
                                    </p>
                                </div>
                            </div>
                            <Switch
                                :checked="emailNotifications"
                                @update:checked="toggleEmailNotifications"
                                :disabled="isSaving"
                            />
                        </div>

                        <!-- Push Notifications Toggle -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 rounded-lg border" :class="[
                                themeStore.isDark 
                                    ? 'bg-gray-800/30 border-gray-700' 
                                    : 'bg-gray-50 border-gray-200'
                            ]">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg" :class="[
                                        themeStore.isDark ? 'bg-gray-700' : 'bg-gray-200'
                                    ]">
                                        <Smartphone class="h-4 w-4" :class="[
                                            themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                        ]" />
                                    </div>
                                    <div class="flex-1">
                                        <Label :class="[
                                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                                        ]" class="text-base font-medium">
                                            Push Notifications
                                        </Label>
                                        <p class="text-sm" :class="[
                                            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                        ]">
                                            Receive browser push notifications
                                        </p>
                                        <div v-if="pushSubscriptionStatus !== 'checking' && pushSubscriptionStatus !== 'unsupported'" class="mt-2">
                                            <span class="text-xs px-2 py-1 rounded-full" :class="[
                                                pushSubscriptionStatus === 'subscribed'
                                                    ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-700')
                                                    : (themeStore.isDark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-600')
                                            ]">
                                                {{ pushSubscriptionStatus === 'subscribed' ? 'Subscribed' : 'Not subscribed' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <Switch
                                    :checked="pushNotifications"
                                    @update:checked="handlePushNotificationToggle"
                                    :disabled="isSaving || pushSubscriptionStatus === 'unsupported' || pushSubscriptionStatus === 'blocked'"
                                />
                            </div>
                            
                            <!-- Push Subscription Status Messages -->
                            <div v-if="pushSubscriptionStatus === 'unsupported'" class="p-3 rounded-lg border" :class="[
                                themeStore.isDark 
                                    ? 'bg-yellow-900/20 border-yellow-800/50' 
                                    : 'bg-yellow-50 border-yellow-200'
                            ]">
                                <div class="flex items-start gap-2">
                                    <AlertCircle class="h-4 w-4 mt-0.5 flex-shrink-0" :class="[
                                        themeStore.isDark ? 'text-yellow-400' : 'text-yellow-600'
                                    ]" />
                                    <p class="text-xs" :class="[
                                        themeStore.isDark ? 'text-yellow-300' : 'text-yellow-700'
                                    ]">
                                        Push notifications are not supported in this browser. Please use a modern browser like Chrome, Firefox, or Edge.
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="pushSubscriptionStatus === 'blocked'" class="p-3 rounded-lg border" :class="[
                                themeStore.isDark 
                                    ? 'bg-red-900/20 border-red-800/50' 
                                    : 'bg-red-50 border-red-200'
                            ]">
                                <div class="flex items-start gap-2">
                                    <AlertCircle class="h-4 w-4 mt-0.5 flex-shrink-0" :class="[
                                        themeStore.isDark ? 'text-red-400' : 'text-red-600'
                                    ]" />
                                    <div class="flex-1">
                                        <p class="text-xs font-medium mb-1" :class="[
                                            themeStore.isDark ? 'text-red-300' : 'text-red-700'
                                        ]">
                                            Notification permission denied
                                        </p>
                                        <p class="text-xs" :class="[
                                            themeStore.isDark ? 'text-red-400' : 'text-red-600'
                                        ]">
                                            {{ pushSubscriptionError || 'Please enable notifications in your browser settings and try again.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="pushSubscriptionError && pushSubscriptionStatus !== 'blocked'" class="p-3 rounded-lg border" :class="[
                                themeStore.isDark 
                                    ? 'bg-red-900/20 border-red-800/50' 
                                    : 'bg-red-50 border-red-200'
                            ]">
                                <div class="flex items-start gap-2">
                                    <AlertCircle class="h-4 w-4 mt-0.5 flex-shrink-0" :class="[
                                        themeStore.isDark ? 'text-red-400' : 'text-red-600'
                                    ]" />
                                    <p class="text-xs" :class="[
                                        themeStore.isDark ? 'text-red-300' : 'text-red-700'
                                    ]">
                                        {{ pushSubscriptionError }}
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="pushNotifications && pushSubscriptionStatus === 'not_subscribed' && !pushSubscriptionError" class="p-3 rounded-lg border" :class="[
                                themeStore.isDark 
                                    ? 'bg-blue-900/20 border-blue-800/50' 
                                    : 'bg-blue-50 border-blue-200'
                            ]">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs" :class="[
                                        themeStore.isDark ? 'text-blue-300' : 'text-blue-700'
                                    ]">
                                        Click the toggle above to subscribe to push notifications
                                    </p>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="subscribeToPush"
                                        :disabled="isSaving"
                                    >
                                        Subscribe
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t" :class="[
                            themeStore.isDark ? 'border-gray-700' : 'border-gray-200'
                        ]"></div>

                        <!-- Granular Notification Preferences -->
                        <div>
                            <h3 class="text-sm font-semibold mb-4" :class="[
                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                            ]">
                                Notification Types
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="(value, key) in notificationPreferences"
                                    :key="key"
                                    class="flex items-center justify-between p-3 rounded-lg border transition-colors"
                                    :class="[
                                        themeStore.isDark 
                                            ? 'bg-gray-800/30 border-gray-700 hover:bg-gray-800/50' 
                                            : 'bg-gray-50 border-gray-200 hover:bg-gray-100'
                                    ]"
                                >
                                    <Label :class="[
                                        themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                    ]" class="text-sm font-medium cursor-pointer">
                                        {{ preferenceLabels[key] || key }}
                                    </Label>
                                    <Switch
                                        :checked="value"
                                        @update:checked="() => togglePreference(key)"
                                        :disabled="isSaving || !emailNotifications"
                                    />
                                </div>
                            </div>
                            <p v-if="!emailNotifications" class="text-xs mt-3" :class="[
                                themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                            ]">
                                Enable email notifications to configure individual notification types
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Placeholder for future settings -->
                <Card :class="[
                    'border-0 shadow-sm opacity-50',
                    themeStore.isDark ? 'bg-gray-900/50' : 'bg-white'
                ]">
                    <CardHeader>
                        <CardTitle :class="[
                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                        ]">
                            More Settings
                        </CardTitle>
                        <CardDescription :class="[
                            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                        ]">
                            Additional settings will be available soon
                        </CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </div>
    </div>
</template>

