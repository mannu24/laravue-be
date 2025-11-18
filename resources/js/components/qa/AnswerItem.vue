<!--
  AnswerItem Component
  Purpose: Displays individual answer with body, author, verified badge, time, and actions
  Props: answer (Object), canVerify (boolean)
  Emits: verify (answerId), upvote (answerId)
-->
<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5"
    :class="{
      'border-green-500 dark:border-green-400 bg-green-50 dark:bg-green-900/20': answer.is_verified,
      'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20': answer.is_ai_generated
    }"
    data-test="answer-item"
  >
    <div class="flex items-start justify-between mb-3">
      <div class="flex items-center gap-3">
        <div v-if="answer.user" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold">
            {{ answer.user.name?.charAt(0).toUpperCase() || '?' }}
          </div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">
            {{ answer.user.name }}
          </span>
        </div>
        <div v-else class="flex items-center gap-2">
          <span class="text-sm font-medium text-gray-900 dark:text-white">AI Assistant</span>
          <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full">
            AI
          </span>
        </div>
        <VerifiedAnswerBadge v-if="answer.is_verified" :verified-by="answer.verified_by" />
      </div>
      <span class="text-xs text-gray-500 dark:text-gray-400">
        {{ formatDate(answer.created_at) }}
      </span>
    </div>

    <div class="prose dark:prose-invert max-w-none mb-4">
      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ answer.body || answer.content }}</p>
    </div>

    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-4">
        <button
          type="button"
          @click="handleUpvote"
          class="flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
          data-test="upvote-button"
        >
          <span>👍</span>
          <span class="text-sm">{{ answer.upvotes || 0 }}</span>
        </button>
      </div>
      <div>
        <button
          v-if="canVerify && !answer.is_verified && !answer.is_ai_generated"
          type="button"
          @click="handleVerify"
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
          data-test="verify-button"
        >
          Verify Answer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import VerifiedAnswerBadge from './VerifiedAnswerBadge.vue'

const props = defineProps({
  answer: {
    type: Object,
    required: true
  },
  canVerify: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['verify', 'upvote'])

const handleVerify = () => {
  emit('verify', props.answer.id)
}

const handleUpvote = () => {
  emit('upvote', props.answer.id)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<style scoped>
/* Answer styles handled by Tailwind */
</style>

