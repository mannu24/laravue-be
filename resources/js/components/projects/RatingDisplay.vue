<script setup>
import { computed } from 'vue'
import { Star } from 'lucide-vue-next'

const props = defineProps({
  rating: {
    type: Number,
    required: true
  },
  count: {
    type: Number,
    default: 0
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})

const sizeClasses = {
  sm: 'h-3 w-3',
  md: 'h-4 w-4',
  lg: 'h-5 w-5'
}

const filledStars = computed(() => Math.floor(props.rating))
const hasHalfStar = computed(() => props.rating % 1 >= 0.5)
</script>

<template>
  <div class="flex items-center gap-1">
    <div class="flex items-center">
      <Star 
        v-for="i in 5" 
        :key="i"
        :class="[
          sizeClasses[size],
          i <= filledStars 
            ? 'fill-yellow-400 text-yellow-400' 
            : i === filledStars + 1 && hasHalfStar
            ? 'fill-yellow-400/50 text-yellow-400'
            : 'text-gray-300'
        ]"
      />
    </div>
    <span class="text-xs text-muted-foreground ml-1">
      {{ rating.toFixed(1) }}
      <span v-if="count > 0" class="text-gray-400">({{ count }})</span>
    </span>
  </div>
</template>

