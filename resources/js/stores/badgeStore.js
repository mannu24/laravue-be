/**
 * Badge Store
 * Manages badges and user badge data
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useBadgeStore = defineStore('badge', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const badges = ref([])
  const userBadges = ref([])

  // Getters
  const allBadges = computed(() => badges.value)
  const earnedBadges = computed(() => userBadges.value)
  const topBadges = computed(() => {
    // Return top 4 badges (earned first, then by XP reward)
    const earned = userBadges.value.slice(0, 4)
    if (earned.length >= 4) return earned
    
    const unearned = badges.value
      .filter(b => !userBadges.value.find(ub => ub.id === b.id))
      .sort((a, b) => (b.xp_reward || 0) - (a.xp_reward || 0))
      .slice(0, 4 - earned.length)
    
    return [...earned, ...unearned]
  })

  /**
   * Fetch all badges
   */
  const fetchAllBadges = async () => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.badges.list())
      badges.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch badges'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch badges for user
   * @param {number|string} userId - User ID
   */
  const fetchUserBadges = async (userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.badges.user(userId))
      userBadges.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch user badges'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Award badge to user (admin feature)
   * @param {object} payload - { user_id, badge_id }
   */
  const awardBadge = async (payload) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.badges.award(), payload)
      // Refresh user badges after awarding
      if (payload.user_id) {
        await fetchUserBadges(payload.user_id)
      }
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to award badge'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear badge data
   */
  const clearBadges = () => {
    badges.value = []
    userBadges.value = []
    error.value = null
  }

  return {
    // State
    loading,
    error,
    badges,
    userBadges,
    // Getters
    allBadges,
    earnedBadges,
    topBadges,
    // Actions
    fetchAllBadges,
    fetchUserBadges,
    awardBadge,
    clearBadges
  }
})

