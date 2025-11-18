<!--
  ToastItem Component
  Purpose: Individual toast notification with animations and auto-dismiss
  Props: toast (object with id, message, type, duration, data)
-->
<template>
  <div
    :class="toastClasses"
    class="toast-item bg-white dark:bg-gray-800 rounded-lg shadow-lg border p-4 min-w-[300px] pointer-events-auto relative overflow-hidden"
    role="alert"
    :aria-live="type === 'error' ? 'assertive' : 'polite'"
  >
    <!-- Progress bar -->
    <div
      v-if="toast.duration > 0"
      class="absolute top-0 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700"
    >
      <div
        :class="progressBarClasses"
        class="h-full transition-all ease-linear"
        :style="{ width: `${progressPercent}%` }"
      ></div>
    </div>

    <div class="flex items-start gap-3">
      <!-- Icon -->
      <div :class="iconClasses" class="flex-shrink-0 mt-0.5">
        <component :is="iconComponent" class="w-5 h-5" />
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <!-- XP Toast -->
        <div v-if="toast.type === 'xp'" class="flex items-center gap-2">
          <span class="text-lg font-bold" :class="textClasses">
            {{ toast.message }}
          </span>
          <span class="sparkle-emoji">✨</span>
        </div>

        <!-- Level Toast -->
        <div v-else-if="toast.type === 'level'" class="flex items-center gap-2">
          <span class="text-lg font-bold" :class="textClasses">
            {{ toast.message }}
          </span>
          <span class="level-glow-indicator"></span>
        </div>

        <!-- Badge Toast -->
        <div v-else-if="toast.type === 'badge'" class="flex items-center gap-3">
          <div
            v-if="toast.data?.badge?.icon_path"
            class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center badge-icon-pop"
          >
            <img
              :src="toast.data.badge.icon_path"
              :alt="toast.data.badge.name"
              class="w-8 h-8 object-contain"
            />
          </div>
          <div
            v-else
            class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center badge-icon-pop"
          >
            <span class="text-white text-xl font-bold">
              {{ toast.data?.badge?.name?.charAt(0).toUpperCase() || '🏆' }}
            </span>
          </div>
          <span class="text-base font-semibold" :class="textClasses">
            {{ toast.message }}
          </span>
        </div>

        <!-- Standard Toast -->
        <p v-else class="text-sm font-medium" :class="textClasses">
          {{ toast.message }}
        </p>
      </div>

      <!-- Close Button -->
      <button
        type="button"
        @click="handleClose"
        class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1"
        aria-label="Close notification"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, h } from 'vue'

const props = defineProps({
  toast: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const progressPercent = ref(100)
let progressInterval = null

const type = computed(() => props.toast.type)

// Toast styling classes
const toastClasses = computed(() => {
  const base = 'border'
  const variants = {
    success: 'border-green-200 dark:border-green-800',
    error: 'border-red-200 dark:border-red-800',
    warning: 'border-amber-200 dark:border-amber-800',
    info: 'border-blue-200 dark:border-blue-800',
    xp: 'border-indigo-200 dark:border-indigo-800 xp-glow',
    level: 'border-purple-200 dark:border-purple-800 level-pulse',
    badge: 'border-yellow-200 dark:border-yellow-800 badge-pop'
  }
  return `${base} ${variants[type.value] || variants.info}`
})

// Text color classes
const textClasses = computed(() => {
  const variants = {
    success: 'text-green-800 dark:text-green-200',
    error: 'text-red-800 dark:text-red-200',
    warning: 'text-amber-800 dark:text-amber-200',
    info: 'text-blue-800 dark:text-blue-200',
    xp: 'text-indigo-600 dark:text-indigo-400',
    level: 'text-purple-600 dark:text-purple-400',
    badge: 'text-yellow-800 dark:text-yellow-200'
  }
  return variants[type.value] || variants.info
})

// Icon color classes
const iconClasses = computed(() => {
  const variants = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-amber-600 dark:text-amber-400',
    info: 'text-blue-600 dark:text-blue-400',
    xp: 'text-indigo-600 dark:text-indigo-400',
    level: 'text-purple-600 dark:text-purple-400',
    badge: 'text-yellow-600 dark:text-yellow-400'
  }
  return variants[type.value] || variants.info
})

// Progress bar classes
const progressBarClasses = computed(() => {
  const variants = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-amber-500',
    info: 'bg-blue-500',
    xp: 'bg-indigo-500',
    level: 'bg-purple-500',
    badge: 'bg-yellow-500'
  }
  return variants[type.value] || variants.info
})

