<script setup>
import { ref, defineEmits } from 'vue';
import InfiniteScroll from '../components/elements/InfiniteScroll.vue'
import PostForm from '../components/feed/PostForm.vue';
import { useAuthStore } from '../stores/auth.js';
import { useThemeStore } from '../stores/theme'

const key = ref(0);
const authStore = useAuthStore();
const themeStore = useThemeStore()
const emit = defineEmits(['share_url'])

const share_url = (url) => {
    emit('share_url', url)
}

const fetch = () => {
    key.value++ ;
}

</script>
<template>
    <div :class="[
        'min-h-screen transition-colors duration-300',
        themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
    ]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Post Form Section -->
            <div class="mb-8">
                <PostForm @fetch="fetch"></PostForm>
            </div>
            
            <!-- Feed Section -->
            <div class="space-y-6">
                <InfiniteScroll scrolling="post" :fetchKey="key" @share_url="share_url" :username="null"></InfiniteScroll>
            </div>
        </div>
    </div>
</template>