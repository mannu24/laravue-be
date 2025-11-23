<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 animate-fade-in">Dashboard</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <Transition name="fade-up">
        <div class="lg:col-span-2">
          <GamificationSummaryCard
            :summary="gamificationSummary"
            @open-profile="handleOpenProfile"
          />
        </div>
      </Transition>
      <Transition name="fade-up" appear>
        <div>
          <XpProgressBar
            ref="xpProgressRef"
            :current-xp="xpSummaryData.xp_current || 0"
            :xp-for-next="xpSummaryData.xp_needed_for_next || 100"
            :level-name="xpSummaryData.current_level?.name || 'Level 1'"
            @level-up="handleLevelUp"
          />
        </div>
      </Transition>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Transition name="fade-in" appear>
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Top Badges</h2>
          <div class="badge-carousel">
            <BadgeList
              :badges="topBadges"
              @view-badge="handleViewBadge"
            />
          </div>
        </div>
      </Transition>
      <Transition name="scale-in" appear>
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Today's Tasks</h2>
          <TaskList
            :tasks="dailyTasks"
            @complete-task="handleCompleteTask"
            @assign-task="handleAssignTask"
          />
        </div>
      </Transition>
    </div>

    <!-- Level Up Modal -->
    <LevelUpModal
      :visible="showLevelUpModal"
      :level="newLevel"
      @close="handleCloseLevelUp"
    />

    <!-- Level Card Glow Effect -->
    <div
      v-if="showLevelGlow"
      class="level-glow-overlay"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUserStore } from '@/stores/userStore'
import { useXpStore } from '@/stores/xpStore'
import { useBadgeStore } from '@/stores/badgeStore'
import { useTaskStore } from '@/stores/taskStore'
import GamificationSummaryCard from '@/components/gamification/GamificationSummaryCard.vue'
import XpProgressBar from '@/components/gamification/XpProgressBar.vue'
import BadgeList from '@/components/gamification/BadgeList.vue'
import TaskList from '@/components/gamification/TaskList.vue'
import LevelUpModal from '@/components/gamification/LevelUpModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const userStore = useUserStore()
const xpStore = useXpStore()
const badgeStore = useBadgeStore()
const taskStore = useTaskStore()

const xpProgressRef = ref(null)
const showLevelUpModal = ref(false)
const showLevelGlow = ref(false)
const newLevel = ref(null)

// Use store computed values
const gamificationSummary = computed(() => userStore.gamificationSummary || {
  xp_total: 0,
  level: null,
  badges_count: 0,
  tasks_completed: 0
})

const xpSummaryData = computed(() => xpStore.summary || {
  xp_current: 0,
  xp_needed_for_next: 100,
  current_level: null
})

const topBadges = computed(() => badgeStore.topBadges)
const dailyTasks = computed(() => taskStore.todayTasks)

onMounted(async () => {
  if (!authStore.isAuthenticated || !authStore.user) {
    router.push('/login')
    return
  }

  const userId = authStore.user.id

  try {
    await Promise.all([
      userStore.fetchGamificationSummary(userId),
      xpStore.fetchXpSummary(userId),
      badgeStore.fetchAllBadges(),
      badgeStore.fetchUserBadges(userId),
      taskStore.fetchDailyTasks(userId)
    ])
  } catch (error) {
    console.error('Error loading dashboard:', error)
  }
})

const handleOpenProfile = () => {
  router.push(`/dashboard`)
}

const handleLevelUp = () => {
  // Show level up modal
  newLevel.value = xpSummaryData.value.current_level || {
    name: 'New Level',
    tier: 'beginner'
  }
  showLevelUpModal.value = true
  showLevelGlow.value = true
  
  // Hide glow after modal closes
  setTimeout(() => {
    if (!showLevelUpModal.value) {
      showLevelGlow.value = false
    }
  }, 3000)
}

const handleCloseLevelUp = () => {
  showLevelUpModal.value = false
  setTimeout(() => {
    showLevelGlow.value = false
  }, 500)
}

const handleViewBadge = (badgeSlug) => {
  router.push(`/badges/${badgeSlug}`)
}

const handleCompleteTask = async (taskId) => {
  if (!authStore.user?.id) return
  
  try {
    await taskStore.completeTask(taskId, authStore.user.id)
    // Tasks will be automatically refreshed by the store
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleAssignTask = async (taskId) => {
  if (!authStore.user?.id) return
  
  try {
    await taskStore.assignTask(taskId, authStore.user.id)
    // Tasks will be automatically refreshed by the store
  } catch (error) {
    console.error('Error assigning task:', error)
  }
}
</script>

<style scoped>
.badge-carousel {
  overflow-x: auto;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}

.badge-carousel::-webkit-scrollbar {
  height: 6px;
}

.badge-carousel::-webkit-scrollbar-track {
  background: transparent;
}

.badge-carousel::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.badge-carousel::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

@media (prefers-color-scheme: light) {
  .badge-carousel::-webkit-scrollbar-thumb {
    background: #94a3b8;
  }

  .badge-carousel::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }
}

.level-glow-overlay {
  position: fixed;
  inset: 0;
  pointer-events: none;
  background: radial-gradient(circle at center, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
  z-index: 40;
  animation: glow-fade 3s ease-out;
}

/* Entrance Animations */
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fade-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scale-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes glow-fade {
  0% {
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}

/* Transitions */
.fade-up-enter-active,
.fade-up-leave-active {
  transition: all 0.6s ease-out;
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-up-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.fade-in-enter-active,
.fade-in-leave-active {
  transition: all 0.5s ease-out;
}

.fade-in-enter-from {
  opacity: 0;
}

.fade-in-leave-to {
  opacity: 0;
}

.scale-in-enter-active,
.scale-in-leave-active {
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.scale-in-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.scale-in-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .animate-fade-in,
  .level-glow-overlay {
    animation: none !important;
  }
  
  .fade-up-enter-active,
  .fade-up-leave-active,
  .fade-in-enter-active,
  .fade-in-leave-active,
  .scale-in-enter-active,
  .scale-in-leave-active {
    transition: opacity 0.2s ease !important;
  }
  
  .fade-up-enter-from,
  .fade-up-leave-to,
  .scale-in-enter-from,
  .scale-in-leave-to {
    transform: none !important;
  }
}
</style>
