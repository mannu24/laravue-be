<script setup>
import { ref,defineEmits } from 'vue';
import { useRoute } from 'vue-router';
import InfiniteFeed from '../components/feed/InfiniteFeed.vue'

const route = useRoute();
const username = route.params.username;
const tab = ref('Feed');
const emit = defineEmits(['share_url'])

const share_url = (url) => {
    emit('share_url', url)
}
</script>
<template>
    <div>
        <div class="max-w-2xl mx-auto sm:px-6 pb-[50px]">
            <ul class="hidden text-sm font-medium text-center text-gray-500 rounded-lg shadow-sm sm:flex dark:divide-gray-700 dark:text-gray-400">
                <li class="w-full focus-within:z-10">
                    <div :class="tab == 'Feed' ? 'text-white/90 bg-vue hover:text-white/100 hover:bg-vue/80' : 'bg-white hover:text-black hover:bg-gray-100'" class="cursor-pointer inline-block w-full p-4 rounded-s-lg focus:text-white focus:bg-vue/80 focus:outline-none" @click="tab='Feed'">Feed</div>
                </li>
                <li class="w-full focus-within:z-10">
                    <div :class="tab == 'Projects' ? 'text-white/90 bg-vue hover:text-white/100 hover:bg-vue/80' : 'bg-white hover:text-black hover:bg-gray-100'" class="cursor-pointer inline-block w-full p-4 focus:text-white focus:bg-vue/80 focus:outline-none" @click="tab='Projects'">Projects</div>
                </li>
                <li class="w-full focus-within:z-10">
                    <div :class="tab == 'Blogs' ? 'text-white/90 bg-vue hover:text-white/100 hover:bg-vue/80' : 'bg-white hover:text-black hover:bg-gray-100'" class="cursor-pointer inline-block w-full p-4 rounded-e-lg focus:text-white focus:bg-vue/80 focus:outline-none" @click="tab='Blogs'">Blogs</div>
                </li>
            </ul>
        </div>
        <Transition name="fade" mode="out-in">
            <InfiniteFeed v-if="tab=='Feed'" :fetchKey="null" @share_url="share_url" :username="username"></InfiniteFeed>
            <div class="max-w-7xl mx-auto rounded-lg bg-laravel p-5" v-else-if="tab=='Projects'"></div>
            <div class="max-w-7xl mx-auto rounded-lg bg-vue p-5" v-else-if="tab=='Blogs'"></div>
        </Transition>
    </div>
</template>
