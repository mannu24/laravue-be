<template>
  <Transition name="slide-up">
    <div
      v-if="showConsent"
      class="fixed bottom-0 left-0 right-0 z-50 p-4"
      :class="themeStore.isDark ? 'bg-gray-900/95 backdrop-blur-sm' : 'bg-white/95 backdrop-blur-sm'"
    >
      <div class="container mx-auto max-w-6xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-2xl border shadow-2xl"
             :class="themeStore.isDark 
               ? 'bg-gradient-to-br from-gray-900/95 via-gray-900/90 to-gray-950/95 border-white/10 shadow-[0_20px_60px_rgba(15,23,42,0.7)]' 
               : 'bg-white/95 border-white/60 shadow-[0_30px_60px_rgba(15,23,42,0.15)]'">
          <div class="flex items-start gap-4 flex-1">
            <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg"
                 :class="themeStore.isDark 
                   ? 'bg-gradient-to-br from-amber-400/30 via-amber-300/30 to-orange-400/40' 
                   : 'bg-gradient-to-br from-amber-100 via-amber-50 to-orange-100'">
              <Cookie class="w-6 h-6" :class="themeStore.isDark ? 'text-amber-200' : 'text-amber-500'" />
            </div>
            <div class="flex-1">
              <h3 class="text-base font-semibold mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                Cookies keep things smooth 🍪
              </h3>
              <p class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
                We use cookies to personalize content, remember your preferences, and analyze performance. You can review our usage any time in the privacy settings.
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3 flex-shrink-0">
            <Button
              @click="handleDecline"
              variant="outline"
              size="sm"
              :class="themeStore.isDark 
                ? 'border-white/10 text-gray-300 hover:bg-white/5 hover:text-white' 
                : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
            >
              Manage Later
            </Button>
            <Button
              @click="handleAccept"
              size="sm"
              class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground px-6 py-3 rounded-full font-semibold shadow-[0_18px_40px_rgba(79,70,229,0.35)] border border-primary/20 transition-all duration-300"
            >
              <ShieldCheck class="w-4 h-4 mr-2" />
              Accept cookies
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
import { Button } from '@/components/ui/button'
import { Cookie, ShieldCheck } from 'lucide-vue-next'

const CONSENT_KEY = 'cookie_consent_status'

const themeStore = useThemeStore()
const showConsent = ref(false)

const shouldShowConsent = () => {
  if (typeof window === 'undefined') {
    return false
  }

  const status = localStorage.getItem(CONSENT_KEY)
  return !status
}

const handleAccept = () => {
  localStorage.setItem(CONSENT_KEY, 'accepted')
  showConsent.value = false
}

const handleDecline = () => {
  localStorage.setItem(CONSENT_KEY, 'dismissed')
  showConsent.value = false
}

onMounted(() => {
  setTimeout(() => {
    if (shouldShowConsent()) {
      showConsent.value = true
    }
  }, 800)
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

