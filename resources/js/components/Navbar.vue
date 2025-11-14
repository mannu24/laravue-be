<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
} from '@/components/ui/navigation-menu'
import { Sun, Moon, Menu, X, Zap, Search, Loader2, FileText, MessageSquare, FolderOpen, Users, ChevronRight, Clock } from 'lucide-vue-next'
import NotificationDropdown from './notifications/NotificationDropdown.vue'
import { useAuthStore } from '../stores/auth.js'
import { useThemeStore } from '../stores/theme.js'
import { useDebounceFn } from '@vueuse/core'
import axios from 'axios'
import { useRecentSearches } from '../composables/useRecentSearches'

const router = useRouter()
const route = useRoute()
const showSearch = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const totalSearchResults = ref(0)
const hasMoreResults = ref(false)
const isSearching = ref(false)
const searchInputRef = ref(null)
const searchModalRef = ref(null)
const searchButtonRef = ref(null)
const isMobileMenuOpen = ref(false)
const authStore = useAuthStore()
const themeStore = useThemeStore()

// Recent searches functionality
const { recentSearches, addRecentSearch, removeRecentSearch, clearRecentSearches, getTimeAgo } = useRecentSearches()

const navigation = computed(() => [
    { name: 'Home', href: '/', current: route.path === '/' },
    { name: 'About', href: '/about', current: route.path === '/about' },
    { name: 'Projects', href: '/projects', current: route.path === '/projects' },
    { name: 'Feed', href: '/feed', current: route.path === '/feed' },
    { name: 'QNA', href: '/qna', current: route.path === '/qna' },
    { name: 'Contact', href: '/contact', current: route.path === '/contact' },
])


// Debounced search function
const performSearch = useDebounceFn(async (query) => {
    if (query.length < 3) {
        searchResults.value = []
        return
    }

    isSearching.value = true
    try {
        // For navbar dropdown: use limit=10 without pagination (no per_page parameter)
        const response = await axios.get(`/api/v1/search?q=${encodeURIComponent(query)}&include_trending=false&include_recommended=false&limit=10`, authStore.config)
        if (response.data.status === 'success' && response.data.data) {
            const data = response.data.data
            // Combine all results into a single array (max 10 for dropdown)
            const allResults = [
                ...(data.posts || []).map(item => ({ ...item, icon: FileText })),
                ...(data.questions || []).map(item => ({ ...item, icon: MessageSquare })),
                ...(data.users || []).map(item => ({ ...item, icon: Users })),
                ...(data.projects || []).map(item => ({ ...item, icon: FolderOpen })),
            ]
            // Limit to 10 results for dropdown
            searchResults.value = allResults.slice(0, 10)
            totalSearchResults.value = data.meta?.total_results || 0
            hasMoreResults.value = data.meta?.has_more || allResults.length > 10
        } else {
            searchResults.value = []
            totalSearchResults.value = 0
            hasMoreResults.value = false
        }
    } catch (error) {
        console.error('Search error:', error)
        searchResults.value = []
        totalSearchResults.value = 0
        hasMoreResults.value = false
    } finally {
        isSearching.value = false
    }
}, 500)

// Watch search query changes
watch(searchQuery, (newQuery) => {
    if (newQuery.length >= 3) {
        performSearch(newQuery)
    } else {
        searchResults.value = []
    }
})

// Close search modal
const closeSearch = () => {
    showSearch.value = false
    searchQuery.value = ''
    searchResults.value = []
    totalSearchResults.value = 0
    hasMoreResults.value = false
}

// Navigate to search results page
const viewAllResults = () => {
    if (searchQuery.value.length >= 3) {
        // Save to recent searches
        addRecentSearch(searchQuery.value)
        router.push({ 
            name: 'search-results', 
            query: { q: searchQuery.value } 
        })
        closeSearch()
    }
}

// Handle search result click
const handleResultClick = (result) => {
    if (result.url) {
        router.push(result.url)
        // Save to recent searches if it's a valid search query
        if (searchQuery.value && searchQuery.value.trim().length >= 3) {
            addRecentSearch(searchQuery.value)
        }
        closeSearch()
    } else if (result.type === 'user' && result.username) {
        router.push(`/@${result.username}`)
        // Save to recent searches if it's a valid search query
        if (searchQuery.value && searchQuery.value.trim().length >= 3) {
            addRecentSearch(searchQuery.value)
        }
        closeSearch()
    }
}

// Handle recent search click
const handleRecentSearchClick = (query) => {
    searchQuery.value = query
    performSearch(query)
    // Move to top of recent searches
    addRecentSearch(query)
}

