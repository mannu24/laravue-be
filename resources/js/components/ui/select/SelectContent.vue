<script setup>
import { inject, onMounted, onUnmounted } from 'vue'
import { Card, CardContent } from '../card'
import { cn } from '@/lib/utils'

const select = inject('select')

const handleClickOutside = (event) => {
  if (!event.target.closest('.select-content')) {
    select.close()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div
    v-if="select.isOpen.value"
    class="select-content absolute z-50 w-full mt-1"
  >
    <Card class="border shadow-lg">
      <CardContent class="p-1">
        <slot />
      </CardContent>
    </Card>
  </div>
</template>

