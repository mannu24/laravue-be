/**
 * Gamification Polling Composable
 * 
 * Periodically polls for gamification updates (task completions, badges, level ups).
 * Replaces the previous WebSocket-based real-time implementation
 * for shared hosting compatibility.
 */

import { onMounted, onBeforeUnmount, watch, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGlobalDataStore } from '@/stores/globalData'
import { useAchievementPopups } from '@/composables/useAchievementPopups'
import { toast } from '@/components/ui/toast'
import axios from 'axios'

const POLL_INTERVAL = 30000 // 30 seconds
let pollTimer = null
let lastCheckedAt = null

export function useGamificationRealtime(options = {}) {
  const authStore = useAuthStore()
  const globalDataStore = useGlobalDataStore()
  const { queueLevelUp, queueBadgeUnlock } = useAchievementPopups()
  const isConnected = ref(false)
  const connectionError = ref(null)

  /**
   * Poll for recent achievement events
   */
  const pollAchievements = async () => {
    if (!authStore.isAuthenticated) return

    try {
      const params = {}
      if (lastCheckedAt) {
        params.since = lastCheckedAt
      }

      const response = await axios.get('/api/v1/gamification/recent-achievements', {
        ...authStore.config,
        params,
      })

      if (response.data.status === 'success') {
        const data = response.data.data

        // Process new badge unlocks
        if (data.badges && data.badges.length > 0) {
          for (const badge of data.badges) {
            if (typeof options.onBadgeUnlocked === 'function') {
              options.onBadgeUnlocked({ badge })
            }
            queueBadgeUnlock(badge)
          }
        }

        // Process level ups
        if (data.level_up) {
          const level = data.level_up.level
          const previousLevel = data.level_up.previous_level
          if (typeof options.onLevelUp === 'function') {
            options.onLevelUp({ level, previous_level: previousLevel })
          }
          queueLevelUp(level, previousLevel)
        }

        // Process auto-completed tasks (not manual ones)
        if (data.tasks && data.tasks.length > 0) {
          for (const task of data.tasks) {
            if (task.source === 'manual') continue

            if (typeof options.onTaskCompleted === 'function') {
              options.onTaskCompleted(task)
            }

            const xp = task.xp_reward ?? 0
            const taskTitle = task.title || 'Task completed!'

            if (xp > 0) {
              toast({
                title: `+${xp} XP!`,
                description: `Task "${taskTitle}" completed`,
                duration: 2500,
              })
            } else {
              toast({
                title: 'Task completed!',
                description: `${taskTitle} completed successfully`,
                duration: 3000,
              })
            }
          }

          // Refresh global data when tasks were completed
          await globalDataStore.fetchGlobalData({ force: true })
        }

        // Update last checked timestamp
        lastCheckedAt = new Date().toISOString()
      }
    } catch (error) {
      // Silently fail — polling errors shouldn't disrupt the UI
      if (error.response?.status !== 401) {
        console.warn('[GamificationPolling] Poll failed:', error.message)
      }
    }
  }

  const startPolling = () => {
    if (pollTimer) return
    lastCheckedAt = new Date().toISOString()
    isConnected.value = true
    pollTimer = setInterval(pollAchievements, POLL_INTERVAL)
  }

  const stopPolling = () => {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
    isConnected.value = false
  }

  watch(
    () => authStore.isAuthenticated,
    (isAuthenticated) => {
      stopPolling()
      if (isAuthenticated) {
        startPolling()
      }
    },
    { immediate: true }
  )

  onMounted(() => {
    if (authStore.isAuthenticated) {
      startPolling()
    }
  })

  onBeforeUnmount(() => {
    stopPolling()
  })

  return {
    connect: startPolling,
    disconnect: stopPolling,
    isConnected,
    connectionError,
  }
}
