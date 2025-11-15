<script setup>
import { inject } from 'vue'
import { Button } from '../button'
import { ChevronDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const select = inject('select')

const handleClick = () => {
  if (select.isOpen.value) {
    select.close()
  } else {
    select.open()
  }
}
</script>

<template>
  <Button
    type="button"
    variant="outline"
    :class="cn('w-full justify-between', $attrs.class)"
    @click="handleClick"
    :disabled="select.disabled"
  >
    <slot />
    <ChevronDown 
      :class="cn('h-4 w-4 opacity-50 transition-transform', select.isOpen.value && 'rotate-180')"
    />
  </Button>
</template>

