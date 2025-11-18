<!--
  QuestionSearchBar Component
  Purpose: Debounced search input for questions
  Props: placeholder (string)
  Emits: search (query)
-->
<template>
  <div class="relative" data-test="question-search-bar">
    <input
      v-model="searchQuery"
      type="text"
      :placeholder="placeholder"
      class="w-full px-4 py-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
      data-test="search-input"
    />
    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
      🔍
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Search questions...'
  }
})

const emit = defineEmits(['search'])

const searchQuery = ref('')
let debounceTimer = null

watch(searchQuery, (newQuery) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (newQuery.length >= 2 || newQuery.length === 0) {
      emit('search', newQuery)
    }
  }, 400)
})
</script>

<style scoped>
/* Search bar styles handled by Tailwind */
</style>