// Handle remove recent search
const handleRemoveRecentSearch = (e, query) => {
    e.stopPropagation()
    removeRecentSearch(query)
}

// Handle clear all recent searches
const handleClearAllRecentSearches = (e) => {
    e.stopPropagation()
    clearRecentSearches()
}

// Handle keyboard navigation
const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        closeSearch()
    }
}

// Handle Ctrl+K / Cmd+K to open search
const handleGlobalKeydown = (e) => {
    // Check for Ctrl+K (Windows/Linux) or Cmd+K (Mac)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault()
        if (!showSearch.value) {
            showSearch.value = true
        }
    }
}

// Focus input when modal opens
watch(showSearch, async (isOpen) => {
    if (isOpen) {
        await nextTick()
        // Use querySelector to find the input element within the modal
        // This is more reliable than accessing component refs in Vue 3
        try {
            const input = searchModalRef.value?.querySelector?.('input[type="text"]')
            if (input && typeof input.focus === 'function') {
                input.focus()
            }
        } catch (error) {
            console.warn('Could not focus search input:', error)
        }
        document.addEventListener('keydown', handleKeydown)
    } else {
        document.removeEventListener('keydown', handleKeydown)
    }
})

// Close on outside click
const handleClickOutside = (event) => {
    // Don't close if clicking the search button or its children
    if (searchButtonRef.value && searchButtonRef.value.contains(event.target)) {
        return
    }
    // Close if clicking outside the modal
    if (searchModalRef.value && !searchModalRef.value.contains(event.target)) {
        closeSearch()
    }
}

// Watch for modal open/close to attach/detach click listener
watch(showSearch, (isOpen) => {
    if (isOpen) {
        // Use setTimeout to avoid immediate trigger from the button click
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside)
        }, 100)
    } else {
        document.removeEventListener('click', handleClickOutside)
    }
})

onMounted(() => {
    themeStore.initTheme()
    // Add global keyboard shortcut for search (Ctrl+K / Cmd+K)
    document.addEventListener('keydown', handleGlobalKeydown)
})

