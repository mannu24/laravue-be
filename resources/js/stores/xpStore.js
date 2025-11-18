/**
 * XP Store
 * Manages XP logs and summary data
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useXpStore = defineStore('xp', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const xpLogs = ref([])
  const xpSummary = ref(null)

  // Getters
  const logs = computed(() => xpLogs.value)
  const summary = computed(() => xpSummary.value)

  /**
   * Fetch XP logs for user
   * @param {number|string} userId - User ID
   * @param {object} params - Query parameters (page, per_page)
   */
  const fetchXpLogs = async (userId, params = {}) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.xp.logs(userId), params)
      xpLogs.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch XP logs'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch XP summary for user
   * @param {number|string} userId - User ID
   */
  const fetchXpSummary = async (userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.xp.summary(userId))
      xpSummary.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch XP summary'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear XP data
   */
  const clearXp = () => {
    xpLogs.value = []
    xpSummary.value = null
    error.value = null
  }

  return {
    // State
    loading,
    error,
    xpLogs,
    xpSummary,
    // Getters
    logs,
    summary,
    // Actions
    fetchXpLogs,
    fetchXpSummary,
    clearXp
  }
})

