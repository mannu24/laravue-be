<!--
  TaskList Component
  Purpose: Renders a list of TaskItem components with actions
  Props: tasks (Array of task objects)
  Emits: complete-task (taskId), assign-task (taskId)
-->
<template>
  <div class="space-y-3">
    <TaskItem
      v-for="task in tasks"
      :key="task.id"
      :task="task"
      @complete="handleComplete"
      @assign="handleAssign"
      data-test="task-item"
    />
    <div v-if="tasks.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No tasks available
    </div>
  </div>
</template>

<script setup>
import TaskItem from './TaskItem.vue'

const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['complete-task', 'assign-task'])

const handleComplete = (taskId) => {
  emit('complete-task', taskId)
}

const handleAssign = (taskId) => {
  emit('assign-task', taskId)
}
</script>

<style scoped>
/* List spacing handled by Tailwind */
</style>

