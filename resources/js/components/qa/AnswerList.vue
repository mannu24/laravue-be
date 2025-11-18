<!--
  AnswerList Component
  Purpose: Renders AnswerItem components sorted: verified first, then AI, then user answers
  Props: answers (Array of answer objects)
  Emits: verify-answer (answerId), reply (answerId)
-->
<template>
  <div class="space-y-4">
    <div v-if="sortedAnswers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No answers yet. Be the first to answer!
    </div>
    <AnswerItem
      v-for="answer in sortedAnswers"
      :key="answer.id"
      :answer="answer"
      :can-verify="canVerify"
      @verify="handleVerify"
      @upvote="handleUpvote"
      data-test="answer-item"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import AnswerItem from './AnswerItem.vue'

const props = defineProps({
  answers: {
    type: Array,
    default: () => []
  },
  canVerify: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['verify-answer', 'reply'])

const sortedAnswers = computed(() => {
  const verified = props.answers.filter(a => a.is_verified)
  const aiGenerated = props.answers.filter(a => a.is_ai_generated && !a.is_verified)
  const userAnswers = props.answers.filter(a => !a.is_ai_generated && !a.is_verified)
  
  return [...verified, ...aiGenerated, ...userAnswers]
})

const handleVerify = (answerId) => {
  emit('verify-answer', answerId)
}

const handleUpvote = (answerId) => {
  // TODO: Handle upvote via store
}
</script>

<style scoped>
/* List spacing handled by Tailwind */
</style>

