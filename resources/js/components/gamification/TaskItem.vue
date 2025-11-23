<!--
  TaskItem Component
  Purpose: Displays individual task with status indicator (tasks are auto-completed)
  Props: task (Object with id, title, description, xp_reward, frequency, status, completed_at)
  Emits: assign (taskId)
-->
<template>
  <Transition name="task-complete">
    <div
      ref="taskRef"
      class="relative overflow-hidden rounded-3xl border p-5 cursor-pointer transition-all duration-300 hover:shadow-lg"
      :class="{
        'bg-gradient-to-br from-sky-100 via-blue-50 to-emerald-100 dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20 border-gray-200 dark:border-white/10 hover:border-blue-300 dark:hover:border-indigo-300/60 hover:shadow-blue-200/50 dark:hover:shadow-sky-500/20 text-gray-900 dark:text-white': task.status === 'pending',
        'bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-sky-500/20 dark:via-blue-900/30 dark:to-emerald-500/20 border-emerald-200 dark:border-white/10 opacity-75 ring-2 ring-emerald-300/50 dark:ring-emerald-300/70 ring-emerald-400/50 dark:ring-emerald-400/60 text-gray-900 dark:text-white': task.status === 'completed'
      }"
      data-test="task-item"
    >
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-tr from-indigo-500/5 dark:from-indigo-500/10 via-transparent to-transparent"></div>
      <div class="relative z-10 flex items-start justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <h4
              class="font-semibold transition-all"
              :class="{
                'text-gray-900 dark:text-white': task.status === 'pending',
                'line-through text-gray-500 dark:text-gray-400': task.status === 'completed'
              }"
            >
              {{ task.title }}
            </h4>
            <Transition name="checkmark">
              <span
                v-if="task.status === 'completed'"
                class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-500 dark:bg-emerald-900/60 text-white dark:text-emerald-200 flex items-center gap-1"
              >
                <span class="checkmark-icon">✓</span>
                Done
              </span>
            </Transition>
          </div>
          <p v-if="task.description" class="text-sm text-gray-600 dark:text-white/80 mb-3">
            {{ task.description }}
          </p>
          <!-- Progress for weekly tasks -->
          <div v-if="task.frequency === 'weekly' && task.progress && task.status !== 'completed'" class="mb-3">
            <div class="flex items-center justify-between text-xs font-medium text-gray-700 dark:text-white/80 mb-1.5">
              <span>Progress</span>
              <span>{{ task.progress.current }} / {{ task.progress.target }}</span>
            </div>
            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-sky-500 to-blue-600 dark:from-sky-400 dark:to-blue-500 transition-all duration-500 ease-out rounded-full"
                :style="{ width: `${task.progress.percentage}%` }"
              ></div>
            </div>
          </div>
          <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center gap-1 text-gray-700 dark:text-white/90">
              <span class="font-semibold text-sky-600 dark:text-sky-300">
                +{{ task.xp_reward || 0 }}
              </span>
              <span>XP</span>
            </div>
            <div v-if="task.completed_at" class="text-gray-500 dark:text-white/60 text-xs">
              Completed {{ formatDate(task.completed_at) }}
            </div>
          </div>
        </div>
        <div class="flex-shrink-0 flex flex-col items-end gap-2">
          <span
              :class="frequencyBadgeClass"
              class="px-2 py-0.5 text-xs font-semibold rounded-full uppercase tracking-wide"
            >
              {{ task.frequency }}
            </span>
        </div>
      </div>
      
    </div>
  </Transition>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  task: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      title: '',
      description: '',
      xp_reward: 0,
      frequency: 'daily',
      status: 'pending',
      completed_at: null
    })
  }
})

const emit = defineEmits(['assign'])

const taskRef = ref(null)

const frequencyBadgeClass = computed(() => {
  return props.task.frequency === 'daily'
    ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300'
    : 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300'
})


const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Watch for status change to completed
watch(() => props.task.status, (newStatus) => {
  if (newStatus === 'completed' && taskRef.value) {
    // Add completion animation
    taskRef.value.classList.add('just-completed')
    setTimeout(() => {
      if (taskRef.value) {
        taskRef.value.classList.remove('just-completed')
      }
    }, 1000)
  }
})
</script>

<style scoped>
.just-completed {
  animation: task-complete-bounce 0.6s ease-out;
}

.checkmark-icon {
  display: inline-block;
  animation: checkmark-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Animations */
@keyframes task-complete-bounce {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes checkmark-pop {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  60% {
    transform: scale(1.3) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

/* Transitions */
.task-complete-enter-active,
.task-complete-leave-active {
  transition: all 0.3s ease;
}

.task-complete-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.task-complete-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.checkmark-enter-active {
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.checkmark-enter-from {
  opacity: 0;
  transform: scale(0) rotate(-180deg);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .task-completed,
  .just-completed,
  .checkmark-icon {
    animation: none !important;
  }
  
  .task-complete-enter-active,
  .task-complete-leave-active,
  .checkmark-enter-active {
    transition: opacity 0.2s ease !important;
  }
}
</style>
