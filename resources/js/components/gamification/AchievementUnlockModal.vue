<!--
  AchievementUnlockModal Component
  Purpose: Modal showing newly unlocked badge with share button
  Props: visible (boolean), badge (Object with badge data)
  Emits: close
-->
<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="visible"
        ref="modalRef"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        @click.self="handleClose"
        @keydown.esc="handleClose"
        role="dialog"
        aria-modal="true"
        aria-labelledby="achievement-title"
        data-test="achievement-unlock-modal"
      >
        <div class="achievement-content">
          <div class="text-center relative z-10">
            <!-- Title -->
            <div class="mb-6">
              <div class="text-7xl mb-4 animate-burst">🏆</div>
              <h2
                id="achievement-title"
                class="text-3xl font-bold text-gray-900 dark:text-white mb-2 animate-pop"
              >
                Achievement Unlocked!
              </h2>
            </div>

            <!-- Badge Display with Animation -->
            <div
              v-if="badge"
              ref="badgeContainerRef"
              class="badge-container mb-6"
            >
              <div class="golden-ring"></div>
              <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl p-8 relative overflow-hidden">
                <div class="burst-particles"></div>
                <div
                  v-if="badge.icon_path"
                  class="w-24 h-24 mx-auto mb-4 rounded-full bg-white/20 flex items-center justify-center animate-badge-pop"
                >
                  <img
                    :src="badge.icon_path"
                    :alt="badge.name"
                    class="w-20 h-20 object-contain"
                  />
                </div>
                <div
                  v-else
                  class="w-24 h-24 mx-auto mb-4 rounded-full bg-white/20 flex items-center justify-center animate-badge-pop"
                >
                  <span class="text-5xl text-white font-bold">
                    {{ badge.name?.charAt(0).toUpperCase() || '?' }}
                  </span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">{{ badge.name }}</h3>
                <p class="text-sm text-white/90">{{ badge.description }}</p>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 animate-fade-in-delay">
              <button
                type="button"
                @click="handleShare"
                class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl animate-pulse-hover"
                data-test="share-button"
              >
                <span>📤</span>
                Share Achievement
              </button>
              <button
                type="button"
                @click="handleClose"
                class="w-full px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
              >
                Continue
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { useAnimation } from '@/composables/useAnimation'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  badge: {
    type: Object,
    default: () => ({
      id: null,
      name: '',
      slug: '',
      description: '',
      icon_path: null
    })
  }
})

const emit = defineEmits(['close'])

const modalRef = ref(null)
const badgeContainerRef = ref(null)
const { playBadgeUnlockSequence, playParticleBurst } = useAnimation()

const handleClose = () => {
  emit('close')
}

const handleShare = () => {
  if (navigator.share) {
    navigator.share({
      title: `I just unlocked the ${props.badge.name} badge!`,
      text: props.badge.description,
      url: window.location.href
    }).catch(() => {
      // User cancelled or error
    })
  } else {
    // Fallback: Copy to clipboard
    const text = `I just unlocked the ${props.badge.name} badge! ${props.badge.description}`
    navigator.clipboard.writeText(text).then(() => {
      // TODO: Show toast notification
    })
  }
}

watch(() => props.visible, async (newVal) => {
  if (newVal) {
    await nextTick()
    if (badgeContainerRef.value) {
      playBadgeUnlockSequence(badgeContainerRef.value)
      // Additional burst effect
      setTimeout(() => {
        playParticleBurst(badgeContainerRef.value, 30)
      }, 300)
    }
  }
})
</script>

<style scoped>
.achievement-content {
  background: white;
  dark:bg-gray-800;
  border-radius: 1.5rem;
  padding: 2rem;
  max-width: 28rem;
  width: 100%;
  margin: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  position: relative;
  z-index: 10;
}

.badge-container {
  position: relative;
  display: inline-block;
}

.golden-ring {
  position: absolute;
  inset: -15px;
  border: 3px solid #fbbf24;
  border-radius: 1.5rem;
  opacity: 0.8;
  filter: blur(8px);
  animation: ring-glow 2s ease-in-out infinite;
  z-index: -1;
}

.burst-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

/* Animations */
@keyframes burst {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes pop {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  70% {
    transform: scale(1.15);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes badge-pop {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  60% {
    transform: scale(1.15) rotate(10deg);
  }
  80% {
    transform: scale(0.95) rotate(-5deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

@keyframes ring-glow {
  0%, 100% {
    opacity: 0.6;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.05);
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-burst {
  animation: burst 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.animate-pop {
  animation: pop 0.6s ease-out;
}

.animate-badge-pop {
  animation: badge-pop 1s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.animate-fade-in-delay {
  animation: fade-in 0.6s ease-out 0.4s both;
}

.animate-pulse-hover:hover {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .animate-burst,
  .animate-pop,
  .animate-badge-pop,
  .animate-fade-in-delay,
  .animate-pulse-hover,
  .golden-ring {
    animation: none !important;
  }
  
  .golden-ring {
    opacity: 0.5;
  }
}
</style>
