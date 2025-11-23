import { onMounted, onBeforeUnmount, watch, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGlobalDataStore } from '@/stores/globalData'
import { useAchievementPopups } from '@/composables/useAchievementPopups'
import { toast } from '@/components/ui/toast'

let gamificationChannel = null
// Track recent task completions to prevent duplicate toasts
const recentCompletions = new Map()
const TOAST_DEBOUNCE_MS = 3000 // 3 seconds

export function useGamificationRealtime(options = {}) {
  const authStore = useAuthStore()
  const globalDataStore = useGlobalDataStore()
  const { queueLevelUp, queueBadgeUnlock } = useAchievementPopups()
  const isConnected = ref(false)
  const connectionError = ref(null)

  const handleTaskCompleted = async (event) => {
    const source = event?.source ?? 'manual'

    // Skip manual completions (already handled via UI)
    if (source === 'manual') {
      return
    }

    const taskId = event?.task?.id ?? event?.task_id
    const xp = event?.xp_reward ?? event?.task?.xp_reward ?? 0
    const taskTitle = event?.task?.title || event?.task_title || 'Task completed!'

    // Deduplicate: Check if we've shown a toast for this task recently
    const recentKey = `${taskId}-${new Date().toDateString()}`
    const lastShown = recentCompletions.get(recentKey)
    
    if (lastShown && Date.now() - lastShown < TOAST_DEBOUNCE_MS) {
      // Skip duplicate toast within debounce window
      return
    }

    // Mark as shown
    recentCompletions.set(recentKey, Date.now())
    
    // Clean up old entries (older than debounce window)
    setTimeout(() => {
      recentCompletions.delete(recentKey)
    }, TOAST_DEBOUNCE_MS)

    if (typeof options.onTaskCompleted === 'function') {
      options.onTaskCompleted(event)
    }

    // Simply refresh global data to get updated task list from backend
    await globalDataStore.fetchGlobalData({ force: true })

    // Show XP toast notification using shadcn toast with actual XP amount from backend
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

  const handleBadgeUnlocked = (event) => {
    console.log('[GamificationRealtime] Badge unlocked event received:', event)
    const badge = event?.badge
    
    if (!badge) {
      console.warn('[GamificationRealtime] Badge unlocked event missing badge data', event)
      return
    }

    console.log('[GamificationRealtime] Queueing badge unlock:', badge)

    if (typeof options.onBadgeUnlocked === 'function') {
      options.onBadgeUnlocked(event)
    }

    // Queue badge unlock popup
    queueBadgeUnlock(badge)

    // Refresh global data to update badges
    globalDataStore.fetchGlobalData({ force: true })
  }

  const handleLevelUp = (event) => {
    console.log('[GamificationRealtime] Level up event received:', event)
    const level = event?.level
    const previousLevel = event?.previous_level

    if (!level) {
      console.warn('[GamificationRealtime] Level up event missing level data', event)
      return
    }

    console.log('[GamificationRealtime] Queueing level up:', level)

    if (typeof options.onLevelUp === 'function') {
      options.onLevelUp(event)
    }

    // Queue level up popup
    queueLevelUp(level, previousLevel)

    // Refresh global data to update level
    globalDataStore.fetchGlobalData({ force: true })
  }

  const connect = () => {
    if (!window.Echo) {
      connectionError.value = 'Broadcasting not configured'
      return
    }

    if (!authStore.isAuthenticated || !authStore.user?.id) {
      return
    }

    try {
      const channelName = `user.${authStore.user.id}`
      gamificationChannel = window.Echo.private(channelName)

      gamificationChannel.listen('.gamification.task.completed', handleTaskCompleted)
      gamificationChannel.listen('.gamification.badge.unlocked', handleBadgeUnlocked)
      gamificationChannel.listen('.gamification.level.up', handleLevelUp)
      
      console.log('[GamificationRealtime] Listening for events on channel:', channelName)

      gamificationChannel.subscribed(() => {
        isConnected.value = true
        connectionError.value = null
        console.log('[GamificationRealtime] Connected to channel:', channelName)
      })

      gamificationChannel.error((error) => {
        console.error('[GamificationRealtime] Channel error:', error)
        connectionError.value = error.message || 'Connection error'
        isConnected.value = false
      })
    } catch (error) {
      console.error('[GamificationRealtime] Connection failed:', error)
      connectionError.value = error.message || 'Failed to connect'
      isConnected.value = false
    }
  }

  const disconnect = () => {
    if (gamificationChannel && authStore.user?.id) {
      try {
        window.Echo.leave(`user.${authStore.user.id}`)
      } catch (error) {
        console.error('[GamificationRealtime] Disconnect error:', error)
      }
    }
    gamificationChannel = null
    isConnected.value = false
  }

  watch(
    () => authStore.isAuthenticated,
    (isAuthenticated) => {
      disconnect()
      if (isAuthenticated) {
        // Small delay to ensure auth state is fully updated
        setTimeout(() => {
          connect()
        }, 100)
      }
    },
    { immediate: true }
  )

  watch(
    () => authStore.user?.id,
    (userId) => {
      if (userId && authStore.isAuthenticated) {
        // Reconnect when user ID is available
        disconnect()
        setTimeout(() => {
          connect()
        }, 100)
      }
    },
    { immediate: true }
  )

  onMounted(() => {
    if (authStore.isAuthenticated && authStore.user?.id) {
      // Small delay to ensure Echo is ready
      setTimeout(() => {
        connect()
      }, 200)
    }
  })

  onBeforeUnmount(() => {
    disconnect()
  })

  return {
    connect,
    disconnect,
    isConnected,
    connectionError,
  }
}

