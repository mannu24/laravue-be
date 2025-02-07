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
import { Sun, Moon, Menu, X, ChevronDown, LogOut } from 'lucide-vue-next'
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
    { name: 'Projects', href: '/projects', current: route.path === '/projects' },
    { name: 'About', href: '/about', current: route.path === '/about' },
    { name: 'Contact', href: '/contact', current: route.path === '/contact' },
    { name: 'Feed', href: '/feed', current: route.path === '/feed' },
    { name: 'QNA', href: '/qna', current: route.path === '/qna' },
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
    <nav :class="[
        'sticky top-0 z-50 w-full border-b transition-colors duration-200',
        themeStore.isDark ? 'bg-gray-950 border-gray-800' : 'bg-white border-gray-200'
    ]">
        <div class="container mx-auto px-4">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <router-link to="/" class="flex items-center space-x-2">
                        <img src="/assets/front/logo/logo.png" class="h-8 w-auto" alt="Logo" />
                    </router-link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <NavigationMenu>
                        <NavigationMenuList>
                            <NavigationMenuItem v-for="item in navigation" :key="item.name">
                                <router-link :to="item.href" :class="[
                                    'px-3 py-2 cursor-pointer text-sm transition-colors hover:text-primary',
                                    item.current ? 'text-primary font-medium' : 'text-muted-foreground'
                                ]">
                                    {{ item.name }}
                                </router-link>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>

                    <!-- Theme Toggle -->
                    <Button variant="ghost" size="icon" @click="themeStore.toggleTheme()">
                        <Sun v-if="themeStore.isDark" class="h-5 w-5" />
                        <Moon v-else class="h-5 w-5" />
                        <span class="sr-only">Toggle theme</span>
                    </Button>

                    <!-- Auth Buttons -->
                    <template v-if="authStore.isAuthenticated">
                        <DropdownMenu>
                            <DropdownMenuTrigger as="div">
                                <Button variant="ghost" class="flex items-center space-x-1">
                                    <span>{{ authStore?.user?.username }}</span>
                                    <ChevronDown class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-48">
                                <DropdownMenuItem @click="router.push(`/profile/${authStore?.user?.username}`)">
                                    Profile
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="logout" :disabled="isLoading">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    <span>{{ isLoading ? 'Logging out...' : 'Logout' }}</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <template v-else>
                        <Button variant="ghost" @click="router.push('/login')">
                            Login
                        </Button>
                        <!-- <Button @click="router.push('/signup')">
              Sign up
            </Button> -->
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <Button variant="ghost" size="icon" class="md:hidden" @click="isMobileMenuOpen = !isMobileMenuOpen">
                    <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
                    <X v-else class="h-6 w-6" />
                    <span class="sr-only">Toggle menu</span>
                </Button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isMobileMenuOpen" class="md:hidden" :class="[
            'border-t transition-colors duration-200',
            themeStore.isDark ? 'border-gray-800' : 'border-gray-100'
        ]">
            <div class="space-y-1 px-4 py-3">
                <router-link v-for="item in navigation" :key="item.name" :to="item.href"
                    class="block px-3 py-2 rounded-md text-base font-medium transition-colors" :class="[
                        item.current
                            ? 'text-primary bg-primary/10'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                    ]" @click="isMobileMenuOpen = false">
                    {{ item.name }}
                </router-link>
            </div>

            <!-- Mobile Auth Buttons -->
            <div class="border-t px-4 py-3" :class="themeStore.isDark ? 'border-gray-800' : 'border-gray-100'">
                <div v-if="!authStore.isAuthenticated" class="flex flex-col space-y-2">
                    <Button variant="ghost" class="w-full justify-start" @click="router.push('/login')">
                        Login
                    </Button>
                    <Button class="w-full justify-start" @click="router.push('/signup')">
                        Sign up
                    </Button>
                </div>
                <div v-else>
                    <Button variant="ghost" class="w-full justify-start text-destructive" @click="logout"
                        :disabled="isLoading">
                        <LogOut class="mr-2 h-4 w-4" />
                        {{ isLoading ? 'Logging out...' : 'Logout' }}
                    </Button>
                </div>
            </div>
        </div>
    </nav>
</template>