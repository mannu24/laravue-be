<!--
  ProfileHeader Component
  Purpose: Display user avatar, name, bio, join date, streak count, and current level
  Props: user (Object with user data)
-->
<template>
  <div
    ref="headerRef"
    class="profile-header bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm relative overflow-hidden"
    :class="{ 'high-level-glow': isHighLevel }"
  >
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
      <!-- Avatar -->
      <div class="relative flex-shrink-0">
        <div
          class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg relative overflow-hidden"
          :class="{ 'avatar-glow': isHighLevel }"
        >
          <img
            v-if="user.avatar || user.avatar_url"
            :src="user.avatar || user.avatar_url"
            :alt="user.name"
            class="w-full h-full object-cover"
          />
          <span v-else>
            {{ user.name?.charAt(0).toUpperCase() || '?' }}
          </span>
        </div>
        <div
          v-if="isHighLevel"
          class="absolute inset-0 rounded-full particle-overlay"
        ></div>
      </div>

      <!-- User Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ user.name || 'User' }}
          </h1>
          <span
            v-if="user.level"
            class="px-3 py-1 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-semibold rounded-full"
          >
            {{ user.level.name }}
          </span>
        </div>
        
        <p v-if="user.bio" class="text-gray-600 dark:text-gray-400 mb-3">
          {{ user.bio }}
        </p>

        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
          <div v-if="user.email" class="flex items-center gap-1">
            <span>📧</span>
            <span>{{ user.email }}</span>
          </div>
          <div v-if="user.created_at" class="flex items-center gap-1">
            <span>📅</span>
            <span>Joined {{ formatDate(user.created_at) }}</span>
          </div>
          <div v-if="user.streak_days" class="flex items-center gap-1">
            <span>🔥</span>
            <span>{{ user.streak_days }} day streak</span>
          </div>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ user.xp_total || 0 }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Total XP</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ user.badges?.length || 0 }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Badges</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true,
    default: () => ({
      name: '',
      email: '',
      bio: '',
      avatar: null,
      avatar_url: null,
      created_at: null,
      streak_days: 0,
      xp_total: 0,
      level: null,
      badges: []
    })
  }
})

const headerRef = ref(null)

const isHighLevel = computed(() => {
  const tier = props.user.level?.tier || 'beginner'
  return ['advanced', 'expert', 'legend'].includes(tier)
})

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long'
  })
}
</script>

<style scoped>
.high-level-glow {
  position: relative;
}

.high-level-glow::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 0.75rem;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899);
  opacity: 0.3;
  filter: blur(8px);
  z-index: -1;
  animation: glow-pulse 3s ease-in-out infinite;
}

.avatar-glow {
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), 0 0 40px rgba(139, 92, 246, 0.3);
  animation: avatar-pulse 2s ease-in-out infinite;
}

.particle-overlay {
  pointer-events: none;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 20px 20px;
  animation: particle-float 10s linear infinite;
}

@keyframes glow-pulse {
  0%, 100% {
    opacity: 0.2;
  }
  50% {
    opacity: 0.4;
  }
}

@keyframes avatar-pulse {
  0%, 100% {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.5), 0 0 40px rgba(139, 92, 246, 0.3);
  }
  50% {
    box-shadow: 0 0 30px rgba(59, 130, 246, 0.7), 0 0 60px rgba(139, 92, 246, 0.5);
  }
}

@keyframes particle-float {
  0% {
    transform: translateY(0);
    opacity: 0;
  }
  50% {
    opacity: 0.3;
  }
  100% {
    transform: translateY(-100%);
    opacity: 0;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .high-level-glow::before,
  .avatar-glow,
  .particle-overlay {
    animation: none !important;
  }
  
  .high-level-glow::before {
    opacity: 0.2;
  }
  
  .avatar-glow {
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
  }
}
</style>

