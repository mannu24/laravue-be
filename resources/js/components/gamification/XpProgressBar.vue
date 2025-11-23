<!--
  XpProgressBar Component
  Purpose: Displays XP progress bar with current XP, next level requirement, and level name
  Props: currentXp (number), xpForNext (number), levelName (string)
  Emits: level-up (when animation completed)
-->
<template>
  <div class="w-full">
    <div class="flex items-center justify-between mb-2">
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">
          {{ levelName || 'Level 1' }}
        </span>
        <span class="text-xs text-gray-600 dark:text-gray-400">
          {{ currentXp }} / {{ xpForNext }} XP
        </span>
      </div>
      <span class="text-xs font-semibold text-gray-700 dark:text-gray-400">
        {{ progressPercent }}%
      </span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden relative">
      <div
        ref="progressBarRef"
        class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-1000 ease-out relative overflow-hidden"
        :style="{ width: `${animatedProgress}%` }"
        :class="{ 'milestone-glow': isNearMilestone }"
        data-test="xp-progress-bar"
      >
        <div class="progress-shine"></div>
        <div v-if="isNearMilestone" class="milestone-sparkle"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, onMounted, ref } from 'vue'

const props = defineProps({
  currentXp: {
    type: Number,
    default: 0
  },
  xpForNext: {
    type: Number,
    default: 100
  },
  levelName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['level-up'])

const progressBarRef = ref(null)
const animatedProgress = ref(0)

const progressPercent = computed(() => {
  if (props.xpForNext === 0) return 100
  const percent = Math.min(100, Math.round((props.currentXp / props.xpForNext) * 100))
  return percent
})

const isNearMilestone = computed(() => {
  return progressPercent.value >= 90 && progressPercent.value < 100
})

// Animate progress bar on mount and when currentXp changes
const animateProgress = () => {
  const target = progressPercent.value
  const start = animatedProgress.value
  const duration = 1000
  const startTime = performance.now()

  const animate = (currentTime) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Ease-out function
    const easeOut = 1 - Math.pow(1 - progress, 3)
    animatedProgress.value = start + (target - start) * easeOut

    if (progress < 1) {
      requestAnimationFrame(animate)
    } else {
      animatedProgress.value = target
    }
  }

  requestAnimationFrame(animate)
}

// Watch for level up (when progress reaches 100%)
watch(progressPercent, (newPercent, oldPercent) => {
  if (newPercent === 100 && oldPercent < 100) {
    // Small delay for animation to complete
    setTimeout(() => {
      emit('level-up')
    }, 1200)
  }
  
  // Animate to new progress
  animateProgress()
})

// Watch for milestone (90%+)
watch(isNearMilestone, (isNear) => {
  if (isNear && progressBarRef.value) {
    // Add color shift effect
    progressBarRef.value.classList.add('milestone-active')
  }
})

onMounted(() => {
  // Animate from 0 to current progress on mount
  animatedProgress.value = 0
  setTimeout(() => {
    animateProgress()
  }, 100)
})
</script>

<style scoped>
.progress-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.4),
    transparent
  );
  animation: progress-shine 3s infinite;
}

.milestone-glow {
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.6), 0 0 20px rgba(139, 92, 246, 0.4);
  animation: milestone-pulse 2s ease-in-out infinite;
}

.milestone-active {
  background: linear-gradient(to right, #3b82f6, #8b5cf6, #ec4899) !important;
}

.milestone-sparkle {
  position: absolute;
  top: 0;
  right: 0;
  width: 20px;
  height: 100%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.8) 0%, transparent 70%);
  animation: sparkle-twinkle 1.5s ease-in-out infinite;
}

@keyframes progress-shine {
  0% {
    left: -100%;
  }
  50%, 100% {
    left: 100%;
  }
}

@keyframes milestone-pulse {
  0%, 100% {
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.6), 0 0 20px rgba(139, 92, 246, 0.4);
  }
  50% {
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.8), 0 0 30px rgba(139, 92, 246, 0.6);
  }
}

@keyframes sparkle-twinkle {
  0%, 100% {
    opacity: 0.5;
    transform: scaleX(1);
  }
  50% {
    opacity: 1;
    transform: scaleX(1.2);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .progress-shine,
  .milestone-glow,
  .milestone-sparkle {
    animation: none !important;
  }
  
  .milestone-glow {
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.4);
  }
}
</style>
