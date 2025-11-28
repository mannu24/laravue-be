<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu'
import { Menu, X, Zap, Sun, Moon } from 'lucide-vue-next'
import NotificationDropdown from './notifications/NotificationDropdown.vue'
import SearchDropdown from './search/SearchDropdown.vue'
import { useAuthStore } from '../stores/auth.js'
import { useThemeStore } from '../stores/theme.js'

const router = useRouter()
const route = useRoute()
const isMobileMenuOpen = ref(false)
const authStore = useAuthStore()
const themeStore = useThemeStore()

const navigation = computed(() => [
    { name: 'Home', href: '/', current: route.path === '/' },
    { name: 'About', href: '/about', current: route.path === '/about' },
    { name: 'Projects', href: '/projects', current: route.path === '/projects' },
    { name: 'Feed', href: '/feed', current: route.path === '/feed' },
    { name: 'QNA', href: '/qna', current: route.path === '/qna' },
    { name: 'Contact', href: '/contact', current: route.path === '/contact' },
])

onMounted(() => {
    themeStore.initTheme()
})
defineOptions({
  name: 'Navbar'
})
</script>

<template>
    <div>
        <nav :class="['sticky top-0 z-50 w-full backdrop-blur-xl transition-all duration-300 border-b',
            themeStore.isDark ? 'bg-gray-950/90 border-gray-800' : 'bg-white/90 border-gray-200'
        ]">
            <!-- Background overlay for glass effect -->
            <div class="absolute inset-0 -z-10" :class="[
                'transition-all duration-500',
                themeStore.isDark
                    ? 'bg-gradient-to-r from-gray-950/95 via-gray-900/90 to-gray-950/95'
                    : 'bg-gradient-to-r from-white/95 via-gray-50/90 to-white/95'
            ]"></div>
    
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo with enhanced styling -->
                    <div class="flex items-center">
                        <router-link to="/" class="group flex items-center space-x-3 transition-all duration-300">
                            <div class="relative">
                                <!-- <div
                                    class="absolute inset-0 rounded-lg bg-gradient-to-r from-primary to-primary/80 opacity-20 blur-sm group-hover:opacity-30 transition-opacity duration-300">
                                </div> -->
                                <img src="/assets/front/logo/logo.png" class="relative h-10 w-auto rounded-lg" alt="LaraVue Logo" />
                            </div>
                            <div class="">
                                <span class="text-xl mb-0 font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                    LaraVue
                                </span>
                                <div class="text-xs text-muted-foreground font-medium">Community</div>
                            </div>
                        </router-link>
                    </div>
                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center space-x-1">
                        <NavigationMenu>
                            <NavigationMenuList class="space-x-1">
                                <NavigationMenuItem v-for="item in navigation" :key="item.name">
                                    <router-link :to="item.href" :class="[
                                        'relative px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300',
                                        'text-gray-700 dark:text-gray-300 dark:hover:text-white hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10',
                                        'focus:outline-none focus:ring-2 focus:ring-primary/20',
                                        item.current
                                            ? 'bg-gradient-to-r from-primary/10 to-secondary/10 shadow-lg dark:text-white text-gray-900'
                                            : 'text-gray-700 dark:text-gray-300 dark:hover:text-white hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10'
                                    ]">
                                        {{ item.name }}
                                        <div v-if="item.current"
                                            class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-6 h-0.5 bg-gradient-to-r from-primary to-primary/80 rounded-full">
                                        </div>
                                    </router-link>
                                </NavigationMenuItem>
                            </NavigationMenuList>
                        </NavigationMenu>
                    </div>
                    <!-- Right side controls -->
                    <div class="flex items-center lg:space-x-3">
                        <!-- Search -->
                        <SearchDropdown />
                        <!-- Theme Toggle -->
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="themeStore.toggleTheme()"
                            class="relative hover:bg-transparent hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300 text-gray-700 dark:text-gray-300 dark:hover:text-white"
                            :title="themeStore.isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        >
                            <Sun v-if="themeStore.isDark" class="h-5 w-5" />
                            <Moon v-else class="h-5 w-5" />
                        </Button>
                        <NotificationDropdown v-if="authStore.isAuthenticated" />
                        <template v-if="authStore.isAuthenticated">
                            <Button 
                                variant="ghost"
                                @click="router.push(`/dashboard`)"
                                class="flex items-center space-x-2 !px-2 hover:bg-gray-100 dark:hover:bg-gradient-to-r dark:hover:from-primary/10 dark:hover:to-secondary/10 transition-all duration-300 text-gray-700 dark:text-gray-300">
                                <div v-if="!authStore?.user?.profile_photo" class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center leading-none text-white text-sm font-bold">
                                    {{ authStore?.user?.username?.charAt(0).toUpperCase() }}
                                </div>
                                <img v-else :src="authStore?.user?.profile_photo" alt="Profile Photo" class="w-8 h-8 rounded-full object-cover">
                            </Button>
                        </template>
                        <template v-else>
                            <Button @click="router.push('/login')"
                                class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-white shadow-lg hover:shadow-xl transition-all duration-300">
                                <Zap class="mr-50" />
                                Sign In
                            </Button>
                        </template>
                        <Button variant="ghost" size="icon"
                            class="lg:hidden hover:bg-gray-100 dark:hover:bg-gradient-to-r dark:hover:from-primary/10 dark:hover:to-secondary/10 transition-all duration-300 text-gray-700 dark:text-gray-300"
                            @click="isMobileMenuOpen = !isMobileMenuOpen">
                            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                            <span class="sr-only">Toggle menu</span>
                        </Button>
                    </div>
                </div>
            </div>
            <Transition name="fade">
                <div v-if="isMobileMenuOpen" class="lg:hidden backdrop-blur-xl border-t transition-all duration-300" :class="[
                    themeStore.isDark ? 'bg-gray-950/95 border-gray-800' : 'bg-white/95 border-gray-200'
                ]">
                    <div class="absolute inset-0 -z-10" :class="[
                        'transition-all duration-500',
                        themeStore.isDark
                            ? 'bg-gradient-to-br from-gray-950/95 via-vue/15 to-gray-950/95'
                            : 'bg-gradient-to-br from-white/95 via-vue/25 to-white/95'
                    ]"></div>
        
                    <div class="space-y-2 px-4 py-2 grid grid-cols-3">
                        <router-link v-for="item in navigation" :key="item.name" :to="item.href"
                            class="flex items-center justify-center lg:justify-start px-4 py-3 rounded-lg text-base font-medium transition-all duration-300"
                            :class="[
                                item.current
                                    ? 'text-foreground bg-gradient-to-r from-primary/10 to-secondary/10 shadow-lg'
                                    : 'text-muted-foreground hover:text-foreground hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10'
                            ]" @click="isMobileMenuOpen = false">
                            {{ item.name }}
                        </router-link>
                    </div>
                </div>
            </Transition>
        </nav>
    </div>
</template>

<style scoped>
/* Enhance the glass effect */
.backdrop-blur-xl {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
