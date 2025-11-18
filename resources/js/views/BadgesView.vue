<!--
  BadgesView
  Purpose: Full list of all badges
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">All Badges</h1>
    
    <div v-if="badgeStore.loading" class="text-center py-12">
      <LoadingSpinner size="lg" />
    </div>

    <BadgeList
      v-else
      :badges="badges"
      @view-badge="handleViewBadge"
    />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBadgeStore } from '@/stores/badgeStore'
import BadgeList from '@/components/gamification/BadgeList.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const router = useRouter()
const badgeStore = useBadgeStore()

// Use store computed values
const badges = computed(() => badgeStore.allBadges)

onMounted(async () => {
  try {
    await badgeStore.fetchAllBadges()
  } catch (error) {
    console.error('Error loading badges:', error)
  }
})

const handleViewBadge = (badgeSlug) => {
  router.push(`/badges/${badgeSlug}`)
}
</script>

<style scoped>
/* Badges view styles handled by Tailwind */
</style>
