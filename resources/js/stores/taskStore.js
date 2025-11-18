/**
 * Task Store
 * Manages tasks and task completion
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

export const useTaskStore = defineStore('task', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const dailyTasks = ref([])
  const weeklyTasks = ref([])

  // Getters
  const todayTasks = computed(() => dailyTasks.value)
  const weekTasks = computed(() => weeklyTasks.value)

  /**
   * Fetch daily tasks for user
   * @param {number|string} userId - User ID
   */
  const fetchDailyTasks = async (userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.tasks.daily(userId))
      dailyTasks.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch daily tasks'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch weekly tasks for user
   * @param {number|string} userId - User ID
   */
  const fetchWeeklyTasks = async (userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.get(endpoints.tasks.weekly(userId))
      weeklyTasks.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch weekly tasks'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Complete a task
   * @param {number|string} taskId - Task ID
   * @param {number|string} userId - User ID
   */
  const completeTask = async (taskId, userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.tasks.complete(), {
        task_id: taskId,
        user_id: userId
      })
      // Refresh tasks after completion
      await fetchDailyTasks(userId)
      await fetchWeeklyTasks(userId)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to complete task'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Assign task to user
   * @param {number|string} taskId - Task ID
   * @param {number|string} userId - User ID
   */
  const assignTask = async (taskId, userId) => {
    try {
      loading.value = true
      error.value = null
      const response = await api.post(endpoints.tasks.assign(), {
        task_id: taskId,
        user_id: userId
      })
      // Refresh tasks after assignment
      await fetchDailyTasks(userId)
      await fetchWeeklyTasks(userId)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to assign task'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear task data
   */
  const clearTasks = () => {
    dailyTasks.value = []
    weeklyTasks.value = []
    error.value = null
  }

  return {
    // State
    loading,
    error,
    dailyTasks,
    weeklyTasks,
    // Getters
    todayTasks,
    weekTasks,
    // Actions
    fetchDailyTasks,
    fetchWeeklyTasks,
    completeTask,
    assignTask,
    clearTasks
  }
})

