<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Navbar from './components/Navbar.vue'
import { ArrowUp } from 'lucide-vue-next'
import "../css/index.css"
import "../css/custom.css"
import { useThemeStore } from './stores/theme'
import { toast, Toaster } from './components/ui/toast'

const themeStore = useThemeStore()

const isScrollTopVisible = ref(false)

onMounted(() => {
    window.addEventListener('scroll', handleScrollTop)
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScrollTop)
})

const handleScrollTop = () => {
    isScrollTopVisible.value = window.scrollY > 200
}

const scrollTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

const shareUrl = (data: string) => {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: "Check out this post",
            url: data,
        })
            .then(() => toast({ title: "Shared successfully", description: "The content has been shared." }))
            .catch(error => toast({ title: "Error sharing", description: error.message, variant: "destructive" }))
    } else {
        toast({ title: "Sharing not supported", description: "Your browser doesn't support the Web Share API.", variant: "destructive" })
    }
}
</script>

<template>
    <div :class="[
        'min-h-screen transition-colors duration-300',
        themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
    ]">
        <Navbar />
        <div class="container mx-auto p-4 grid gap-8">
            <main class="container mx-auto p-4 grid gap-8">
                <router-view @share_url="shareUrl" />
            </main>
        </div>
        <Transition name="fade">
            <Button v-if="isScrollTopVisible" variant="secondary" size="icon"
                class="fixed right-6 bottom-6 rounded-full shadow-lg" @click="scrollTop">
                <ArrowUp class="h-4 w-4" />
                <span class="sr-only">Scroll to top</span>
            </Button>
        </Transition>
        <Toaster />
    </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.9);
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