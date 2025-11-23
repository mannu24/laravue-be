<!--
  LevelUpModal Component
  Purpose: Full-screen modal for level-up animation with reward call-to-action
  Props: visible (boolean), level (Object with level data)
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
        aria-labelledby="level-up-title"
        data-test="level-up-modal"
      >
        <GamificationEffects
          type="confetti"
          :intensity="60"
          :trigger="visible"
        />
        
        <div class="level-up-content">
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
            <!-- Celebration Emoji -->
            <div class="mb-6">
              <div class="text-7xl mb-4 animate-celebration">🎉</div>
              <h2
                id="level-up-title"
                class="text-4xl font-bold text-gray-900 dark:text-white mb-2 animate-scale-up"
              >
                Level Up!
              </h2>
              <p class="text-lg text-gray-600 dark:text-gray-400 animate-fade-in">
                You've reached <span class="font-semibold text-blue-600 dark:text-blue-400">{{ level?.name || 'New Level' }}</span>
              </p>
            </div>

            <!-- Level Badge with Glow -->
            <div
              v-if="level"
              ref="levelBadgeRef"
              class="level-badge-container mb-6"
            >
              <div class="level-badge-glow"></div>
              <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="shine-effect"></div>
                <div class="text-5xl font-bold mb-2 animate-level-name">{{ level.name }}</div>
                <div class="text-sm opacity-90 uppercase tracking-wider">{{ level.tier }}</div>
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
import { ref, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@/components/ui/toast'
import GamificationEffects from './GamificationEffects.vue'
import { useAnimation } from '@/composables/useAnimation'
import { useAchievementPopups } from '@/composables/useAchievementPopups'
import { Share2, X } from 'lucide-vue-next'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  level: {
    type: Object,
    default: () => ({
      id: null,
      name: '',
      xp_required: 0,
      tier: ''
    })
  }
})

const emit = defineEmits(['close'])

const router = useRouter()
const modalRef = ref(null)
const levelBadgeRef = ref(null)
const { playGlowEffect, playLevelUpSequence } = useAnimation()
const { clearQueue } = useAchievementPopups()

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
  const shareText = `I just leveled up to ${props.level?.name || 'a new level'}! 🎉`
  const shareUrl = window.location.origin + '/dashboard'
  
  if (navigator.share) {
    navigator.share({
      title: 'Level Up Achievement!',
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
    if (levelBadgeRef.value) {
      playGlowEffect(levelBadgeRef.value, '#3b82f6', 3000)
    }
    if (modalRef.value) {
      playLevelUpSequence(modalRef.value)
    }
  }
})
</script>

<style scoped>
.level-up-content {
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

.dark .level-up-content {
  background: #1f2937;
}

.level-badge-container {
  position: relative;
  display: inline-block;
}

.level-badge-glow {
  position: absolute;
  inset: -10px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899);
  border-radius: 1.5rem;
  opacity: 0.6;
  filter: blur(20px);
  animation: glow-pulse 2s ease-in-out infinite;
  z-index: -1;
}

.shine-effect {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shine 3s infinite;
}

/* Animations */
@keyframes celebration {
  0%, 100% {
    transform: scale(1) rotate(0deg);
  }
  25% {
    transform: scale(1.1) rotate(-5deg);
  }
  50% {
    transform: scale(1.15) rotate(5deg);
  }
  75% {
    transform: scale(1.1) rotate(-3deg);
  }
}

@keyframes scale-up {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
    opacity: 1;
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

@keyframes level-name {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }
  60% {
    transform: scale(1.15);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes glow-pulse {
  0%, 100% {
    opacity: 0.4;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

@keyframes shine {
  0% {
    left: -100%;
  }
  50%, 100% {
    left: 100%;
  }
}

.animate-celebration {
  animation: celebration 1s ease-in-out;
}

.animate-scale-up {
  animation: scale-up 0.6s ease-out;
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out 0.2s both;
}

.animate-fade-in-delay {
  animation: fade-in 0.6s ease-out 0.4s both;
}

.animate-level-name {
  animation: level-name 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
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
  .animate-celebration,
  .animate-scale-up,
  .animate-fade-in,
  .animate-fade-in-delay,
  .animate-level-name,
  .level-badge-glow,
  .shine-effect {
    animation: none !important;
  }
  
  .level-badge-glow {
    opacity: 0.3;
  }
}
</style>
