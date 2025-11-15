<script setup>
import { computed } from 'vue'
import { useThemeStore } from '../../stores/theme'
import { useAuthStore } from '../../stores/auth'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import RatingDisplay from './RatingDisplay.vue'
import { User, Calendar, ThumbsUp, Verified } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from '@/components/ui/toast'

const props = defineProps({
  review: {
    type: Object,
    required: true
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['edit', 'delete', 'helpful'])

const themeStore = useThemeStore()
const authStore = useAuthStore()

const formatDate = (dateString) => {
  if (!dateString) return 'Recently'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const markHelpful = async () => {
  try {
    // This would call an API endpoint to mark as helpful
    emit('helpful', props.review.id)
  } catch (error) {
    console.error('Error marking review as helpful:', error)
  }
}
</script>

<template>
  <Card :class="[
    'border-0 shadow-sm',
    themeStore.isDark ? 'bg-gray-800/50' : 'bg-white'
  ]">
    <CardContent class="p-5">
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold">
            {{ review.user?.name?.charAt(0) || 'U' }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-medium" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                {{ review.user?.name || 'Anonymous' }}
              </span>
              <Badge v-if="review.is_verified_purchase" variant="outline" class="text-xs">
                <Verified class="h-3 w-3 mr-1" />
                Verified Purchase
              </Badge>
              <Badge v-if="review.is_featured" class="bg-yellow-500 text-white text-xs">
                Featured
              </Badge>
            </div>
            <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
              <Calendar class="h-3 w-3" />
              <span>{{ formatDate(review.created_at) }}</span>
            </div>
          </div>
        </div>
        <RatingDisplay :rating="review.rating" size="sm" />
      </div>

      <!-- Comment -->
      <p v-if="review.comment" class="text-sm mb-4 leading-relaxed"
        :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
        {{ review.comment }}
      </p>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-3 border-t"
        :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'">
        <div class="flex items-center gap-4">
          <button
            @click="markHelpful"
            class="flex items-center gap-1 text-xs text-muted-foreground hover:text-blue-500 transition-colors"
          >
            <ThumbsUp class="h-3.5 w-3.5" />
            <span>Helpful ({{ review.helpful_count || 0 }})</span>
          </button>
        </div>
        <div v-if="canEdit || canDelete" class="flex items-center gap-2">
          <Button
            v-if="canEdit"
            variant="ghost"
            size="sm"
            @click="$emit('edit', review)"
            class="text-xs"
          >
            Edit
          </Button>
          <Button
            v-if="canDelete"
            variant="ghost"
            size="sm"
            @click="$emit('delete', review.id)"
            class="text-xs text-red-500 hover:text-red-600"
          >
            Delete
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

