<!--
  LevelCard Component
  Purpose: Displays level information with tier badge, progress, and next level gap
  Props: level (Object with id, name, xp_required, tier), progress (number 0-100)
  Emits: none (pure display component)
-->
<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-shadow"
    data-test="level-card"
  >
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
          {{ level?.name || 'Level 1' }}
        </h3>
        <div class="flex items-center gap-2">
          <span
            :class="tierBadgeClass"
            class="px-2 py-1 text-xs font-semibold rounded-full"
            data-test="tier-badge"
          >
            {{ level?.tier || 'beginner' }}
          </span>
        </div>
      </div>
      <div class="text-right">
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ level?.xp_required || 0 }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">XP Required</div>
      </div>
    </div>

    <div class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm text-gray-600 dark:text-gray-400">Progress</span>
        <span class="text-sm font-semibold text-gray-900 dark:text-white">
          {{ progress }}%
        </span>
      </div>
      <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
        <div
          class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-500"
          :style="{ width: `${progress}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  level: {
    type: Object,
    default: () => ({
      id: null,
      name: '',
      xp_required: 0,
      tier: 'beginner'
    })
  },
  progress: {
    type: Number,
    default: 0,
    validator: (value) => value >= 0 && value <= 100
  }
})

const tierBadgeClass = computed(() => {
  const tier = props.level?.tier || 'beginner'
  const classes = {
    beginner: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    intermediate: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    advanced: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    expert: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    legend: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
  }
  return classes[tier] || classes.beginner
})
</script>

<style scoped>
/* Component-specific styles if needed */
</style>

