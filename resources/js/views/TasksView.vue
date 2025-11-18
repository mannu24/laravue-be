<!--
  TasksView
  Purpose: Shows daily and weekly tasks with completion functionality
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Tasks</h1>
    
    <Tabs :tabs="tabs" @change="handleTabChange">
      <template #daily>
        <div class="mt-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Daily Tasks</h2>
          <div v-if="taskStore.loading" class="text-center py-8">
            <LoadingSpinner />
          </div>
          <TaskList
            v-else
            :tasks="dailyTasks"
            @complete-task="handleCompleteTask"
            @assign-task="handleAssignTask"
          />
        </div>
      </template>
      <template #weekly>
        <div class="mt-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Weekly Tasks</h2>
          <div v-if="taskStore.loading" class="text-center py-8">
            <LoadingSpinner />
          </div>
          <TaskList
            v-else
            :tasks="weeklyTasks"
            @complete-task="handleCompleteTask"
            @assign-task="handleAssignTask"
          />
        </div>
      </template>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTaskStore } from '@/stores/taskStore'
import { useToast } from '@/composables/useToast'
import Tabs from '@/components/ui/Tabs.vue'
import TaskList from '@/components/gamification/TaskList.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()
const taskStore = useTaskStore()
const { toastSuccess, toastError, toastXp } = useToast()

const tabs = ref([
  { id: 'daily', label: 'Daily Tasks' },
  { id: 'weekly', label: 'Weekly Tasks' }
])

const activeTab = ref('daily')

// Use store computed values
const dailyTasks = computed(() => taskStore.todayTasks)
const weeklyTasks = computed(() => taskStore.weekTasks)

onMounted(async () => {
  if (!authStore.isAuthenticated || !authStore.user?.id) {
    return
  }

  const userId = authStore.user.id

  try {
    await Promise.all([
      taskStore.fetchDailyTasks(userId),
      taskStore.fetchWeeklyTasks(userId)
    ])
  } catch (error) {
    toastError('Failed to load tasks')
  }
})

const handleTabChange = (tabId) => {
  activeTab.value = tabId
}

const handleCompleteTask = async (taskId) => {
  if (!authStore.user?.id) {
    toastError('Please login to complete tasks')
    return
  }

  try {
    const result = await taskStore.completeTask(taskId, authStore.user.id)
    
    // Show XP toast if XP was awarded
    if (result?.xp_reward) {
      toastXp(result.xp_reward)
    } else {
      toastSuccess('Task completed!')
    }
  } catch (error) {
    toastError(error.message || 'Failed to complete task')
  }
}

const handleAssignTask = async (taskId) => {
  if (!authStore.user?.id) {
    toastError('Please login to assign tasks')
    return
  }

  try {
    await taskStore.assignTask(taskId, authStore.user.id)
    toastSuccess('Task assigned!')
  } catch (error) {
    toastError(error.message || 'Failed to assign task')
  }
}
</script>

<style scoped>
/* Tasks view styles handled by Tailwind */
</style>
