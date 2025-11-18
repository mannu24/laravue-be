<!--
  AchievementsTimeline Component
  Purpose: Show chronological achievements with vertical timeline
  Props: events (Array of event objects)
-->
<template>
  <div class="achievements-timeline relative">
    <TransitionGroup name="timeline" tag="div">
      <div
        v-for="(event, index) in sortedEvents"
        :key="event.id || index"
        class="timeline-item relative pl-8 pb-8"
        :class="{ 'last-item': index === sortedEvents.length - 1 }"
      >
        <!-- Timeline Connector -->
        <div
          v-if="index !== sortedEvents.length - 1"
          class="absolute left-3 top-8 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"
        ></div>

        <!-- Timeline Dot -->
        <div
          :class="dotClasses(event.type)"
          class="absolute left-0 top-1 w-6 h-6 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center z-10"
        >
          <span class="text-xs">{{ eventIcon(event.type) }}</span>
        </div>

        <!-- Event Content -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between gap-4 mb-2">
            <div class="flex-1">
              <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                {{ event.title }}
              </h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                {{ event.description }}
              </p>
              <div class="text-xs text-gray-500 dark:text-gray-500">
                {{ formatDate(event.timestamp) }}
              </div>
            </div>
            <div
              :class="typeBadgeClass(event.type)"
              class="px-2 py-1 text-xs font-medium rounded-full flex-shrink-0"
            >
              {{ eventTypeLabel(event.type) }}
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>

    <!-- Year Dividers (if needed) -->
    <div
      v-for="(year, yearIndex) in yearDividers"
      :key="`year-${year}`"
      class="year-divider"
      :style="{ top: `${yearIndex * 100}px` }"
    >
      <div class="text-sm font-semibold text-gray-400 dark:text-gray-500">
        {{ year }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
})

const sortedEvents = computed(() => {
  return [...props.events].sort((a, b) => {
    return new Date(b.timestamp) - new Date(a.timestamp)
  })
})

const yearDividers = computed(() => {
  const years = new Set()
  props.events.forEach(event => {
    const year = new Date(event.timestamp).getFullYear()
    years.add(year)
  })
  return Array.from(years).sort((a, b) => b - a)
})

const eventIcon = (type) => {
  const icons = {
    level: '🎯',
    badge: '🏆',
    xp: '⭐',
    task: '✅',
    answer_verified: '✓'
  }
  return icons[type] || '📌'
}

const dotClasses = (type) => {
  const classes = {
    level: 'bg-purple-500',
    badge: 'bg-yellow-500',
    xp: 'bg-indigo-500',
    task: 'bg-blue-500',
    answer_verified: 'bg-green-500'
  }
  return classes[type] || 'bg-gray-500'
}

const typeBadgeClass = (type) => {
  const classes = {
    level: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    badge: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    xp: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
    task: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    answer_verified: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  }
  return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
}

const eventTypeLabel = (type) => {
  const labels = {
    level: 'Level Up',
    badge: 'Badge',
    xp: 'XP Milestone',
    task: 'Task',
    answer_verified: 'Verified'
  }
  return labels[type] || 'Event'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) return 'Today'
  if (days === 1) return 'Yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}
</script>

<style scoped>
.achievements-timeline {
  position: relative;
}

.timeline-item {
  position: relative;
}

.last-item {
  padding-bottom: 0;
}

.year-divider {
  position: absolute;
  left: 0;
  padding-left: 2rem;
  margin-top: 1rem;
  margin-bottom: 1rem;
}

/* Timeline animations */
.timeline-enter-active {
  animation: timeline-fade-up 0.5s ease-out;
}

.timeline-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

@keyframes timeline-fade-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .timeline-enter-active {
    animation: none !important;
  }
  
  .timeline-enter-from {
    transform: none !important;
  }
}
</style>

