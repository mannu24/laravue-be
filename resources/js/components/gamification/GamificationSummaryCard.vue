<!--
  GamificationSummaryCard Component
  Purpose: Compact card showing XP, level, badges count, and tasks completed for dashboard
  Props: summary (Object with xp_total, level, badges_count, tasks_completed)
  Emits: open-profile
-->
<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 cursor-pointer hover:shadow-lg transition-all"
    @click="handleClick"
    data-test="gamification-summary-card"
  >
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Your Progress
      </h3>
      <button
        type="button"
        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium"
        @click.stop="handleClick"
      >
        View Profile →
      </button>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ summary.xp_total || 0 }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Total XP</div>
      </div>
      <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ summary.level?.name || 'Level 1' }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Current Level</div>
      </div>
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
      <div class="text-center flex-1">
        <div class="text-xl font-bold text-gray-900 dark:text-white">
          {{ summary.badges_count || 0 }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Badges</div>
      </div>
      <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
      <div class="text-center flex-1">
        <div class="text-xl font-bold text-gray-900 dark:text-white">
          {{ summary.tasks_completed || 0 }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Tasks Done</div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      xp_total: 0,
      level: null,
      badges_count: 0,
      tasks_completed: 0
    })
  }
})

const emit = defineEmits(['open-profile'])

const handleClick = () => {
  emit('open-profile')
}
</script>

<style scoped>
/* Card styles handled by Tailwind */
</style>

