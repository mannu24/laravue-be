/**
 * Level Store
 * Manages levels and level progress data
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useLevelStore = defineStore('level', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const levels = ref([])
  const progress = ref(null)
  const currentLevel = ref(null)

  // Getters
  const allLevels = computed(() => levels.value)
  const levelProgress = computed(() => progress.value)
  const userCurrentLevel = computed(() => currentLevel.value)

  /**
   * Fetch all levels
   */
  const fetchLevels = async () => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.levels.list())
      levels.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch levels'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get level for given XP amount
   * @param {number} xp - XP amount
   */
  const getLevelForXp = async (xp) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.levels.show(xp))
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch level'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch level progress for user
   * @param {number|string} userId - User ID
   */
  const fetchLevelProgress = async (userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.levels.progress(userId))
      progress.value = response.data.data
      currentLevel.value = response.data.data.level
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch level progress'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear level data
   */
  const clearLevels = () => {
    levels.value = []
    progress.value = null
    currentLevel.value = null
    error.value = null
  }

  return {
    // State
    loading,
    error,
    levels,
    progress,
    currentLevel,
    // Getters
    allLevels,
    levelProgress,
    userCurrentLevel,
    // Actions
    fetchLevels,
    getLevelForXp,
    fetchLevelProgress,
    clearLevels
  }
})

