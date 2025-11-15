<script setup>
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'

const props = defineProps({
  project: {
    type: Object,
    required: true
  }
})

const badgeConfig = computed(() => {
  if (props.project.is_sellable) {
    return {
      label: 'Premium',
      class: 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white'
    }
  }
  
  if (props.project.project_type === 'open') {
    return {
      label: 'Open Source',
      class: 'bg-gradient-to-r from-green-500 to-emerald-500 text-white'
    }
  }
  
  return {
    label: 'Closed Source',
    class: 'bg-gray-500 text-white'
  }
})
</script>

<template>
  <Badge :class="['text-xs font-medium shadow-md', badgeConfig.class]">
    {{ badgeConfig.label }}
  </Badge>
</template>

