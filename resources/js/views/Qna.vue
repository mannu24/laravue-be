<script setup>
import InfiniteScroll from '../components/elements/InfiniteScroll.vue'
import {Button} from '../components/ui/button'

const emit = defineEmits(['share_url'])

const share_url = (url) => {
    emit('share_url', url)
}
</script>
<template>
    <div class="min-h-screen">
        <div class="max-w-4xl mx-auto py-5">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-white/10 pb-5 mb-5">
                <h1 class="text-3xl font-semibold">Laravue Q&A</h1>
                <Button variant="default" @click="$router.push('/qna/ask')">Ask a Question</Button>
            </div>
            <div class="space-y-6">
                <InfiniteScroll scrolling="qna" @share_url="share_url" :username="null"></InfiniteScroll>
            </div>
        </div>
    </div>
</template>
