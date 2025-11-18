<!--
  ProfileBadgeGallery Component
  Purpose: Display all user badges in a responsive grid with filter controls
  Props: badges (Array of badge objects)
-->
<template>
  <div>
    <!-- Filter Controls -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
      <button
        v-for="filter in filters"
        :key="filter.key"
        type="button"
        @click="activeFilter = filter.key"
        :class="filterButtonClass(filter.key)"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- Badge Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <TransitionGroup name="badge" tag="div" class="contents">
        <div
          v-for="badge in filteredBadges"
          :key="badge.id"
          class="badge-gallery-item"
        >
          <BadgeItem
            :badge="badge"
            @click="handleBadgeClick"
          />
        </div>
      </TransitionGroup>
    </div>

    <div v-if="filteredBadges.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
      No badges found for this filter.
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import BadgeItem from '@/components/gamification/BadgeItem.vue'

const props = defineProps({
  badges: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['view-badge'])

const activeFilter = ref('all')

const filters = [
  { key: 'all', label: 'All' },
  { key: 'rare', label: 'Rare' },
  { key: 'quality', label: 'Quality' },
  { key: 'consistency', label: 'Consistency' },
  { key: 'participation', label: 'Participation' }
]

const filteredBadges = computed(() => {
  if (activeFilter.value === 'all') {
    return props.badges
  }
  return props.badges.filter(badge => badge.type === activeFilter.value)
})

const filterButtonClass = (filterKey) => {
  return activeFilter.value === filterKey
    ? 'bg-blue-600 text-white shadow-md'
    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
}

const handleBadgeClick = (badge) => {
  emit('view-badge', badge.slug)
}
</script>

<style scoped>
.badge-enter-active {
  animation: badge-pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.badge-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.badge-gallery-item {
  transition: transform 0.2s ease;
}

.badge-gallery-item:hover {
  transform: translateY(-2px);
}

@keyframes badge-pop-in {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .badge-enter-active {
    animation: none !important;
  }
  
  .badge-enter-from {
    transform: none !important;
  }
  
  .badge-gallery-item:hover {
    transform: none;
  }
}
</style>

