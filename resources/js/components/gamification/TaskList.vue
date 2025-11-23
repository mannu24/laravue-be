<script setup>
import { ref, computed } from 'vue'
import TaskItem from './TaskItem.vue'
import { CircleAlertIcon } from 'lucide-vue-next'
import Tabs from '@/components/ui/Tabs.vue'

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  dailyTasks: {
    type: Array,
    default: () => []
  },
  weeklyTasks: {
    type: Array,
    default: () => []
  },
  showFilter: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['complete-task', 'assign-task'])

const activeTab = ref('daily')

// Determine which tasks to show
const tasksToShow = computed(() => {
  // If showFilter is true and we have daily/weekly tasks, use those
  if (props.showFilter && (props.dailyTasks.length > 0 || props.weeklyTasks.length > 0)) {
    return activeTab.value === 'daily' ? props.dailyTasks : props.weeklyTasks
  }
  // Otherwise use the tasks prop (for backward compatibility)
  return props.tasks
})

const handleComplete = (taskId) => {
  emit('complete-task', taskId)
}

const handleAssign = (taskId) => {
  emit('assign-task', taskId)
}

const handleTabChange = (tabId) => {
  activeTab.value = tabId
}

// Check if we should show tabs
const shouldShowTabs = computed(() => {
  return props.showFilter && (props.dailyTasks.length > 0 || props.weeklyTasks.length > 0)
})
</script>
<template>
  <div>
    <!-- Tabs Filter (only show if showFilter is true and we have daily/weekly tasks) -->
    <div v-if="shouldShowTabs" class="mb-6">
      <Tabs 
        :tabs="[
          { id: 'daily', label: 'Daily Tasks' },
          { id: 'weekly', label: 'Weekly Tasks' }
        ]"
        :default-tab="activeTab"
        @change="handleTabChange"
      >
        <template #daily>
          <div class="mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-4">
              <TaskItem
                v-for="task in dailyTasks"
                :key="task.id"
                :task="task"
                @complete="handleComplete"
                @assign="handleAssign"
                data-test="task-item"
              />
              <div v-if="dailyTasks.length === 0" class="col-span-2 flex flex-col items-center justify-center py-12">
                <CircleAlertIcon class="w-12 h-12 mx-auto mb-4 text-sky-500/60 dark:text-sky-300/30" />
                <div class="text-lg font-medium text-gray-700 dark:text-gray-400">No daily tasks</div>
                <div class="mt-2 text-sm text-gray-600 dark:text-gray-500">You're all caught up. Check back later for new tasks!</div>
              </div>
            </div>
          </div>
        </template>
        <template #weekly>
          <div class="mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-4">
              <TaskItem
                v-for="task in weeklyTasks"
                :key="task.id"
                :task="task"
                @complete="handleComplete"
                @assign="handleAssign"
                data-test="task-item"
              />
              <div v-if="weeklyTasks.length === 0" class="col-span-2 flex flex-col items-center justify-center py-12">
                <CircleAlertIcon class="w-12 h-12 mx-auto mb-4 text-sky-500/60 dark:text-sky-300/30" />
                <div class="text-lg font-medium text-gray-700 dark:text-gray-400">No weekly tasks</div>
                <div class="mt-2 text-sm text-gray-600 dark:text-gray-500">You're all caught up. Check back later for new tasks!</div>
              </div>
            </div>
          </div>
        </template>
      </Tabs>
    </div>

    <!-- Regular Task List (when no filter or using tasks prop) -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-3 gap-y-4">
      <TaskItem
        v-for="task in tasksToShow"
        :key="task.id"
        :task="task"
        @complete="handleComplete"
        @assign="handleAssign"
        data-test="task-item"
      />
      <div v-if="tasksToShow.length === 0" class="col-span-2 flex flex-col items-center justify-center py-12">
        <CircleAlertIcon class="w-12 h-12 mx-auto mb-4 text-sky-500/60 dark:text-sky-300/30" />
        <div class="text-lg font-medium text-gray-700 dark:text-gray-400">No tasks to show</div>
        <div class="mt-2 text-sm text-gray-600 dark:text-gray-500">You're all caught up. Check back later for new tasks!</div>
      </div>
    </div>
  </div>
</template>