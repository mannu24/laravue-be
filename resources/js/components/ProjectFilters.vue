<script setup>
const filters = [
  { name: 'All', value: 'all' },
  { name: 'Open Source', value: 'opensource' },
  { name: 'Premium', value: 'premium' },
  { name: 'Popular', value: 'popular' },
]

const emit = defineEmits(['filter'])
</script>

<template>
  <div class="flex flex-wrap gap-4 mb-8">
    <button v-for="filter in filters" :key="filter.value" @click="emit('filter', filter.value)"
      class="px-4 py-2 rounded-md bg-gray-800 text-white hover:bg-primary-600 transition-colors">
      {{ filter.name }}
    </button>
  </div>
</template>