// Icon component
const iconComponent = computed(() => {
  const icons = {
    success: () => h('svg', {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M5 13l4 4L19 7'
      })
    ]),
    error: () => h('svg', {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M6 18L18 6M6 6l12 12'
      })
    ]),
    warning: () => h('svg', {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
      })
    ]),
    info: () => h('svg', {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]),
    xp: () => h('span', { class: 'text-lg' }, '⭐'),
    level: () => h('span', { class: 'text-lg' }, '🎯'),
    badge: () => h('span', { class: 'text-lg' }, '🏆')
  }
  return icons[type.value] || icons.info
})

const handleClose = () => {
  emit('close', props.toast.id)
}

// Progress bar animation
onMounted(() => {
  if (props.toast.duration > 0) {
    const startTime = Date.now()
    const duration = props.toast.duration
    
    progressInterval = setInterval(() => {
      const elapsed = Date.now() - startTime
      const remaining = Math.max(0, duration - elapsed)
      progressPercent.value = (remaining / duration) * 100
      
      if (remaining <= 0) {
        clearInterval(progressInterval)
      }
    }, 16) // ~60fps
  }
})

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
})
</script>

<style scoped>
/* Toast enter/exit animations */
.toast-enter-active {
  animation: toast-in 0.3s ease-out;
}

.toast-leave-active {
  animation: toast-out 0.2s ease-in;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

@keyframes toast-in {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes toast-out {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(30px);
  }
}

/* XP Toast Sparkle */
.sparkle-emoji {
  display: inline-block;
  animation: sparkle 1.5s ease-in-out infinite;
}

@keyframes sparkle {
  0% {
    opacity: 0;
    transform: scale(0.8) rotate(0deg);
  }
  50% {
    opacity: 1;
    transform: scale(1.1) rotate(30deg);
  }
  100% {
    opacity: 0;
    transform: scale(0.8) rotate(0deg);
  }
}

.xp-glow {
  animation: xp-glow 2s ease-in-out infinite;
}

@keyframes xp-glow {
  0%, 100% {
    box-shadow: 0 0 0px rgba(99, 102, 241, 0.5);
  }
  50% {
    box-shadow: 0 0 12px rgba(99, 102, 241, 0.8);
  }
}

/* Level Toast Pulse */
.level-pulse {
  animation: level-glow 2s ease-in-out infinite;
}

@keyframes level-glow {
  0%, 100% {
    box-shadow: 0 0 0px rgba(147, 51, 234, 0.5);
  }
  50% {
    box-shadow: 0 0 12px rgba(168, 85, 247, 0.8);
  }
}

.level-glow-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #a855f7;
  animation: level-pulse-dot 1.5s ease-in-out infinite;
}

@keyframes level-pulse-dot {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.3);
  }
}

/* Badge Toast Pop */
.badge-pop {
  animation: badge-pop-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes badge-pop-in {
  0% {
    transform: scale(0.6);
  }
  60% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.badge-icon-pop {
  animation: badge-icon-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes badge-icon-pop {
  0% {
    transform: scale(0) rotate(-180deg);
  }
  60% {
    transform: scale(1.15) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .toast-enter-active,
  .toast-leave-active,
  .sparkle-emoji,
  .xp-glow,
  .level-pulse,
  .level-glow-indicator,
  .badge-pop,
  .badge-icon-pop {
    animation: none !important;
  }
  
  .toast-enter-from,
  .toast-leave-to {
    transform: none !important;
  }
}
</style>

