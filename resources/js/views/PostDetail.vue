<script setup>
import { useRoute, useRouter } from 'vue-router';
import { ref, onMounted, defineEmits } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import PostCard from '../components/feed/PostCard.vue'
import CommentSection from '../components/CommentSection.vue'

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const postcode = route.params.post_code;
const post = ref(null);
const emit = defineEmits(['share_url'])

const fetchPost = async () => {
    try {
        const response = await axios.get(`/api/v1/posts/post_${postcode}`, authStore.config);
        post.value = response.data;
    } catch (error) {
        console.error('Error fetching post:', error);
    }
};

const share_url = (url) => {
    emit('share_url', url)
}
const post_deleted = (value) => {
    router.push('/feed')
}

const like_action = async (data) => {
    post.value.liked = data[1]
    if (data[1]) {
        post.value.likes_count++
    } else {
        post.value.likes_count--
    }
}

const handleLike = async (data) => {
    post.value.comments.find(i => i.id == data[0]).liked = data[1]
    if (data[1]) {
        post.value.comments.find(i => i.id == data[0]).likes_count++
    } else {
        post.value.comments.find(i => i.id == data[0]).likes_count--
    }
}

const handleDelete = (id) => {
    post.value.comments = post.value.comments.filter((comment) => comment.id !== id)
    post.value.comments_count--
}

const commentAdded = (comment) => {
    post.value.comments.push(comment)
    post.value.comments_count++
}
onMounted(fetchPost);
</script>

<template>
    <div class="max-w-2xl mx-auto mt-[40px] sm:px-6 pb-5">
        <h4 class="text-vue hover:text-laravel transition-all duration-300 ease-in mb-5 cursor-pointer"
            @click="router.push('/feed')">
            <i class="fas fa-chevron-left fa-sm"></i><span class="text-lg"> Back</span>
        </h4>
        <PostCard @share_url="share_url" @liked_action="like_action" @delete_post="post_deleted" v-if="post"
            :post="post" />
        <CommentSection @commentLiked="handleLike" @commentDeleted="handleDelete" @commentAdded="commentAdded"
            type="post" :post_code="post.post_code" v-if="post" :comments="post.comments" />
    </div>
</template>