// Cleanup
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleKeydown)
    document.removeEventListener('keydown', handleGlobalKeydown)
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
                        <Button ref="searchButtonRef" variant="ghost" size="icon" @click="showSearch = true" class="relative overflow-hidden hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                            <Search class="h-5 w-5 rotate-0 scale-100 transition-all duration-300" />
                            <span class="sr-only">Search</span>
                        </Button>
                        <Button variant="ghost" size="icon" @click="themeStore.toggleTheme()" class="relative overflow-hidden hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                            <Sun v-if="themeStore.isDark" class="h-5 w-5 rotate-0 scale-100 transition-all duration-300" />
                            <Moon v-else class="h-5 w-5 rotate-0 scale-100 transition-all duration-300" />
                            <span class="sr-only">Toggle theme</span>
                        </Button>
                        <NotificationDropdown v-if="authStore.isAuthenticated" />
                        <template v-if="authStore.isAuthenticated">
                            <Button 
                                variant="ghost"
                                @click="router.push(`/profile/@${authStore?.user?.username}`)"
                                class="flex items-center space-x-2 hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-sm font-bold">
                                    {{ authStore?.user?.username?.charAt(0).toUpperCase() }}
                                </div>
                                <span class="hidden sm:block font-medium">{{ authStore?.user?.username }}</span>
                            </Button>
                        </template>
                        <template v-else>
                            <Button @click="router.push('/login')"
                                class="bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70 text-primary-foreground shadow-lg hover:shadow-xl transition-all duration-300">
                                <Zap class="mr-50" />
                                Sign In
                            </Button>
                        </template>
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
                        <Button 
                            variant="ghost"
                            @click="router.push(`/profile/@${authStore?.user?.username}`); isMobileMenuOpen = false"
                            class="flex items-center space-x-3 w-full justify-start hover:bg-gradient-to-r hover:from-primary/10 hover:to-secondary/10 transition-all duration-300">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-primary/80 flex items-center justify-center text-primary-foreground font-bold">
                                {{ authStore?.user?.username?.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-medium text-foreground">{{ authStore?.user?.username }}</div>
                                <div class="text-sm text-muted-foreground">View Profile</div>
                            </div>
                        </Button>
                    </div>
                </div>
            </div>
        </nav>
        <Transition name="fade">
            <div v-show="showSearch" @click.self="closeSearch" class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4 bg-black/60 backdrop-blur-sm">
                <div ref="searchModalRef" class="relative w-full max-w-2xl transition-all duration-300 ease-out" :class="showSearch ? 'opacity-100 scale-100' : 'opacity-0 scale-95'">
                    <div class="relative rounded-2xl shadow-2xl border transition-all duration-300" :class="[themeStore.isDark ? 'bg-gray-900/95 border-gray-800' : 'bg-white/95 border-gray-200']">
                        <div class="flex items-center gap-3 px-4 py-4">
                            <Search class="h-5 w-5 flex-shrink-0" :class="[
                                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                            ]" />
                            <Input 
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Search posts, questions, projects, users..."
                                class="flex-1 border-0 bg-transparent text-base focus-visible:ring-0 focus-visible:ring-offset-0 placeholder:text-gray-400"
                                :class="[
                                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                                ]"
                                @keydown.escape="closeSearch"
                            />
                            <div class="flex items-center gap-2">
                                <Loader2 v-if="isSearching" class="h-4 w-4 animate-spin text-gray-400" />
                                <Button 
                                    variant="ghost" 
                                    size="icon" 
                                    @click="closeSearch"
                                    :class="[
                                        'h-8 w-8 rounded-lg transition-colors',
                                        themeStore.isDark 
                                            ? 'hover:bg-gray-800/70' 
                                            : 'hover:bg-gray-100'
                                    ]">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        <!-- Search Results Dropdown -->
                        <Transition name="slide-down">
                            <div v-if="searchResults.length > 0 || isSearching || (searchQuery.length === 0 && recentSearches.length > 0)" 
                                class="absolute top-full left-0 right-0 mt-1 max-h-96 overflow-y-auto rounded-xl shadow-xl border backdrop-blur-xl transition-all duration-200 z-50"
                                :class="[
                                    themeStore.isDark 
                                        ? 'bg-gray-900/95 border-gray-800' 
                                        : 'bg-white/95 border-gray-200'
                                ]">
                                <!-- Recent Searches (shown when no query and no results) -->
                                <div v-if="!isSearching && searchQuery.length === 0 && recentSearches.length > 0" class="pt-2">
                                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center justify-between" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                    ]">
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-3.5 w-3.5" />
                                            <span>Recent Searches</span>
                                        </div>
                                        <button 
                                            @click="handleClearAllRecentSearches"
                                            class="text-xs normal-case font-normal hover:underline transition-colors"
                                            :class="[
                                                themeStore.isDark 
                                                    ? 'text-gray-400 hover:text-gray-300' 
                                                    : 'text-gray-500 hover:text-gray-700'
                                            ]">
                                            Clear all
                                        </button>
                                    </div>
                                    <div 
                                        v-for="(search, index) in recentSearches" 
                                        :key="`recent-${search.query}-${search.timestamp}`"
                                        @click="handleRecentSearchClick(search.query)"
                                        class="flex items-center gap-3 px-4 py-3 cursor-pointer transition-colors duration-150 border-b last:border-b-0 group"
                                        :class="[
                                            themeStore.isDark 
                                                ? 'border-gray-800 hover:bg-gray-800/70 hover:border-gray-700' 
                                                : 'border-gray-100 hover:bg-gray-100'
                                        ]">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors" :class="[
                                            themeStore.isDark 
                                                ? 'bg-gray-800/50 text-gray-400' 
                                                : 'bg-gray-100 text-gray-500'
                                        ]">
                                            <Clock class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate" :class="[
                                                themeStore.isDark ? 'text-white' : 'text-gray-900'
                                            ]">
                                                {{ search.query }}
                                            </p>
                                            <p class="text-xs mt-0.5" :class="[
                                                themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                            ]">
                                                {{ getTimeAgo(search.timestamp) }}
                                            </p>
                                        </div>
                                        <button
                                            @click="handleRemoveRecentSearch($event, search.query)"
                                            class="flex-shrink-0 p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-200"
                                            :class="[
                                                themeStore.isDark 
                                                    ? 'hover:bg-gray-700 text-gray-400 hover:text-gray-300' 
                                                    : 'hover:bg-gray-200 text-gray-400 hover:text-gray-600'
                                            ]"
                                            title="Remove from recent searches"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div v-else-if="isSearching" class="p-8 text-center">
                                    <Loader2 class="h-6 w-6 animate-spin mx-auto mb-2" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                    ]" />
                                    <p class="text-sm" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                    ]">Searching...</p>
                                </div>

                                <!-- Results List -->
                                <div v-else-if="searchResults.length > 0" class="pt-2">
                                    <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center justify-between" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                    ]">
                                        <span>Results ({{ totalSearchResults > 0 ? totalSearchResults : searchResults.length }})</span>
                                        <span v-if="searchResults.length < totalSearchResults" class="text-xs normal-case font-normal">
                                            Showing {{ searchResults.length }} of {{ totalSearchResults }}
                                        </span>
                                    </div>
                                    <div 
                                        v-for="(result, index) in searchResults" 
                                        :key="`${result.type}-${result.id || result.username || index}`"
                                        @click="handleResultClick(result)"
                                        class="flex items-center gap-3 px-4 py-3 cursor-pointer transition-colors duration-150 border-b last:border-b-0"
                                        :class="[
                                            themeStore.isDark 
                                                ? 'border-gray-800 hover:bg-gray-800/70 hover:border-gray-700' 
                                                : 'border-gray-100 hover:bg-gray-100'
                                        ]">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors" :class="[
                                            result.type === 'post' 
                                                ? (themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600') :
                                            result.type === 'question' 
                                                ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600') :
                                            result.type === 'project' 
                                                ? (themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600') :
                                                (themeStore.isDark ? 'bg-orange-900/30 text-orange-400' : 'bg-orange-100 text-orange-600')
                                        ]">
                                            <component :is="result.icon || FileText" class="h-5 w-5" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-medium px-2 py-0.5 rounded capitalize transition-colors" :class="[
                                                    result.type === 'post' 
                                                        ? (themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600') :
                                                    result.type === 'question' 
                                                        ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600') :
                                                    result.type === 'project' 
                                                        ? (themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600') :
                                                        (themeStore.isDark ? 'bg-orange-900/30 text-orange-400' : 'bg-orange-100 text-orange-600')
                                                ]">
                                                    {{ result.type }}
                                                </span>
                                                <span v-if="result.author && typeof result.author === 'object' && result.author.username" 
                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                    @{{ result.author.username }}
                                                </span>
                                                <span v-else-if="result.author && typeof result.author === 'string'" 
                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                    @{{ result.author }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-medium truncate" :class="[
                                                themeStore.isDark ? 'text-white' : 'text-gray-900'
                                            ]">
                                                {{ result.title || result.name || result.username }}
                                            </p>
                                            <p v-if="result.content || result.description" class="text-xs mt-1 line-clamp-1" :class="[
                                                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                            ]">
                                                {{ result.content || result.description }}
                                            </p>
                                            <div v-if="result.stats" class="flex items-center gap-3 mt-1 text-xs" :class="[
                                                themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                            ]">
                                                <span v-if="result.stats.likes !== undefined">
                                                    ❤️ {{ result.stats.likes }}
                                                </span>
                                                <span v-if="result.stats.comments !== undefined">
                                                    💬 {{ result.stats.comments }}
                                                </span>
                                                <span v-if="result.stats.answers !== undefined">
                                                    💡 {{ result.stats.answers }}
                                                </span>
                                                <span v-if="result.stats.views !== undefined">
                                                    👁️ {{ result.stats.views }}
                                                </span>
                                            </div>
                                        </div>
                                        <ChevronRight class="h-4 w-4 flex-shrink-0" :class="[
                                            themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                        ]" />
                                    </div>
                                    
                                    <!-- View All Button -->
                                    <div v-if="hasMoreResults || totalSearchResults > searchResults.length" 
                                        class="px-4 py-3 border-t" :class="[
                                            themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
                                        ]">
                                        <Button 
                                            @click="viewAllResults"
                                            variant="outline"
                                            class="w-full justify-center"
                                            :class="[
                                                themeStore.isDark 
                                                    ? 'hover:bg-gray-800' 
                                                    : 'hover:bg-gray-100'
                                            ]">
                                            View All Results ({{ totalSearchResults }})
                                            <ChevronRight class="h-4 w-4 ml-2" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- No Results -->
                                <div v-else-if="!isSearching && searchQuery.length >= 3" class="p-8 text-center">
                                    <Search class="h-8 w-8 mx-auto mb-2" :class="[
                                        themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                                    ]" />
                                    <p class="text-sm font-medium mb-1" :class="[
                                        themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                    ]">No results found</p>
                                    <p class="text-xs" :class="[
                                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                                    ]">Try different keywords</p>
                                </div>
                            </div>
                        </Transition>

                        <!-- Search Hint -->
                        <div v-if="searchQuery.length > 0 && searchQuery.length < 3" 
                            class="px-4 py-2 text-xs border-t" :class="[
                                themeStore.isDark 
                                    ? 'text-gray-400 border-gray-800 bg-gray-900/50' 
                                    : 'text-gray-500 border-gray-200 bg-gray-50'
                            ]">
                            Type at least 3 characters to search
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
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

/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Slide down transition for dropdown */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease-out;
}

.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Line clamp utility */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
