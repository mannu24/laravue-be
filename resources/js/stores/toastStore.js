/**
 * Toast Store
 * Manages global toast notifications with auto-dismiss and stacking
 */
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  // State
  const toasts = ref([])

  /**
   * Generate unique ID for toast
   */
  const generateId = () => {
    return `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  }

  /**
   * Add a new toast
   * @param {object} options - Toast options
   * @param {string} options.message - Toast message
   * @param {string} options.type - Toast type (success, error, warning, info, xp, level, badge)
   * @param {number} options.duration - Duration in ms (default: 3000)
   * @param {object} options.data - Additional data for gamification toasts
   */
  const addToast = ({ message, type = 'info', duration = 3000, data = {} }) => {
    const id = generateId()
    const toast = {
      id,
      message,
      type,
      duration,
      data,
      createdAt: Date.now()
    }

    // Add to beginning of array (newest on top)
    toasts.value.unshift(toast)

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }

    return id
  }

  /**
   * Remove a toast by ID
   * @param {string} id - Toast ID
   */
  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  /**
   * Clear all toasts
   */
  const clearAll = () => {
    toasts.value = []
  }

  return {
    // State
    toasts,
    // Actions
    addToast,
    removeToast,
    clearAll
  }
})

