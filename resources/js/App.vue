<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Navbar from './components/Navbar.vue'
import "../css/index.css"

const isScrollTopVisible = ref(false)
onMounted(() => {
    window.addEventListener('scroll', handleScrollTop);
});
onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScrollTop);
});
const handleScrollTop = () => {
    isScrollTopVisible.value = window.scrollY > 200;
}
const scollTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
const share_url = (data) => {
    if (navigator.share) {
        navigator.share({
            'title': document.title,
            'text': "Post",
            'url': data,
        }).then(() => console.log('Successful share'))
        .catch(error => console.log('Error sharing:', error));
    }
}
</script>
<template>
    <div class="min-h-screen bg-gray-900">
        <Navbar />
        <router-view @share_url="share_url" />
        <transition name="fade">
            <button v-if="isScrollTopVisible"
                class="fixed right-10 bottom-10 flex justify-center items-center text-white bg-vue/80 w-10 h-10 transition-all duration-300 ease-in-out rounded-full hover:bg-laravel/70 hover:scale-110"
                type="button" @click="scollTop()">
                <i class="fas fa-arrow-up me-0"></i>
            </button>
        </transition>
    </div>
</template>
<style>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
    transform-origin: center;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.bounce-enter-active {
    animation: bounce-in 0.3s;
}
.bounce-leave-active {
    animation: bounce-in 0.3s reverse;
}
@keyframes bounce-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.05);
        /* margin-bottom: 60px; */
    }
    100% {
        transform: scale(1);
    }
}
</style>