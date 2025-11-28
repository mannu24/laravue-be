<script setup>
import { useRoute, useRouter } from 'vue-router';
import { ref, onMounted, defineEmits, watch, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import PostCard from '../components/feed/PostCard.vue'
import CommentSection from '../components/CommentSection.vue'
import BackNavigator from '../components/elements/BackNavigator.vue'
import LoadingSpinner from '../components/ui/LoadingSpinner.vue'

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const postCode = computed(() => route.params.post_code);
const post = ref(null);
const loading = ref(true);
const emit = defineEmits(['share_url'])

const postBreadcrumbs = computed(() => {
    if (!post.value) return [{ name: 'Feed', href: '/feed' }];
    return [
        { name: 'Feed', href: '/feed' },
        { name: post.value.title || post.value.post_code || 'Post', href: `/feed/${post.value.post_code}` }
    ];
});

const fetchPost = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/api/v1/posts/post_${postCode.value}`, authStore.config);
        post.value = response.data;
    } catch (error) {
        console.error('Error fetching post:', error);
        post.value = null;
    } finally {
        loading.value = false;
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

watch(postCode, () => {
    fetchPost()
})
onMounted(fetchPost);
</script>

<template>
    <div class="max-w-7xl mx-auto py-5 min-h-[60vh]">
        <div v-if="loading" class="min-h-[60vh] flex flex-col items-center justify-center text-center gap-3">
            <LoadingSpinner size="lg" />
            <p class="text-gray-600 dark:text-gray-300">Loading post...</p>
        </div>

        <template v-else-if="post">
            <BackNavigator :items="postBreadcrumbs" />
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Post Card -->
                <div class="lg:col-span-2 lg:sticky lg:top-8 lg:self-start">
                    <PostCard 
                        @share_url="share_url" 
                        @liked_action="like_action" 
                        @delete_post="post_deleted" 
                        :post="post" 
                    />
                </div>
                
                <!-- Right Column: Comment Section -->
                <div class="lg:col-span-1">
                    <CommentSection 
                        @commentLiked="handleLike" 
                        @commentDeleted="handleDelete" 
                        @commentAdded="commentAdded"
                        type="post" 
                        :post_code="post.post_code" 
                        :comments="post.comments" 
                    />
                </div>
            </div>
        </template>

        <div v-else class="min-h-[60vh] flex flex-col items-center justify-center text-center gap-3">
            <p class="text-gray-600 dark:text-gray-300">Post not found or unavailable.</p>
            <button 
                class="text-vue hover:text-laravel transition-colors underline"
                @click="router.push('/feed')"
            >
                Back to Feed
            </button>
        </div>
    </div>
</template>
