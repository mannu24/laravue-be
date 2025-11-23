/**
 * Global Data Store
 * Fetches aggregated dashboard data in a single request
 */
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useGlobalDataStore = defineStore('globalData', () => {
  const loading = ref(false)
  const error = ref(null)
  const payload = ref(null)
  const lastFetchedAt = ref(null)

  const gamificationSummary = computed(() => {
    return payload.value?.gamification?.summary ?? {
      xp_total: 0,
      badges_count: 0,
      tasks_completed: 0,
      streak_days: 0,
    }
  })

  const xpSummary = computed(() => payload.value?.xp_summary ?? null)

  const xpProgress = computed(() => {
    const summary = xpSummary.value
    if (!summary) {
      return {
        xp_current: 0,
        xp_needed_for_next: 100,
        current_level: null,
        next_level: null,
      }
    }

    const xpCurrent = summary.total_xp ?? 0
    const rawRequirement = summary.next_level?.xp_required ?? xpCurrent
    const nextLevelRequirement = rawRequirement || 100

    return {
      xp_current: xpCurrent,
      xp_needed_for_next: nextLevelRequirement,
      current_level: summary.current_level,
      next_level: summary.next_level,
      xp_to_next_level: summary.xp_to_next_level ?? Math.max(nextLevelRequirement - xpCurrent, 0),
    }
  })

  const allBadges = computed(() => payload.value?.badges?.all ?? [])
  const earnedBadges = computed(() => payload.value?.badges?.earned ?? [])

  const topBadges = computed(() => {
    if (!earnedBadges.value.length && !allBadges.value.length) {
      return []
    }

    const earned = earnedBadges.value.slice(0, 4)
    if (earned.length >= 4) {
      return earned
    }

    const unearned = allBadges.value
      .filter(badge => !earnedBadges.value.find(ub => ub.id === badge.id))
      .sort((a, b) => (b.xp_reward || 0) - (a.xp_reward || 0))
      .slice(0, 4 - earned.length)

    return [...earned, ...unearned]
  })

  const dailyTasks = computed(() => payload.value?.tasks?.daily ?? [])
  const weeklyTasks = computed(() => payload.value?.tasks?.weekly ?? [])

  const user = computed(() => payload.value?.user ?? null)

  const fetchGlobalData = async ({ force = false } = {}) => {
    if (!force && payload.value) {
      return payload.value
    }

    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.global.data())
      payload.value = response.data.data
      lastFetchedAt.value = new Date()
      return payload.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load global data'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clear = () => {
    payload.value = null
    error.value = null
    lastFetchedAt.value = null
  }

  return {
    // State
    loading,
    error,
    payload,
    lastFetchedAt,

    // Getters
    gamificationSummary,
    xpSummary,
    xpProgress,
    allBadges,
    earnedBadges,
    topBadges,
    dailyTasks,
    weeklyTasks,
    user,
    // Actions
    fetchGlobalData,
    clear,
  }
})

