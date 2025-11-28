<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import CommentCard from './elements/CommentCard.vue'
import { Button } from '@/components/ui/button'

const { comments, post_code } = defineProps(['comments', 'post_code'])

const emit = defineEmits(['commentAdded', 'commentDeleted', 'commentLiked'])
const authStore = useAuthStore()
const text = ref('')
const isSubmitting = ref(false)

const hasComments = computed(() => (comments || []).length > 0)


const handleDelete = (id) => {
    emit('commentDeleted', id)
}

const handleLike = (data) => {
    emit('commentLiked', data)
}

const handleSubmit = async () => {
    if (!text.value.trim() || isSubmitting.value) return

    try {
        isSubmitting.value = true
        const response = await axios.post(
            '/api/v1/post/comment',
            { code: post_code, content: text.value.trim() },
            authStore.config
        )

        if (response.data.status === 'success') {
            text.value = ''
            emit('commentAdded', response.data.comment)
        }
    } finally {
        isSubmitting.value = false
    }
}
</script>

<template>
    <section class="bg-card border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm space-y-5 p-5">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Comments</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ comments?.length || 0 }} total</span>
        </div>
        <div class="space-y-3">
            <TransitionGroup name="fade" appear>
                <CommentCard
                    v-for="comment, index in comments"
                    :key="index"
                    @commentLiked="handleLike"
                    @commentDeleted="handleDelete"
                    :comment="comment"
                />
            </TransitionGroup>
    
            <p v-if="!hasComments" class="text-sm text-gray-500 dark:text-gray-400 text-center py-8 border rounded-lg border-dashed border-gray-200 dark:border-gray-700">
                No comments yet. Be the first to share your thoughts!
            </p>
        </div>
        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
            <form @submit.prevent="handleSubmit" class="space-y-3">
                <textarea
                    rows="3"
                    required
                    v-model="text"
                    placeholder="Share your thoughts..."
                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-400 px-4 py-3 text-sm transition-all duration-200 focus:outline-none focus:border-green-500 dark:focus:border-green-500 hover:border-gray-400 dark:hover:border-gray-500 resize-none"
                ></textarea>
                <div class="flex items-center justify-end gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="text = ''"
                        :disabled="!text.length || isSubmitting"
                    >
                        Clear
                    </Button>
                    <Button
                        type="submit"
                        size="sm"
                        class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white shadow-md"
                        :disabled="isSubmitting || !text.length"
                    >
                        <span v-if="!isSubmitting">Post</span>
                        <span v-else class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Posting…
                        </span>
                    </Button>
                </div>
            </form>
        </div>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>