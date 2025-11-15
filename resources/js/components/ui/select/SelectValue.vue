<script setup>
import { inject, computed } from 'vue'

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Select...'
  }
})

const select = inject('select')

const displayValue = computed(() => {
  const value = select.selectedValue.value
  if (value === null || value === '' || value === undefined) {
    return props.placeholder
  }
  // If there's a slot, let parent handle display
  return value
})
</script>

<template>
  <span v-if="!$slots.default">{{ displayValue }}</span>
  <slot v-else />
</template>

