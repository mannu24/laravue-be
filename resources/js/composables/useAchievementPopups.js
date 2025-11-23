/**
 * Composable for handling achievement popups (level ups and badge unlocks)
 * Manages queue of achievements to show sequentially
 * 
 * NOTE: This is a singleton - all components share the same state
 */
import { ref, computed } from 'vue'

// Singleton state - shared across all instances
const achievementQueue = ref([])
const currentAchievement = ref(null)

export function useAchievementPopups() {
  const isShowing = computed(() => currentAchievement.value !== null)

  /**
   * Add achievement to queue
   * @param {Object} achievement - Achievement data with type, level, or badge
   */
  const queueAchievement = (achievement) => {
    console.log('[AchievementPopups] Adding achievement to queue:', achievement.type)
    achievementQueue.value.push({
      id: Date.now() + Math.random(),
      ...achievement,
      timestamp: new Date(),
    })
    
    // Show next achievement if none is currently showing
    if (!currentAchievement.value) {
      console.log('[AchievementPopups] Showing achievement immediately')
      showNext()
    } else {
      console.log('[AchievementPopups] Achievement queued, waiting for current to close')
    }
  }

  /**
   * Show next achievement in queue
   */
  const showNext = () => {
    if (achievementQueue.value.length === 0) {
      currentAchievement.value = null
      return
    }

    currentAchievement.value = achievementQueue.value.shift()
  }

  /**
   * Close current achievement and show next
   */
  const closeCurrent = () => {
    currentAchievement.value = null
    
    // Show next achievement after a short delay
    setTimeout(() => {
      showNext()
    }, 300)
  }

  /**
   * Queue level up
   * @param {Object} level - Level data
   * @param {Object} previousLevel - Previous level data
   */
  const queueLevelUp = (level, previousLevel = null) => {
    console.log('[AchievementPopups] Queueing level up:', level, previousLevel)
    queueAchievement({
      type: 'level_up',
      level,
      previousLevel,
    })
  }

  /**
   * Queue badge unlock
   * @param {Object} badge - Badge data
   */
  const queueBadgeUnlock = (badge) => {
    console.log('[AchievementPopups] Queueing badge unlock:', badge)
    queueAchievement({
      type: 'badge_unlocked',
      badge,
    })
  }

  /**
   * Queue multiple achievements (e.g., from a single action)
   * @param {Array} achievements - Array of achievement objects
   */
  const queueMultiple = (achievements) => {
    achievements.forEach(achievement => {
      if (achievement.type === 'level_up') {
        queueLevelUp(achievement.level, achievement.previousLevel)
      } else if (achievement.type === 'badge_unlocked') {
        queueBadgeUnlock(achievement.badge)
      } else {
        queueAchievement(achievement)
      }
    })
  }

  /**
   * Clear all queued achievements
   */
  const clearQueue = () => {
    achievementQueue.value = []
    currentAchievement.value = null
  }

  return {
    // State
    currentAchievement,
    isShowing,
    achievementQueue: computed(() => achievementQueue.value),
    
    // Methods
    queueAchievement,
    queueLevelUp,
    queueBadgeUnlock,
    queueMultiple,
    closeCurrent,
    clearQueue,
  }
}

