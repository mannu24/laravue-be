<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import RatingDisplay from './RatingDisplay.vue'
import { Star, Loader2 } from 'lucide-vue-next'

const props = defineProps({
  review: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({
  rating: props.review?.rating || 0,
  comment: props.review?.comment || ''
})

const hoveredRating = ref(0)

const setRating = (rating) => {
  formData.value.rating = rating
}

const handleSubmit = () => {
  if (formData.value.rating === 0) {
    return
  }
  emit('submit', formData.value)
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Write a Review</CardTitle>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Rating -->
        <div>
          <Label>Rating *</Label>
          <div class="flex items-center gap-2 mt-2">
            <div class="flex items-center gap-1">
              <Star
                v-for="i in 5"
                :key="i"
                :class="[
                  'h-6 w-6 cursor-pointer transition-colors',
                  i <= (hoveredRating || formData.rating)
                    ? 'fill-yellow-400 text-yellow-400'
                    : 'text-gray-300 hover:text-yellow-300'
                ]"
                @click="setRating(i)"
                @mouseenter="hoveredRating = i"
                @mouseleave="hoveredRating = 0"
              />
            </div>
            <span v-if="formData.rating > 0" class="text-sm text-muted-foreground">
              {{ formData.rating }} out of 5
            </span>
          </div>
        </div>

        <!-- Comment -->
        <div>
          <Label for="comment">Your Review</Label>
          <Textarea
            id="comment"
            v-model="formData.comment"
            placeholder="Share your experience with this project..."
            rows="4"
            maxlength="2000"
          />
          <p class="text-xs text-muted-foreground mt-1">
            {{ formData.comment.length }}/2000 characters
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Button
            type="button"
            variant="outline"
            @click="handleCancel"
            :disabled="loading"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="loading || formData.rating === 0"
          >
            <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
            {{ review ? 'Update Review' : 'Submit Review' }}
          </Button>
        </div>
      </form>
    </CardContent>
  </Card>
</template>

