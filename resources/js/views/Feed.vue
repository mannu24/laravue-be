<script setup>
import { ref, defineEmits } from 'vue';
import InfiniteFeed from '../components/feed/InfiniteFeed.vue'
import PostForm from '../components/feed/PostForm.vue';
import { useAuthStore } from '../stores/auth.js';

const key = ref(0);
const authStore = useAuthStore();
const emit = defineEmits(['share_url'])

const share_url = (url) => {
    emit('share_url', url)
}

const fetch = () => {
    key.value++ ;
}

</script>
<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-0 pb-12">
        <PostForm v-if="authStore.isAuthenticated" @fetch="fetch"></PostForm>
    </div>
    <InfiniteFeed :fetchKey="key" @share_url="share_url" :username="null"></InfiniteFeed>
</template>