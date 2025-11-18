<!--
  BadgeItem Component
  Purpose: Displays individual badge with icon, name, and awarded status
  Props: badge (Object with id, name, slug, description, icon_path, awarded_at)
  Emits: click (badge object)
-->
<template>
  <div
    ref="badgeRef"
    class="badge-item bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 cursor-pointer transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
    :class="{
      'ring-2 ring-blue-500 dark:ring-blue-400': badge.awarded_at,
      'rare-badge': isRareBadge,
      'hover-scale': true
    }"
    @click="handleClick"
    @mouseenter="handleHover"
    data-test="badge-item"
  >
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 relative">
        <div
          v-if="badge.icon_path"
          class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center badge-icon"
        >
          <img
            :src="badge.icon_path"
            :alt="badge.name"
            class="w-8 h-8 object-contain"
          />
        </div>
        <div
          v-else
          class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center badge-icon"
        >
          <span class="text-white text-xl font-bold">
            {{ badge.name?.charAt(0).toUpperCase() || '?' }}
          </span>
        </div>
        <div v-if="isRareBadge" class="rare-glow"></div>
      </div>
      <div class="flex-1 min-w-0">
        <h4 class="font-semibold text-gray-900 dark:text-white truncate mb-1">
          {{ badge.name }}
        </h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-2">
          {{ badge.description }}
        </p>
        <div v-if="badge.awarded_at" class="flex items-center gap-1">
          <span class="text-xs text-green-600 dark:text-green-400 font-medium">
            ✓ Awarded
          </span>
          <span class="text-xs text-gray-400 dark:text-gray-500">
            {{ formatDate(badge.awarded_at) }}
          </span>
        </div>
        <div v-else class="text-xs text-gray-400 dark:text-gray-500">
          Not earned yet
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  badge: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      name: '',
      slug: '',
      description: '',
      icon_path: null,
      awarded_at: null,
      type: 'participation'
    })
  }
})

const emit = defineEmits(['click'])

const badgeRef = ref(null)

const isRareBadge = computed(() => {
  return props.badge.type === 'rare' || props.badge.type === 'event'
})

const handleClick = () => {
  emit('click', props.badge)
}

const handleHover = () => {
  if (badgeRef.value && isRareBadge.value) {
    badgeRef.value.classList.add('rare-hover')
    setTimeout(() => {
      if (badgeRef.value) {
        badgeRef.value.classList.remove('rare-hover')
      }
    }, 500)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.badge-item {
  position: relative;
}

.hover-scale:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.badge-icon {
  transition: transform 0.3s ease;
}

.badge-item:hover .badge-icon {
  transform: scale(1.1) rotate(5deg);
}

.rare-badge {
  border-color: #fbbf24;
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.05) 0%, rgba(251, 191, 36, 0.02) 100%);
}

.rare-glow {
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(251, 191, 36, 0.4) 0%, transparent 70%);
  opacity: 0;
  animation: rare-glow-pulse 3s ease-in-out infinite;
  pointer-events: none;
  z-index: -1;
}

.rare-hover .rare-glow {
  opacity: 1;
  animation: rare-glow-intense 0.5s ease-out;
}

@keyframes rare-glow-pulse {
  0%, 100% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.1);
  }
}

@keyframes rare-glow-intense {
  0% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 1;
    transform: scale(1.2);
  }
  100% {
    opacity: 0.6;
    transform: scale(1.1);
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .hover-scale:hover {
    transform: none;
  }
  
  .badge-item:hover .badge-icon {
    transform: none;
  }
  
  .rare-glow {
    animation: none !important;
    opacity: 0.2;
  }
  
  .rare-hover .rare-glow {
    animation: none !important;
  }
}
</style>
