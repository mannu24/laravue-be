<!--
  LevelsView
  Purpose: Shows all levels and user's current progress
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Levels</h1>
    
    <div v-if="loading" class="text-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else>
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Your Progress</h2>
        <LevelCard
          v-if="currentLevel"
          :level="currentLevel"
          :progress="levelProgressData.progress_percent || 0"
        />
      </div>

      <div>
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">All Levels</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <LevelCard
            v-for="level in allLevels"
            :key="level.id"
            :level="level"
            :progress="calculateProgress(level)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useLevelStore } from '@/stores/levelStore'
import { useUserStore } from '@/stores/userStore'
import LevelCard from '@/components/gamification/LevelCard.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()
const levelStore = useLevelStore()
const userStore = useUserStore()

const loading = ref(true)
const userXp = ref(0)

// Use store computed values
const allLevels = computed(() => levelStore.allLevels)
const currentLevel = computed(() => levelStore.userCurrentLevel)
const levelProgressData = computed(() => levelStore.levelProgress || { progress_percent: 0 })

onMounted(async () => {
  try {
    await levelStore.fetchLevels()
    
    // Fetch user profile to get XP if authenticated
    if (authStore.isAuthenticated && authStore.user?.id) {
      await Promise.all([
        levelStore.fetchLevelProgress(authStore.user.id),
        userStore.fetchUserProfile(authStore.user.id)
      ])
      userXp.value = userStore.userProfile?.xp_total || 0
    }
    
    loading.value = false
  } catch (error) {
    console.error('Error loading levels:', error)
    loading.value = false
  }
})

const calculateProgress = (level) => {
  // Calculate progress for each level based on user's XP
  const nextLevel = allLevels.value.find(l => l.xp_required > level.xp_required)
  if (!nextLevel) return 100
  
  const range = nextLevel.xp_required - level.xp_required
  const progress = ((userXp.value - level.xp_required) / range) * 100
  return Math.min(100, Math.max(0, progress))
}
</script>

<style scoped>
/* Levels view styles handled by Tailwind */
</style>
