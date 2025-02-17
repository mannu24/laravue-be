<script setup>
import InfiniteScroll from '../components/elements/InfiniteScroll.vue'
import {Button} from '../components/ui/button'

const emit = defineEmits(['share_url'])

const share_url = (url) => {
    emit('share_url', url)
}
</script>
<template>
    <div>
        <div class="flex justify-between">
            <h1 class="text-3xl font-bold mb-6">Latest Questions</h1>
            <Button variant="default" @click="$router.push('/qna/ask')">Ask a Question</Button>
        </div>
        <InfiniteScroll scrolling="qna" @share_url="share_url" :username="null"></InfiniteScroll>
    </div>
</template>
