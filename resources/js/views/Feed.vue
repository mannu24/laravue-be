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
    <div :class="['min-h-screen transition-colors duration-300 space-y-12 py-5',
        themeStore.isDark ? 'bg-gray-950 text-gray-100' : 'bg-gray-50 text-gray-900'
    ]">
            <div class="w-full mx-auto sm:px-6 lg:px-0 pb-8">
            <PostForm @fetch="fetch"></PostForm>
        </div>
        <InfiniteScroll scrolling="post" :fetchKey="key" @share_url="share_url" :username="null"></InfiniteScroll>
    </div>
</template>