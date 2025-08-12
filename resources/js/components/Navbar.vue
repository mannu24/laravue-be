<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu'
import { Sun, Moon, Menu, X, ChevronDown, LogOut, User, Settings, Zap } from 'lucide-vue-next'
import { useAuthStore } from '../stores/auth.js'
import { useThemeStore } from '../stores/theme.js'
import axios from 'axios'

const router = useRouter()
const route = useRoute()
const errorMessage = ref('')
const isLoading = ref(false)
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

const logout = async () => {
    isLoading.value = true
    try {
        await axios.get('/api/v1/logout', authStore.config)
        authStore.clearAuthData()
        router.push('/login')
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Server Error Occurred.'
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    themeStore.initTheme()
})
</script>

<template>
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
                            <div
                                class="absolute inset-0 rounded-lg bg-gradient-to-r from-primary to-primary/80 opacity-20 blur-sm group-hover:opacity-30 transition-opacity duration-300">
                            </div>
                            <img src="/assets/front/logo/logo.png" class="relative h-8 w-auto rounded-lg"
                                alt="LaraVue Logo" />
                        </div>
                        <div class="hidden sm:block">
                            <span
                                class="text-xl font-bold bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-transparent">
                                <span class="text-primary">Lara</span><span class="text-secondary">Vue</span>
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
                                    'hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10',
                                    'hover:text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20',
                                    item.current
                                        ? 'text-foreground bg-gradient-to-r from-primary/10 to-secondary/10 shadow-lg'
                                        : 'text-muted-foreground hover:text-foreground'
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
                <div class="flex items-center space-x-3">
                    <!-- Theme Toggle -->
                    <Button variant="ghost" size="icon" @click="themeStore.toggleTheme()"
                        class="relative overflow-hidden hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                        <Sun v-if="themeStore.isDark" class="h-5 w-5 rotate-0 scale-100 transition-all duration-300" />
                        <Moon v-else class="h-5 w-5 rotate-0 scale-100 transition-all duration-300" />
                        <span class="sr-only">Toggle theme</span>
                    </Button>

                    <!-- Auth Section -->
                    <template v-if="authStore.isAuthenticated">
                        <DropdownMenu>
                            <DropdownMenuTrigger as="div">
                                <Button variant="ghost"
                                    class="flex items-center space-x-2 hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-sm font-bold">
                                        {{ authStore?.user?.username?.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="hidden sm:block font-medium">{{ authStore?.user?.username }}</span>
                                    <ChevronDown class="h-4 w-4 transition-transform duration-200" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-56 mt-2 backdrop-blur-xl border-0 shadow-2xl"
                                :class="themeStore.isDark ? 'bg-gray-900/95' : 'bg-white/95'">
                                <div
                                    class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-primary/80">
                                </div>
                                <div class="p-2">
                                    <div class="px-2 py-1.5 text-sm text-muted-foreground">
                                        Signed in as <span class="font-medium text-foreground">{{
                                            authStore?.user?.username }}</span>
                                    </div>
                                </div>
                                <DropdownMenuItem @click="router.push(`/profile/@${authStore?.user?.username}`)"
                                    class="cursor-pointer">
                                    <User class="mr-2 h-4 w-4" />
                                    Profile
                                </DropdownMenuItem>
                                <DropdownMenuItem class="cursor-pointer">
                                    <Settings class="mr-2 h-4 w-4" />
                                    Settings
                                </DropdownMenuItem>
                                <div class="h-px bg-border my-1"></div>
                                <DropdownMenuItem @click="logout" :disabled="isLoading"
                                    class="cursor-pointer text-primary">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    <span>{{ isLoading ? 'Logging out...' : 'Logout' }}</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>

                    <template v-else>
                        <Button variant="ghost" @click="router.push('/login')"
                            class="hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                            Login
                        </Button>
                        <Button @click="router.push('/signup')"
                            class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300">
                            <Zap class="h-4 w-4 mr-2" />
                            Sign up
                        </Button>
                    </template>

                    <!-- Mobile Menu Button -->
                    <Button variant="ghost" size="icon"
                        class="lg:hidden hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300"
                        @click="isMobileMenuOpen = !isMobileMenuOpen">
                        <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                        <span class="sr-only">Toggle menu</span>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isMobileMenuOpen" class="lg:hidden backdrop-blur-xl border-t transition-all duration-300" :class="[
            themeStore.isDark ? 'bg-gray-950/95 border-gray-800' : 'bg-white/95 border-gray-200'
        ]">
            <div class="absolute inset-0 -z-10" :class="[
                'transition-all duration-500',
                themeStore.isDark
                    ? 'bg-gradient-to-br from-gray-950/95 via-primary/10 to-gray-950/95'
                    : 'bg-gradient-to-br from-white/95 via-primary/5 to-white/95'
            ]"></div>

            <div class="space-y-2 px-4 py-6">
                <router-link v-for="item in navigation" :key="item.name" :to="item.href"
                    class="flex items-center px-4 py-3 rounded-lg text-base font-medium transition-all duration-300"
                    :class="[
                        item.current
                            ? 'text-foreground bg-gradient-to-r from-primary/10 to-secondary/10 shadow-lg'
                            : 'text-muted-foreground hover:text-foreground hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10'
                    ]" @click="isMobileMenuOpen = false">
                    {{ item.name }}
                </router-link>
            </div>

            <!-- Mobile Auth Section -->
            <div class="border-t px-4 py-4" :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-200'">
                <div v-if="!authStore.isAuthenticated" class="flex flex-col space-y-3">
                    <Button variant="ghost"
                        class="w-full justify-center hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300"
                        @click="router.push('/login')">
                        Login
                    </Button>
                    <Button
                        class="w-full justify-center bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg"
                        @click="router.push('/signup')">
                        <Zap class="h-4 w-4 mr-2" />
                        Sign up
                    </Button>
                </div>

                <div v-else class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center text-primary-foreground font-bold">
                            {{ authStore?.user?.username?.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-medium text-foreground">{{ authStore?.user?.username }}</div>
                            <div class="text-sm text-muted-foreground">Authenticated</div>
                        </div>
                    </div>
                    <Button variant="ghost" size="icon" class="text-primary hover:bg-primary/10"
                        @click="logout" :disabled="isLoading">
                        <LogOut class="h-5 w-5" />
                    </Button>
                </div>
            </div>
        </div>
    </nav>
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
