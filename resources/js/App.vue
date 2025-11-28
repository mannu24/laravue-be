<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, computed, defineAsyncComponent, watch } from 'vue'
import { useRoute } from 'vue-router'
const Navbar = defineAsyncComponent(() => import('./components/Navbar.vue'))
import "../css/index.css"
import "../css/custom.css"
import { useThemeStore } from './stores/theme'
import { toast, Toaster } from './components/ui/toast'
import ToastContainer from './components/ui/ToastContainer.vue'
import { useGamificationRealtime } from './composables/useGamificationRealtime'
import CookieConsent from './components/CookieConsent.vue'
import NotificationConsentDialog from './components/NotificationConsentDialog.vue'
import AchievementPopups from './components/gamification/AchievementPopups.vue'
import { useGlobalDataStore } from './stores/globalData'

const themeStore = useThemeStore()
const route = useRoute()
const globalDataStore = useGlobalDataStore()
useGamificationRealtime()

// Computed property to determine dot color based on route
const dotColor = computed(() => {
    const path = route.path
    
    if (path.startsWith('/feed')) {
        // return '#41B883' // Vue green
        return '#347958' // Vue green
    } else if (path.startsWith('/qna')) {
        return '#cd180e' // Laravel red
    } else if (path.startsWith('/dashboard')) {
        return '#3b82f6' // Blue
    } else if (path.startsWith('/projects')) {
        return '#a855f7' // Purple
    } else if (path.startsWith('/about')) {
        return '#ef4444' // Danger red
    } else if (path.startsWith('/home') || path === '/') {
        // For white, use a subtle gray that works on both light/dark backgrounds
        return themeStore.isDark ? '#ffffff' : '#222222' // White for dark mode, light gray for light mode
    }
    
    // Default color
    return '#444cf7'
})

const updateDotColor = () => {
    const color = dotColor.value
    
    // For white/home route, adjust opacity based on theme
    if (route.path.startsWith('/home') || route.path === '/') {
        const opacity = themeStore.isDark ? '40' : '30'
        document.documentElement.style.setProperty('--polka-dot-color', color + opacity)
    } else {
        document.documentElement.style.setProperty('--polka-dot-color', color) // Add 60 for opacity
    }
}

const isScrollTopVisible = ref(false)

// Watch route changes and update CSS variable
watch([() => route.path, () => themeStore.isDark], () => {
    updateDotColor()
}, { immediate: true })

onMounted(() => {
    // Initialize theme on app mount
    themeStore.initTheme()
    // Initialize dot color
    updateDotColor()
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
            .then(() => toast({ title: "Link Copied Successfully!", description: "Paste the copied link anywhere to share." }))
            .catch(error => toast({ title: "Error sharing", description: error.message, variant: "destructive" }))
    } else {
        toast({ title: "Sharing not supported", description: "Your browser doesn't support the Web Share API.", variant: "destructive" })
    }
}
</script>

<template>

    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </head>
    <div :class="[
        'min-h-screen polka-dots',
        themeStore.isDark ? 'bg-gray-950' : 'bg-gray-50'
    ]">
        <!-- 1px Progress bar for global-data API loading -->
        <div v-if="globalDataStore.loading" class="fixed top-0 left-0 right-0 h-[1px] z-50 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 progress-bar-animate"></div>
        <Navbar />
        <main class="container px-4 lg:px-0">
            <router-view @share_url="shareUrl" />
        </main>
        <Transition name="fade">
            <button v-if="isScrollTopVisible"
                class="fixed right-5 bottom-5 flex justify-center items-center text-white w-10 h-10 transition-all duration-300 ease-in-out rounded-full hover:opacity-80 hover:scale-110 z-[100]"
                :style="{ backgroundColor: dotColor }"
                type="button" @click="scrollTop()">
                <i class="fas fa-arrow-up me-0"></i>
            </button>
        </Transition>
        <Toaster />
        <ToastContainer />
        <CookieConsent />
        <NotificationConsentDialog />
        <AchievementPopups />
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

.polka-dots {
    background-color: transparent;
    opacity: 1;
    background-image:  radial-gradient(var(--polka-dot-color) 0.85px, transparent 0.85px), radial-gradient(var(--polka-dot-color) 0.85px, transparent 0.85px);
    background-size: 27px 27px;
    background-position: 0 0, 13px 13px;
    transition: background-image 0.3s ease;
}

/* background-size: 54px 54px;
background-position: 0 0,27px 27px;
*/

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

.progress-bar-animate {
    animation: progress-bar 1.5s ease-in-out infinite;
    background-size: 200% 100%;
    background-position: -200% 0;
}

@keyframes progress-bar {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>
