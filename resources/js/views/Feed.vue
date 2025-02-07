<script setup>
import { ref, defineEmits } from 'vue';
import InfiniteScroll from '../components/elements/InfiniteScroll.vue'
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
    <div class="w-full mx-auto sm:px-6 lg:px-0 pb-8">
        <PostForm v-if="authStore.isAuthenticated" @fetch="fetch"></PostForm>
    </div>
    <InfiniteScroll scrolling="post" :fetchKey="key" @share_url="share_url" :username="null"></InfiniteScroll>
</template>