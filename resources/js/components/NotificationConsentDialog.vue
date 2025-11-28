<template>
  <AlertDialog :open="showConsent" @update:open="handleDialogToggle">
    <AlertDialogContent class="sm:max-w-lg border border-primary/20 bg-gradient-to-b from-white to-slate-50 dark:from-gray-900 dark:to-gray-950">
      <AlertDialogHeader class="space-y-4">
        <div
          class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto shadow-xl"
          :class="themeStore.isDark ? 'bg-primary/20 text-primary-100' : 'bg-primary/10 text-primary'"
        >
          <Bell class="w-6 h-6" />
        </div>
        <div class="text-center space-y-2">
          <AlertDialogTitle class="text-2xl font-semibold">
            Enable smart notifications
          </AlertDialogTitle>
          <AlertDialogDescription class="text-base leading-relaxed">
            Get instant alerts for replies, badges, and live community drops.
            We only ping you for the things you choose to follow.
          </AlertDialogDescription>
        </div>
      </AlertDialogHeader>

      <div
        class="rounded-xl border border-dashed border-primary/30 bg-white/60 dark:bg-white/5 p-4 flex flex-col gap-3 text-sm text-left"
      >
        <div class="flex items-center gap-3">
          <ShieldCheck class="w-4 h-4 text-emerald-500" />
          <span>Encrypted delivery + one-tap unsubscribe.</span>
        </div>
        <div class="flex items-center gap-3">
          <Sparkles class="w-4 h-4 text-primary" />
          <span>Hand-picked highlights, zero spam.</span>
        </div>
      </div>

      <AlertDialogFooter class="flex flex-col sm:flex-row gap-3 pt-4">
        <AlertDialogCancel
          class="flex-1"
          @click="handleDecline"
        >
          Maybe later
        </AlertDialogCancel>
        <AlertDialogAction
          class="flex-1 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground border border-primary/30 shadow-[0_15px_40px_rgba(79,70,229,0.35)] disabled:opacity-70 disabled:cursor-not-allowed"
          :disabled="isRequesting"
          @click="handleAccept"
        >
          {{ isRequesting ? 'Enabling...' : 'Enable alerts' }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Bell, ShieldCheck, Sparkles } from 'lucide-vue-next'
import { useThemeStore } from '@/stores/theme'
import { useAuthStore } from '@/stores/auth'
import { usePushNotifications } from '@/composables/usePushNotifications'

const CONSENT_KEY = 'notification_consent_user'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const pushNotifications = usePushNotifications()

const showConsent = ref(false)
const isRequesting = ref(false)
let promptTimeout = null

const getUserConsentKey = () => {
  if (!authStore.user?.id) return null
  return `${CONSENT_KEY}_${authStore.user.id}`
}

const markConsentHandled = () => {
  const key = getUserConsentKey()
  if (key) {
    localStorage.setItem(key, 'true')
  }
  showConsent.value = false
}

const hasSeenConsent = () => {
  const key = getUserConsentKey()
  if (!key) return true
  return localStorage.getItem(key) === 'true'
}

const evaluatePrompt = async () => {
  if (typeof window === 'undefined' || !('Notification' in window)) {
    return
  }

  if (!authStore.isAuthenticated || !authStore.user?.id) {
    return
  }

  if (hasSeenConsent()) {
    return
  }

  try {
    const supported = await pushNotifications.initialize(authStore)
    if (!supported) {
      markConsentHandled()
      return
    }

    const alreadySubscribed = await pushNotifications.checkSubscription(authStore)
    if (alreadySubscribed) {
      markConsentHandled()
      return
    }
  } catch (error) {
    console.error('[NotificationConsent] Failed to prepare push notifications:', error)
  }

  const permission = Notification.permission

  if (permission === 'default') {
    showConsent.value = true
  } else {
    markConsentHandled()
  }
}

const queuePrompt = () => {
  if (promptTimeout) {
    clearTimeout(promptTimeout)
  }

  promptTimeout = setTimeout(async () => {
    await evaluatePrompt()
    promptTimeout = null
  }, 1000)
}

const handleSuccess = async () => {
  if (authStore.isAuthenticated) {
    try {
      await pushNotifications.checkSubscription(authStore)
    } catch (error) {
      console.error('[NotificationConsent] Failed to refresh subscription status:', error)
    }
  }
  markConsentHandled()
}

const handleAccept = async () => {
  if (isRequesting.value) return

  isRequesting.value = true

  try {
    if (authStore.isAuthenticated) {
      await pushNotifications.initialize(authStore)
      await pushNotifications.subscribe(authStore)
    } else {
      const permission = await Notification.requestPermission()
      if (permission !== 'granted') {
        throw new Error('Permission denied')
      }
    }

    await handleSuccess()
  } catch (error) {
    console.error('[NotificationConsent] Permission request failed:', error)
    await handleSuccess()
  } finally {
    isRequesting.value = false
  }
}

const handleDecline = () => {
  markConsentHandled()
}

const handleDialogToggle = (value) => {
  if (!value && showConsent.value) {
    handleDecline()
  } else {
    showConsent.value = value
  }
}

watch(
  () => (authStore.isAuthenticated && authStore.user?.id) ? authStore.user.id : null,
  (userId) => {
    if (userId) {
      queuePrompt()
    } else {
      if (promptTimeout) {
        clearTimeout(promptTimeout)
        promptTimeout = null
      }
      showConsent.value = false
    }
  },
  { immediate: true }
)

onMounted(() => {
  if (authStore.isAuthenticated && authStore.user?.id) {
    queuePrompt()
  }
})

onBeforeUnmount(() => {
  if (promptTimeout) {
    clearTimeout(promptTimeout)
  }
})
</script>

