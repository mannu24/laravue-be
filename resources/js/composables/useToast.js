/**
 * useToast Composable
 * Helper functions for showing toast notifications
 */
import { useToastStore } from '@/stores/toastStore'

export function useToast() {
  const toastStore = useToastStore()

  /**
   * Show success toast
   * @param {string} message - Success message
   * @param {number} duration - Duration in ms
   */
  const toastSuccess = (message, duration = 3000) => {
    return toastStore.addToast({
      message,
      type: 'success',
      duration
    })
  }

  /**
   * Show error toast
   * @param {string} message - Error message
   * @param {number} duration - Duration in ms
   */
  const toastError = (message, duration = 4000) => {
    return toastStore.addToast({
      message,
      type: 'error',
      duration
    })
  }

  /**
   * Show warning toast
   * @param {string} message - Warning message
   * @param {number} duration - Duration in ms
   */
  const toastWarning = (message, duration = 3500) => {
    return toastStore.addToast({
      message,
      type: 'warning',
      duration
    })
  }

  /**
   * Show info toast
   * @param {string} message - Info message
   * @param {number} duration - Duration in ms
   */
  const toastInfo = (message, duration = 3000) => {
    return toastStore.addToast({
      message,
      type: 'info',
      duration
    })
  }

  /**
   * Show XP gain toast
   * @param {number} amount - XP amount gained
   * @param {number} duration - Duration in ms
   */
  const toastXp = (amount, duration = 2500) => {
    return toastStore.addToast({
      message: `+${amount} XP!`,
      type: 'xp',
      duration,
      data: { amount }
    })
  }

  /**
   * Show level-up toast
   * @param {object|string} level - Level object or level name
   * @param {number} duration - Duration in ms
   */
  const toastLevelUp = (level, duration = 4000) => {
    const levelName = typeof level === 'string' ? level : level?.name || 'New Level'
    return toastStore.addToast({
      message: `Level ${levelName} Reached!`,
      type: 'level',
      duration,
      data: { level }
    })
  }

  /**
   * Show badge unlock toast
   * @param {object} badge - Badge object
   * @param {number} duration - Duration in ms
   */
  const toastBadgeUnlocked = (badge, duration = 4000) => {
    return toastStore.addToast({
      message: `Unlocked: ${badge.name || 'New Badge'}!`,
      type: 'badge',
      duration,
      data: { badge }
    })
  }

  return {
    toastSuccess,
    toastError,
    toastWarning,
    toastInfo,
    toastXp,
    toastLevelUp,
    toastBadgeUnlocked
  }
}

