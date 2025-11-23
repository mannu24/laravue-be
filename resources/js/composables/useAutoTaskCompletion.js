/**
 * Composable for automatic task completion
 * Detects user actions and automatically completes related tasks
 */
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useGlobalDataStore } from '@/stores/globalData'
import { useTaskStore } from '@/stores/taskStore'
import api from '@/lib/api'
import endpoints from '@/lib/endpoints'

// Task title mappings for automatic detection
const TASK_MAPPINGS = {
  // Route-based tasks
  'questions.ask': 'Ask 1 Question',
  'questions.index': null, // No specific task
  'tasks.index': null, // No specific task
  
  // Action-based tasks (handled in components)
  'question_created': 'Ask 1 Question',
  'answer_created': 'Answer 1 Question',
  // Profile visits are tracked automatically by backend when viewing someone else's profile
}

// Track completed tasks in this session to avoid duplicate calls
const completedTasks = ref(new Set())

export function useAutoTaskCompletion() {
  const authStore = useAuthStore()
  const globalDataStore = useGlobalDataStore()
  const taskStore = useTaskStore()

  /**
   * Complete a task automatically by title
   * @param {string} taskTitle - The title of the task to complete
   * @param {boolean} silent - If true, don't show toast notifications
   */
  const completeTaskByTitle = async (taskTitle, silent = false) => {
    if (!authStore.isAuthenticated || !authStore.user?.id) {
      return false
    }

    // Check if already completed in this session
    const taskKey = `${taskTitle}-${new Date().toDateString()}`
    if (completedTasks.value.has(taskKey)) {
      return false
    }

    try {
      const response = await api.post(endpoints.tasks.autoComplete(), {
        task_title: taskTitle
      })

      if (response.data.success) {
        const task = response.data.data
        
        // Mark as completed in session
        completedTasks.value.add(taskKey)

        // Refresh global data to update UI
        await globalDataStore.fetchGlobalData({ force: true })

        // XP toast will be shown via realtime notification from backend
        // when UserCompletedTask event is broadcast

        return true
      }
    } catch (error) {
      // Silently fail - task might not exist or already completed
      // Don't show error to user for automatic completions
      console.debug('[AutoTask] Task completion failed:', error.response?.data?.message || error.message)
      return false
    }

    return false
  }

  /**
   * Complete task based on route name
   * @param {string} routeName - Vue router route name
   */
  const completeTaskByRoute = async (routeName) => {
    const taskTitle = TASK_MAPPINGS[routeName]
    
    if (!taskTitle) {
      return false
    }

    return await completeTaskByTitle(taskTitle, false)
  }

  /**
   * Complete task based on action
   * @param {string} action - Action name (e.g., 'question_created', 'answer_created')
   */
  const completeTaskByAction = async (action) => {
    const taskTitle = TASK_MAPPINGS[action]
    
    if (!taskTitle) {
      return false
    }

    return await completeTaskByTitle(taskTitle, false)
  }

  /**
   * Clear completed tasks cache (useful for testing or reset)
   */
  const clearCompletedCache = () => {
    completedTasks.value.clear()
  }

  return {
    completeTaskByTitle,
    completeTaskByRoute,
    completeTaskByAction,
    clearCompletedCache,
  }
}

