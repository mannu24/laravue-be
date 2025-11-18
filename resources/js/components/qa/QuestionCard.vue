<!--
  QuestionCard Component
  Purpose: Displays question preview with title, excerpt, author, upvotes, views, and verified answer indicator
  Props: question (Object with question data)
  Emits: open (questionId)
-->
<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-all cursor-pointer"
    @click="handleOpen"
    data-test="question-card"
  >
    <div class="flex items-start justify-between gap-4 mb-3">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex-1">
        {{ question.title }}
      </h3>
      <div
        v-if="hasVerifiedAnswer"
        class="flex-shrink-0 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full"
        data-test="verified-badge"
      >
        ✓ Verified
      </div>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
      {{ question.body || question.content || question.ai_generated_summary || 'No description' }}
    </p>

    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-1">
          <span>👤</span>
          <span>{{ question.user?.name || 'Anonymous' }}</span>
        </div>
        <div class="flex items-center gap-1">
          <span>👍</span>
          <span>{{ question.upvotes_count || question.score || 0 }}</span>
        </div>
        <div class="flex items-center gap-1">
          <span>👁️</span>
          <span>{{ question.views || question.view_count || 0 }}</span>
        </div>
      </div>
      <div class="text-xs">
        {{ formatDate(question.created_at) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  question: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['open'])

const hasVerifiedAnswer = computed(() => {
  return props.question.answers?.some(answer => answer.is_verified) || false
})

const handleOpen = () => {
  emit('open', props.question.id)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) return 'Today'
  if (days === 1) return 'Yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  if (days < 365) return `${Math.floor(days / 30)} months ago`
  return `${Math.floor(days / 365)} years ago`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

