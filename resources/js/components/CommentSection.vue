<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import CommentCard from './elements/CommentCard.vue';
import { Button } from '@/components/ui/button';

const { comments, post_code } = defineProps(['comments', 'post_code'])

const emit = defineEmits(['commentAdded', 'commentDeleted', 'commentLiked'])
const authStore = useAuthStore()
const text = ref('')
const isSubmitting = ref(false)

const handleDelete = (id) => {
    emit('commentDeleted', id)
}

const handleLike = (data) => {
    emit('commentLiked', data)
}

const handleSubmit = async () => {
    isSubmitting.value = true
    const response = await axios.post('/api/v1/post/comment', {
        code: post_code, content: text.value
    }, authStore.config)
    isSubmitting.value = false
    if(response.data.status == 'success') {
        text.value = ''
        emit('commentAdded', response.data.comment)
    }
}
</script>
<template>
    <div>
        <form @submit.prevent="handleSubmit">
            <textarea rows="3" required v-model="text" placeholder="Write a comment..."
            class="bg-gray-800/80 w-full shadow-md rounded-lg overflow-hidden text-white p-3 mb-4 focus:outline-0 hover:cursor-pointer hover:bg-gray-700/40 transition-all duration-350 ease-in" 
            ></textarea>
            <Button type="submit" class="bg-green-600 hover:bg-green-700" :disabled="isSubmitting">
                Submit Post
            </Button>
        </form>
        <TransitionGroup name="fade" appear>
            <CommentCard v-for="c,index in comments" :key="index" @commentLiked="handleLike" @commentDeleted="handleDelete" :comment="c" />
        </TransitionGroup>
    </div>
</template>