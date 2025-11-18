<!--
  LevelJourney Component
  Purpose: Show completed and upcoming levels in a horizontal scrollable view
  Props: currentLevel (Object), nextLevel (Object), levels (Array)
-->
<template>
  <div class="level-journey bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Level Journey</h3>
    
    <div class="relative">
      <!-- Scrollable Container -->
      <div class="overflow-x-auto pb-4 -mx-6 px-6 scrollbar-hide">
        <div class="flex gap-4 min-w-max">
          <div
            v-for="(level, index) in displayLevels"
            :key="level.id"
            class="level-journey-item flex-shrink-0"
            :class="getLevelClass(level, index)"
          >
            <!-- Level Circle -->
            <div class="flex flex-col items-center gap-2">
              <div
                :class="levelCircleClass(level, index)"
                class="w-16 h-16 rounded-full flex items-center justify-center border-4 relative transition-all"
              >
                <!-- Checkmark for completed -->
                <span v-if="isCompleted(level)" class="text-2xl">✓</span>
                <!-- Current level indicator -->
                <span v-else-if="isCurrent(level)" class="text-xl font-bold">{{ level.name }}</span>
                <!-- Future level -->
                <span v-else class="text-xl font-bold opacity-50">{{ level.name }}</span>
                
                <!-- Glow effect for current level -->
                <div
                  v-if="isCurrent(level)"
                  class="absolute inset-0 rounded-full level-glow"
                ></div>
              </div>
              
              <!-- Level Info -->
              <div class="text-center min-w-[80px]">
                <div class="text-xs font-semibold text-gray-900 dark:text-white">
                  {{ level.name }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ level.xp_required }} XP
                </div>
              </div>
            </div>

            <!-- Connector Line -->
            <div
              v-if="index < displayLevels.length - 1"
              :class="connectorClass(level, displayLevels[index + 1])"
              class="absolute top-8 left-full w-4 h-0.5 -ml-2"
            ></div>
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mt-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">
            Progress to {{ nextLevel?.name || 'Next Level' }}
          </span>
          <span class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ progressPercent }}%
          </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-1000 ease-out"
            :style="{ width: `${progressPercent}%` }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentLevel: {
    type: Object,
    default: null
  },
  nextLevel: {
    type: Object,
    default: null
  },
  levels: {
    type: Array,
    default: () => []
  },
  currentXp: {
    type: Number,
    default: 0
  }
})

const displayLevels = computed(() => {
  if (props.levels.length === 0) return []
  
  // Show current level and a few before/after
  const currentIndex = props.levels.findIndex(l => l.id === props.currentLevel?.id)
  const start = Math.max(0, currentIndex - 2)
  const end = Math.min(props.levels.length, currentIndex + 5)
  
  return props.levels.slice(start, end)
})

const isCompleted = (level) => {
  if (!props.currentLevel) return false
  return level.xp_required < (props.currentLevel.xp_required || 0)
}

const isCurrent = (level) => {
  if (!props.currentLevel) return false
  return level.id === props.currentLevel.id
}

const isFuture = (level) => {
  if (!props.currentLevel) return true
  return level.xp_required > (props.currentLevel.xp_required || 0)
}

const getLevelClass = (level, index) => {
  return {
    'level-completed': isCompleted(level),
    'level-current': isCurrent(level),
    'level-future': isFuture(level)
  }
}

const levelCircleClass = (level, index) => {
  if (isCompleted(level)) {
    return 'bg-green-500 border-green-600 text-white'
  } else if (isCurrent(level)) {
    return 'bg-purple-500 border-purple-600 text-white'
  } else {
    return 'bg-gray-300 dark:bg-gray-600 border-gray-400 dark:border-gray-500 text-gray-600 dark:text-gray-300'
  }
}

const connectorClass = (currentLevel, nextLevel) => {
  if (isCompleted(currentLevel) && isCompleted(nextLevel)) {
    return 'bg-green-500'
  } else if (isCompleted(currentLevel) || isCurrent(currentLevel)) {
    return 'bg-gradient-to-r from-green-500 to-purple-500'
  } else {
    return 'bg-gray-300 dark:bg-gray-600'
  }
}

const progressPercent = computed(() => {
  if (!props.currentLevel || !props.nextLevel) return 0
  
  const currentXp = props.currentXp || props.currentLevel.xp_required || 0
  const currentLevelXp = props.currentLevel.xp_required || 0
  const nextLevelXp = props.nextLevel.xp_required || 0
  const range = nextLevelXp - currentLevelXp
  
  if (range === 0) return 100
  
  const progress = ((currentXp - currentLevelXp) / range) * 100
  return Math.min(100, Math.max(0, progress))
})
</script>

<style scoped>
.level-journey-item {
  position: relative;
}

.level-glow {
  background: radial-gradient(circle, rgba(168, 85, 247, 0.4) 0%, transparent 70%);
  animation: level-glow-pulse 2s ease-in-out infinite;
  pointer-events: none;
}

@keyframes level-glow-pulse {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.2);
  }
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .level-glow {
    animation: none !important;
    opacity: 0.5;
  }
}
</style>

