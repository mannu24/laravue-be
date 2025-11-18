<!--
  ProfileStats Component
  Purpose: Show key numeric counters with animated number transitions
  Props: stats (Object with stat values)
-->
<template>
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <TransitionGroup name="stat" tag="div" class="contents">
      <div
        v-for="stat in statsList"
        :key="stat.key"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-center mb-2">
          <span class="text-2xl">{{ stat.icon }}</span>
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
          {{ animatedValue(stat.key) }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ stat.label }}
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      xp_total: 0,
      level: 0,
      badges_count: 0,
      tasks_completed: 0,
      answers_count: 0,
      questions_count: 0
    })
  }
})

const animatedValues = ref({})

const statsList = computed(() => [
  { key: 'xp_total', label: 'Total XP', icon: '⭐' },
  { key: 'level', label: 'Level', icon: '🎯' },
  { key: 'badges_count', label: 'Badges', icon: '🏆' },
  { key: 'tasks_completed', label: 'Tasks', icon: '✅' },
  { key: 'answers_count', label: 'Answers', icon: '💬' },
  { key: 'questions_count', label: 'Questions', icon: '❓' }
])

const animateValue = (key, target) => {
  const start = animatedValues.value[key] || 0
  const duration = 1500
  const startTime = performance.now()

  const animate = (currentTime) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Ease-out function
    const easeOut = 1 - Math.pow(1 - progress, 3)
    animatedValues.value[key] = Math.floor(start + (target - start) * easeOut)

    if (progress < 1) {
      requestAnimationFrame(animate)
    } else {
      animatedValues.value[key] = target
    }
  }

  requestAnimationFrame(animate)
}

const animatedValue = (key) => {
  return animatedValues.value[key] ?? 0
}

watch(() => props.stats, (newStats) => {
  statsList.value.forEach(stat => {
    const value = newStats[stat.key] || 0
    animateValue(stat.key, value)
  })
}, { deep: true, immediate: true })

onMounted(() => {
  // Initialize with 0, then animate to actual values
  statsList.value.forEach(stat => {
    animatedValues.value[stat.key] = 0
    const value = props.stats[stat.key] || 0
    setTimeout(() => {
      animateValue(stat.key, value)
    }, 100)
  })
})
</script>

<style scoped>
.stat-enter-active {
  animation: stat-fade-in 0.5s ease-out;
}

.stat-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

@keyframes stat-fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .stat-enter-active {
    animation: none !important;
  }
  
  .stat-enter-from {
    transform: none !important;
  }
}
</style>

