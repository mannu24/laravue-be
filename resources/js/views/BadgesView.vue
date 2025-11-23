<!--
  BadgesView
  Purpose: Full list of all badges with filters
-->
<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Badges</h1>
      <p class="mt-2 text-gray-600 dark:text-white/80">
        Collect badges by completing achievements and leveling up
      </p>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-3">
      <!-- Status Filter -->
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-700 dark:text-white/70">Status:</span>
        <button
          v-for="status in statusFilters"
          :key="status.id"
          @click="activeStatusFilter = status.id"
          class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
          :class="{
            'bg-blue-500 text-white shadow-lg shadow-blue-500/30': activeStatusFilter === status.id,
            'bg-gray-200 dark:bg-gray-900 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-gray-800': activeStatusFilter !== status.id
          }"
        >
          {{ status.label }}
        </button>
      </div>

      <!-- Type Filter -->
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-700 dark:text-white/70">Type:</span>
        <button
          v-for="type in typeFilters"
          :key="type.id"
          @click="activeTypeFilter = type.id"
          class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
          :class="{
            'bg-purple-500 text-white shadow-lg shadow-purple-500/30': activeTypeFilter === type.id,
            'bg-gray-200 dark:bg-gray-900 text-gray-700 dark:text-white/70 hover:bg-gray-300 dark:hover:bg-gray-800': activeTypeFilter !== type.id
          }"
        >
          {{ type.label }}
        </button>
      </div>
    </div>

    <!-- Badges List -->
    <div v-if="globalDataStore.loading" class="relative z-10 text-center py-12">
      <LoadingSpinner />
    </div>
    <div v-else class="relative z-10">
      <BadgeList
        :badges="filteredBadges"
        ui="grid"
        @view-badge="handleViewBadge"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGlobalDataStore } from '@/stores/globalData'
import BadgeList from '@/components/gamification/BadgeList.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const router = useRouter()
const globalDataStore = useGlobalDataStore()

// Filters
const activeStatusFilter = ref('all') // all, earned, unearned
const activeTypeFilter = ref('all') // all, quality, contribution, rare, event

const statusFilters = [
  { id: 'all', label: 'All' },
  { id: 'earned', label: 'Earned' },
  { id: 'unearned', label: 'Unearned' }
]

const typeFilters = [
  { id: 'all', label: 'All' },
  { id: 'quality', label: 'Quality' },
  { id: 'contribution', label: 'Contribution' },
  { id: 'rare', label: 'Rare' },
  { id: 'event', label: 'Event' }
]

// Get all badges and earned badges from global data store
const allBadges = computed(() => globalDataStore.allBadges)
const earnedBadges = computed(() => globalDataStore.earnedBadges)

// Create a map of earned badge IDs for quick lookup
const earnedBadgeIds = computed(() => {
  return new Set(earnedBadges.value.map(badge => badge.id))
})

// Filter badges based on active filters
const filteredBadges = computed(() => {
  let badges = [...allBadges.value]

  // Filter by status
  if (activeStatusFilter.value === 'earned') {
    badges = badges.filter(badge => earnedBadgeIds.value.has(badge.id))
  } else if (activeStatusFilter.value === 'unearned') {
    badges = badges.filter(badge => !earnedBadgeIds.value.has(badge.id))
  }

  // Filter by type
  if (activeTypeFilter.value !== 'all') {
    badges = badges.filter(badge => badge.type === activeTypeFilter.value)
  }

  // Add awarded_at property based on earned status
  badges = badges.map(badge => ({
    ...badge,
    awarded_at: earnedBadgeIds.value.has(badge.id) ? earnedBadges.value.find(eb => eb.id === badge.id)?.awarded_at : null
  }))

  return badges
})

onMounted(async () => {
  try {
    // Fetch global data if not already loaded
    if (!globalDataStore.payload) {
      await globalDataStore.fetchGlobalData({ force: true })
    }
  } catch (error) {
    console.error('Error loading badges:', error)
  }
})

const handleViewBadge = (badgeSlug) => {
  router.push(`/badges/${badgeSlug}`)
}
</script>
