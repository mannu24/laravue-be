<template>
  <Transition name="slide-up">
    <div
      v-if="showConsent"
      class="fixed bottom-0 left-0 right-0 z-50 p-4"
      :class="themeStore.isDark ? 'bg-gray-900/95 backdrop-blur-sm' : 'bg-white/95 backdrop-blur-sm'"
    >
      <div class="container mx-auto max-w-6xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl border shadow-lg"
             :class="themeStore.isDark 
               ? 'bg-gradient-to-r from-gray-800/90 to-gray-900/90 border-white/10' 
               : 'bg-gradient-to-r from-white to-gray-50 border-gray-200'">
          <div class="flex items-center gap-4 flex-1">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center"
                 :class="themeStore.isDark ? 'bg-gradient-to-br from-blue-500/20 to-purple-500/20' : 'bg-gradient-to-br from-blue-100 to-purple-100'">
              <Bell class="w-6 h-6" :class="themeStore.isDark ? 'text-blue-400' : 'text-blue-600'" />
            </div>
            <div class="flex-1">
              <h3 class="text-base font-semibold mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                Stay Updated with Notifications
              </h3>
              <p class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                To provide you a better experience, please subscribe to notifications. Get real-time updates on your activities, achievements, and interactions.
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3 flex-shrink-0">
            <Button
              @click="handleDecline"
              variant="ghost"
              size="sm"
              :class="themeStore.isDark ? 'text-gray-400 hover:text-gray-300' : 'text-gray-600 hover:text-gray-900'"
            >
              Maybe Later
            </Button>
            <Button
              @click="handleAccept"
              :disabled="isRequesting"
              size="sm"
              class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white"
            >
              <Bell class="w-4 h-4 mr-2" />
              {{ isRequesting ? 'Enabling...' : 'Enable Notifications' }}
            </Button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'
import { usePushNotifications } from '@/composables/usePushNotifications'
import { Button } from '@/components/ui/button'
import { Bell } from 'lucide-vue-next'

const CONSENT_KEY = 'notification_consent_shown'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const pushNotifications = usePushNotifications()

const showConsent = ref(false)
const isRequesting = ref(false)

/**
 * Check if we should show the consent banner
 */
const shouldShowConsent = () => {
  // Check if already shown
  if (localStorage.getItem(CONSENT_KEY) === 'true') {
    return false
  }

  // Check if notifications are supported
  if (typeof window === 'undefined' || !('Notification' in window)) {
    return false
  }

  // Check current permission status
  const permission = Notification.permission
  
  // Don't show if already granted or denied
  if (permission === 'granted' || permission === 'denied') {
    // Mark as shown so we don't check again
    localStorage.setItem(CONSENT_KEY, 'true')
    return false
  }

  // Only show if permission is 'default' (not yet asked)
  return permission === 'default'
}

/**
 * Handle accept - request notification permission
 */
const handleAccept = async () => {
  if (isRequesting.value) return

  isRequesting.value = true

  try {
    // Initialize push notifications if needed
    if (authStore.isAuthenticated) {
      await pushNotifications.initialize(authStore)
    }

    // Request permission via push notifications composable
    if (authStore.isAuthenticated) {
      await pushNotifications.subscribe(authStore)
    } else {
      // If not authenticated, just request browser permission
      const permission = await Notification.requestPermission()
      if (permission === 'granted') {
        console.log('[NotificationConsent] Permission granted')
      }
    }
    
    // Mark as shown
    localStorage.setItem(CONSENT_KEY, 'true')
    showConsent.value = false
  } catch (error) {
    console.error('[NotificationConsent] Permission request failed:', error)
    // Mark as shown even if denied (so we don't ask again)
    localStorage.setItem(CONSENT_KEY, 'true')
    showConsent.value = false
  } finally {
    isRequesting.value = false
  }
}

/**
 * Handle decline - just hide the banner
 */
const handleDecline = () => {
  showConsent.value = false
  // Mark as shown so we don't show again
  localStorage.setItem(CONSENT_KEY, 'true')
}

onMounted(() => {
  // Check if we should show consent after a short delay
  setTimeout(() => {
    if (shouldShowConsent()) {
      showConsent.value = true
    }
  }, 1000) // 1 second delay
})
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease-out;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(100%);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
</style>

