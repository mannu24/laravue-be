/**
 * User Store
 * Manages user profile and gamification data
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useUserStore = defineStore('user', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const user = ref(null)
  const gamification = ref(null)

  // Getters
  const userProfile = computed(() => user.value)
  const gamificationSummary = computed(() => gamification.value)

  /**
   * Fetch user profile
   * @param {number|string} id - User ID
   */
  const fetchUserProfile = async (id) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.users.show(id))
      user.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch user profile'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update user profile
   * @param {number|string} id - User ID
   * @param {object} payload - Update data
   */
  const updateUser = async (id, payload) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.patch(endpoints.users.update(id), payload)
      user.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update user profile'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch gamification summary for user
   * @param {number|string} id - User ID
   */
  const fetchGamificationSummary = async (id) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.users.gamification(id))
      gamification.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch gamification data'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear user data
   */
  const clearUser = () => {
    user.value = null
    gamification.value = null
    error.value = null
  }

  return {
    // State
    loading,
    error,
    user,
    gamification,
    // Getters
    userProfile,
    gamificationSummary,
    // Actions
    fetchUserProfile,
    updateUser,
    fetchGamificationSummary,
    clearUser
  }
})

