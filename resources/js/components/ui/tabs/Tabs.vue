<script setup>
import { provide, ref, watch } from 'vue'

const props = defineProps({
  defaultValue: {
    type: String,
    default: ''
  },
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const activeTab = ref(props.modelValue || props.defaultValue)

provide('tabs', {
  activeTab,
  setActiveTab: (value) => {
    activeTab.value = value
    emit('update:modelValue', value)
  }
})

watch(() => props.modelValue, (newValue) => {
  if (newValue !== undefined) {
    activeTab.value = newValue
  }
})
</script>

<template>
  <div class="w-full">
    <slot />
  </div>
</template>
