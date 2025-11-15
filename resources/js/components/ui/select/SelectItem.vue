<script setup>
import { inject, computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  value: {
    type: [String, Number],
    required: true
  }
})

const select = inject('select')

const isSelected = computed(() => select.selectedValue.value === props.value)

const handleClick = () => {
  select.select(props.value)
}
</script>

<template>
  <div
    :class="cn(
      'px-3 py-2 text-sm cursor-pointer rounded-md hover:bg-accent transition-colors',
      isSelected && 'bg-accent font-medium',
      $attrs.class
    )"
    @click="handleClick"
  >
    <slot />
  </div>
</template>

