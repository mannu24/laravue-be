<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else-if="user">
      <!-- Profile Card -->
      <Transition name="fade-in">
        <ProfileCard
          :user="user"
          :social-links="user.social_links || []"
          :gamification-summary="gamificationSummary"
        />
      </Transition>

      <!-- Profile Stats -->
      <Transition name="fade-in" appear>
        <div class="mb-8">
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Statistics</h2>
          <ProfileStats :stats="profileStats" />
        </div>
      </Transition>

      <!-- Level Journey -->
      <Transition name="fade-in" appear>
        <div class="mb-8">
          <LevelJourney
            :current-level="user.level"
            :next-level="nextLevel"
            :levels="allLevels"
            :current-xp="user.xp_total || 0"
          />
        </div>
      </Transition>

      <!-- Badges Section -->
      <Transition name="fade-in" appear>
        <div class="mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Badges</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              {{ userBadges.length }} earned
            </span>
          </div>
          <ProfileBadgeGallery
            :badges="userBadges"
            @view-badge="handleViewBadge"
          />
        </div>
      </Transition>

      <!-- XP History Chart -->
      <Transition name="fade-in" appear>
        <div class="mb-8">
          <XpHistoryChart :xp-logs="xpLogs" />
        </div>
      </Transition>

      <!-- Achievements Timeline -->
      <Transition name="fade-in" appear>
        <div class="mb-8">
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Achievements Timeline</h2>
          <AchievementsTimeline :events="timelineEvents" />
        </div>
      </Transition>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-500 dark:text-gray-400">User not found</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUserStore } from '@/stores/userStore'
import { useBadgeStore } from '@/stores/badgeStore'
import { useXpStore } from '@/stores/xpStore'
import { useLevelStore } from '@/stores/levelStore'
import { useQuestionStore } from '@/stores/questionStore'
import { useAnswerStore } from '@/stores/answerStore'
import ProfileCard from '@/components/profile/ProfileCard.vue'
import ProfileStats from '@/components/profile/ProfileStats.vue'
import ProfileBadgeGallery from '@/components/profile/ProfileBadgeGallery.vue'
import AchievementsTimeline from '@/components/profile/AchievementsTimeline.vue'
import XpHistoryChart from '@/components/profile/XpHistoryChart.vue'
import LevelJourney from '@/components/profile/LevelJourney.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { useGlobalDataStore } from '@/stores/globalData'

// Declare emits to avoid Vue warnings
defineEmits(['share_url'])

const route = useRoute()
const router = useRouter()

const authStore = useAuthStore()
const userStore = useUserStore()
const badgeStore = useBadgeStore()
const xpStore = useXpStore()
const levelStore = useLevelStore()
const questionStore = useQuestionStore()
const answerStore = useAnswerStore()
const globalDataStore = useGlobalDataStore()

const loading = ref(true)
const allLevels = ref([])

// Use store computed values
const user = computed(() => userStore.userProfile)
const userBadges = computed(() => badgeStore.earnedBadges?.value || [])
const xpLogs = computed(() => xpStore.logs?.value || [])
const xpSummary = computed(() => xpStore.summary?.value)
const levelProgress = computed(() => levelStore.levelProgress?.value)
const gamificationSummary = computed(() => {
  // Get from API response if available, otherwise from globalDataStore
  if (user.value?.gamification?.summary) {
    return user.value.gamification.summary
  }
  return globalDataStore.gamificationSummary
})

// Computed properties
const nextLevel = computed(() => {
  if (!user.value?.level || !allLevels.value.length) return null
  
  const currentIndex = allLevels.value.findIndex(l => l.id === user.value.level.id)
  return allLevels.value[currentIndex + 1] || null
})

const profileStats = computed(() => ({
  xp_total: user.value?.xp_total || 0,
  level: user.value?.level?.name || 'Level 1',
  badges_count: (userBadges.value || []).length,
  tasks_completed: 0, // TODO: Get from task store when available
  answers_count: (answerStore.allAnswers?.value || []).length,
  questions_count: (questionStore.allQuestions?.value || []).length
}))

const timelineEvents = computed(() => {
  const events = []

  // XP log events
  (xpLogs.value || []).forEach(log => {
    if (log.event_type === 'answer_verified') {
      events.push({
        id: `xp-${log.id}`,
        type: 'answer_verified',
        timestamp: log.created_at,
        title: 'Verified Answer',
        description: 'Your answer was accepted as verified.',
        data: log
      })
    }
    
    if (log.event_type?.includes('task')) {
      events.push({
        id: `task-${log.id}`,
        type: 'task',
        timestamp: log.created_at,
        title: 'Task Completed',
        description: `Completed ${log.event_type.replace('_', ' ')}`,
        data: log
      })
    }

    // XP milestones (every 100 XP or significant amounts)
    if (log.xp_amount && (log.xp_amount % 100 === 0 || log.xp_amount >= 50)) {
      events.push({
        id: `milestone-${log.id}`,
        type: 'xp',
        timestamp: log.created_at,
        title: `+${log.xp_amount} XP Milestone`,
        description: `Earned ${log.xp_amount} XP for ${log.event_type}`,
        data: log
      })
    }
  })

  // Level-up events
  if (user.value?.level) {
    events.push({
      id: `level-${user.value.level.id}`,
      type: 'level',
      timestamp: user.value.level.updated_at || user.value.created_at,
      title: `Reached ${user.value.level.name}`,
      description: `You progressed to ${user.value.level.tier} tier.`,
      data: user.value.level
    })
  }

  // Badge unlock events
  (userBadges.value || []).forEach(badge => {
    if (badge.awarded_at || badge.pivot?.awarded_at) {
      events.push({
        id: `badge-${badge.id}`,
        type: 'badge',
        timestamp: badge.awarded_at || badge.pivot?.awarded_at,
        title: `Badge Unlocked: ${badge.name}`,
        description: badge.description || 'You earned a new badge!',
        data: badge
      })
    }
  })

  // Sort by timestamp (newest first)
  return events.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
})

onMounted(async () => {
  const username = route.params.username || route.params.id
  
  try {
    await userStore.fetchUserProfile(username)
    // Fetch global data for gamification if not in response
    if (!user.value?.gamification) {
      await globalDataStore.fetchGlobalData()
    }
    // await Promise.all([
    //   badgeStore.fetchUserBadges(userId),
    //   xpStore.fetchXpLogs(userId),
    //   xpStore.fetchXpSummary(userId),
    //   levelStore.fetchLevelProgress(userId),
    //   levelStore.fetchLevels()
    // ])
    
    // allLevels.value = levelStore.allLevels
    loading.value = false
  } catch (error) {
    console.error('Error loading profile:', error)
    loading.value = false
  }
})

const handleViewBadge = (badgeSlug) => {
  router.push(`/badges/${badgeSlug}`)
}
</script>

<style scoped>
/* Fade-in transitions */
.fade-in-enter-active {
  animation: fade-in 0.6s ease-out;
}

.fade-in-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

@keyframes fade-in {
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
  .fade-in-enter-active {
    animation: none !important;
  }
  
  .fade-in-enter-from {
    transform: none !important;
  }
}
</style>
