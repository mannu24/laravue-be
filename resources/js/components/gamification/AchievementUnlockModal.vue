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
        @click.self="handleCloseWithClear"
        @keydown.esc="handleCloseWithClear"
        role="dialog"
        aria-modal="true"
        aria-labelledby="achievement-title"
        data-test="achievement-unlock-modal"
      >
        <div class="achievement-content">
          <!-- Close Button -->
          <button
            type="button"
            @click="handleCloseWithClear"
            class="absolute top-4 right-4 z-20 p-2 rounded-full bg-white/10 dark:bg-gray-800/50 hover:bg-white/20 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-300 transition-colors"
            aria-label="Close modal"
          >
            <X class="w-5 h-5" />
          </button>
          
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
              <div 
                class="badge-type-ring"
                :class="badgeRingClass"
              ></div>
              <div 
                class="rounded-2xl p-8 relative overflow-hidden"
                :class="badgeGradientClass"
              >
                <div class="burst-particles"></div>
                <img
                  v-if="badge.icon_path"
                  :src="badge.icon_path"
                  :alt="badge.name"
                  class="w-[80px] h-[80px] mx-auto mb-2 object-contain"
                  :class="badgeImageShadowClass"
                />
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
            <div class="flex gap-3 animate-fade-in-delay">
              <button
                type="button"
                @click="handleGoToProfile"
                class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
              >
                Go to Profile
              </button>
              <button
                type="button"
                @click="handleShare"
                class="flex-1 flex items-center justify-center gap-3 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl"
                data-test="share-button"
              >
                <Share2 class="w-5 h-5" />
                Share
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@/components/ui/toast'
import { useAnimation } from '@/composables/useAnimation'
import { useAchievementPopups } from '@/composables/useAchievementPopups'
import { Share2, X } from 'lucide-vue-next'

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

const router = useRouter()
const modalRef = ref(null)
const badgeContainerRef = ref(null)
const { playBadgeUnlockSequence, playParticleBurst } = useAnimation()
const { clearQueue } = useAchievementPopups()

// Get badge type (default to 'participation' if not set)
const badgeType = computed(() => {
  return props.badge?.type || 'participation'
})

// Badge gradient classes based on type
const badgeGradientClass = computed(() => {
  const type = badgeType.value
  const gradients = {
    quality: 'bg-gradient-to-br from-blue-500 to-cyan-500',
    contribution: 'bg-gradient-to-br from-green-500 to-emerald-500',
    rare: 'bg-gradient-to-br from-red-500 to-rose-500',
    event: 'bg-gradient-to-br from-purple-500 to-violet-500',
    participation: 'bg-gradient-to-br from-yellow-400 to-amber-500',
    consistency: 'bg-gradient-to-br from-green-700 to-emerald-800'
  }
  return gradients[type] || gradients.participation
})

// Badge ring glow classes based on type
const badgeRingClass = computed(() => {
  const type = badgeType.value
  const rings = {
    quality: 'border-blue-500',
    contribution: 'border-green-500',
    rare: 'border-red-500',
    event: 'border-purple-500',
    participation: 'border-yellow-400',
    consistency: 'border-green-700'
  }
  return rings[type] || rings.participation
})

// Badge image shadow classes based on type
const badgeImageShadowClass = computed(() => {
  const type = badgeType.value
  const shadows = {
    quality: 'drop-shadow-[0_8px_16px_rgba(59,130,246,0.6)]',
    contribution: 'drop-shadow-[0_8px_16px_rgba(34,197,94,0.6)]',
    rare: 'drop-shadow-[0_8px_16px_rgba(239,68,68,0.6)]',
    event: 'drop-shadow-[0_8px_16px_rgba(168,85,247,0.6)]',
    participation: 'drop-shadow-[0_8px_16px_rgba(234,179,8,0.6)]',
    consistency: 'drop-shadow-[0_8px_16px_rgba(21,128,61,0.6)]'
  }
  return shadows[type] || shadows.participation
})

const handleClose = () => {
  emit('close')
}

const handleCloseWithClear = () => {
  // Clear the queue when closing to prevent popup from showing again
  clearQueue()
  handleClose()
}

const handleGoToProfile = () => {
  // Clear the queue when navigating away to prevent popup from showing again
  clearQueue()
  handleClose()
  router.push('/dashboard')
}

const handleShare = () => {
  const shareText = `I just unlocked the ${props.badge.name} badge! 🏆`
  const shareUrl = window.location.origin + '/dashboard'
  
  if (navigator.share) {
    navigator.share({
      title: 'Badge Unlocked!',
      text: shareText,
      url: shareUrl
    }).then(() => {
      toast({
        title: 'Shared successfully!',
        description: 'Your achievement has been shared.',
      })
    }).catch((error) => {
      if (error.name !== 'AbortError') {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(`${shareText} ${shareUrl}`).then(() => {
          toast({
            title: 'Link Copied!',
            description: 'Achievement link copied to clipboard',
          })
        })
      }
    })
  } else {
    // Fallback: Copy to clipboard
    navigator.clipboard.writeText(`${shareText} ${shareUrl}`).then(() => {
      toast({
        title: 'Link Copied!',
        description: 'Achievement link copied to clipboard',
      })
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
  border-radius: 1.5rem;
  padding: 2rem;
  max-width: 28rem;
  width: 100%;
  margin: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  position: relative;
  z-index: 10;
}

.dark .achievement-content {
  background: #1f2937;
}

.badge-container {
  position: relative;
  display: inline-block;
}

.badge-type-ring {
  position: absolute;
  inset: -15px;
  border: 3px solid;
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
