<!--
  TaskItem Component
  Purpose: Displays individual task with action button to complete and status indicator
  Props: task (Object with id, title, description, xp_reward, frequency, status, completed_at)
  Emits: complete (taskId), assign (taskId)
-->
<template>
  <Transition name="task-complete">
    <div
      ref="taskRef"
      class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all"
      :class="{
        'opacity-75': task.status === 'completed',
        'border-green-500 dark:border-green-400': task.status === 'completed',
        'task-completed': isCompleting
      }"
      data-test="task-item"
    >
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <h4
              class="font-semibold text-gray-900 dark:text-white transition-all"
              :class="{ 'line-through text-gray-400 dark:text-gray-500': task.status === 'completed' }"
            >
              {{ task.title }}
            </h4>
            <span
              :class="frequencyBadgeClass"
              class="px-2 py-0.5 text-xs font-medium rounded-full"
            >
              {{ task.frequency }}
            </span>
            <Transition name="checkmark">
              <span
                v-if="task.status === 'completed'"
                class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 flex items-center gap-1"
              >
                <span class="checkmark-icon">✓</span>
                Completed
              </span>
            </Transition>
          </div>
          <p v-if="task.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            {{ task.description }}
          </p>
          <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
              <span class="font-semibold text-blue-600 dark:text-blue-400">
                +{{ task.xp_reward || 0 }}
              </span>
              <span>XP</span>
            </div>
            <div v-if="task.completed_at" class="text-gray-500 dark:text-gray-500 text-xs">
              Completed {{ formatDate(task.completed_at) }}
            </div>
          </div>
        </div>
        <div class="flex-shrink-0">
          <button
            v-if="task.status !== 'completed'"
            type="button"
            @click="handleComplete"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all hover:scale-105 active:scale-95"
            data-test="complete-task-button"
          >
            Complete
          </button>
          <button
            v-else
            type="button"
            disabled
            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed"
          >
            Done
          </button>
        </div>
      </div>
      
      <!-- Sparkle burst effect -->
      <div
        v-if="showSparkle"
        ref="sparkleContainer"
        class="sparkle-container"
      ></div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useAnimation } from '@/composables/useAnimation'

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

const emit = defineEmits(['complete', 'assign'])

const taskRef = ref(null)
const sparkleContainer = ref(null)
const isCompleting = ref(false)
const showSparkle = ref(false)
const { playSparkle } = useAnimation()

const frequencyBadgeClass = computed(() => {
  return props.task.frequency === 'daily'
    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
    : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
})

const handleComplete = () => {
  isCompleting.value = true
  showSparkle.value = true
  
  // Play sparkle animation
  setTimeout(() => {
    if (taskRef.value) {
      playSparkle(taskRef.value, 8)
    }
  }, 100)
  
  // Emit after animation
  setTimeout(() => {
    emit('complete', props.task.id)
    isCompleting.value = false
    showSparkle.value = false
  }, 600)
}

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
.sparkle-container {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
}

.task-completed {
  animation: task-complete-bounce 0.6s ease-out;
}

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
