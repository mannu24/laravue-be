<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useThemeStore } from '../stores/theme'
import { useAuthStore } from '../stores/auth'
import { Button } from '../components/ui/button'
import { Input } from '../components/ui/input'
import { Card, CardContent } from '../components/ui/card'
import { 
    Search, 
    FileText, 
    MessageSquare, 
    FolderOpen, 
    Users, 
    ChevronRight,
    ChevronLeft,
    Filter,
    X,
    Loader2,
    Calendar,
    Tag,
    User,
    ChevronDown,
    ChevronUp
} from 'lucide-vue-next'
import { useDebounceFn } from '@vueuse/core'
import EmptyState from '../components/ui/EmptyState.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const themeStore = useThemeStore()
const authStore = useAuthStore()

const searchQuery = ref('')
const searchResults = ref({
    posts: [],
    questions: [],
    users: [],
    projects: []
})
const isLoading = ref(false)
const isLoadingMore = ref(false)
const currentPage = ref(1)
const perPage = ref(25)
const totalResults = ref(0)
const totalPages = ref(1)
const hasMore = ref(true)
const searchTime = ref(0)
const selectedType = ref('all')
const selectedSort = ref('relevance')
const observer = ref(null)
const loadMoreTrigger = ref(null)

// Advanced filters
const showAdvancedFilters = ref(false)
const authorFilter = ref('')
const selectedAuthor = ref(null)
const tagFilter = ref('')
const selectedTags = ref([])
const dateFrom = ref('')
const dateTo = ref('')

// Autocomplete suggestions
const authorSuggestions = ref([])
const tagSuggestions = ref([])
const showAuthorSuggestions = ref(false)
const showTagSuggestions = ref(false)
const authorInputRef = ref(null)
const tagInputRef = ref(null)

const typeOptions = [
    { value: 'all', label: 'All' },
    { value: 'post', label: 'Posts' },
    { value: 'question', label: 'Questions' },
    { value: 'user', label: 'Users' },
    { value: 'project', label: 'Projects' }
]

const sortOptions = [
    { value: 'relevance', label: 'Relevance' },
    { value: 'date', label: 'Newest' },
    { value: 'trending', label: 'Trending' },
    { value: 'popular', label: 'Popular' }
]

// Get all results as a flat array
const allResults = computed(() => {
    const results = []
    if (selectedType.value === 'all' || selectedType.value === 'post') {
        results.push(...searchResults.value.posts.map(item => ({ ...item, icon: FileText })))
    }
    if (selectedType.value === 'all' || selectedType.value === 'question') {
        results.push(...searchResults.value.questions.map(item => ({ ...item, icon: MessageSquare })))
    }
    if (selectedType.value === 'all' || selectedType.value === 'user') {
        results.push(...searchResults.value.users.map(item => ({ ...item, icon: Users })))
    }
    if (selectedType.value === 'all' || selectedType.value === 'project') {
        results.push(...searchResults.value.projects.map(item => ({ ...item, icon: FolderOpen })))
    }
    
    return results
})

const performSearch = async (isLoadMore = false) => {
    if (searchQuery.value.length < 3) {
        return
    }

    if (isLoadMore) {
        if (isLoadingMore.value || !hasMore.value) return
        isLoadingMore.value = true
    } else {
        if (isLoading.value) return
        isLoading.value = true
        currentPage.value = 1
        searchResults.value = {
            posts: [],
            questions: [],
            users: [],
            projects: []
        }
        hasMore.value = true
    }

    try {
        // For search results page: use per_page=25 with pagination
        const params = new URLSearchParams({
            q: searchQuery.value,
            type: selectedType.value,
            sort: selectedSort.value,
            page: currentPage.value.toString(),
            per_page: perPage.value.toString() // This triggers pagination mode
        })

        // Add advanced filters
        if (selectedAuthor.value?.username) {
            params.append('author', selectedAuthor.value.username)
        }
        if (selectedTags.value.length > 0) {
            params.append('tags', selectedTags.value.join(','))
        }
        if (dateFrom.value) {
            params.append('date_from', dateFrom.value)
        }
        if (dateTo.value) {
            params.append('date_to', dateTo.value)
        }

        const response = await axios.get(`/api/v1/search?${params.toString()}`, authStore.config)
        
        if (response.data.status === 'success' && response.data.data) {
            const data = response.data.data
            
            if (isLoadMore) {
                // Append new results
                searchResults.value = {
                    posts: [...searchResults.value.posts, ...(data.posts || [])],
                    questions: [...searchResults.value.questions, ...(data.questions || [])],
                    users: [...searchResults.value.users, ...(data.users || [])],
                    projects: [...searchResults.value.projects, ...(data.projects || [])]
                }
            } else {
                // Replace results for new search
                searchResults.value = {
                    posts: data.posts || [],
                    questions: data.questions || [],
                    users: data.users || [],
                    projects: data.projects || []
                }
            }
            
            totalResults.value = data.meta?.total_results || 0
            totalPages.value = data.meta?.total_pages || 1
            searchTime.value = data.meta?.search_time || 0
            hasMore.value = currentPage.value < totalPages.value

            // Update URL without reload (only for initial search, not load more)
            if (!isLoadMore) {
                const queryParams = {
                    q: searchQuery.value,
                    type: selectedType.value,
                    sort: selectedSort.value
                }
                if (selectedAuthor.value?.username) {
                    queryParams.author = selectedAuthor.value.username
                }
                if (selectedTags.value.length > 0) {
                    queryParams.tags = selectedTags.value.join(',')
                }
                if (dateFrom.value) {
                    queryParams.date_from = dateFrom.value
                }
                if (dateTo.value) {
                    queryParams.date_to = dateTo.value
                }
                router.replace({ query: queryParams })
            }

            // Setup observer after results are loaded
            if (hasMore.value) {
                await nextTick()
                setupIntersectionObserver()
            }
        }
    } catch (error) {
        console.error('Search error:', error)
    } finally {
        if (isLoadMore) {
            isLoadingMore.value = false
        } else {
            isLoading.value = false
        }
    }
}

const handleResultClick = (result) => {
    if (result.url) {
        router.push(result.url)
    } else if (result.type === 'user' && result.username) {
        router.push(`/@${result.username}`)
    }
}

const loadMore = () => {
    if (hasMore.value && !isLoadingMore.value && !isLoading.value) {
        currentPage.value++
        performSearch(true)
    }
}

const handleSearch = () => {
    currentPage.value = 1
    performSearch(false)
}

// Fetch tag suggestions
const fetchTagSuggestions = useDebounceFn(async (query) => {
    if (query.length < 2) {
        tagSuggestions.value = []
        showTagSuggestions.value = false
        return
    }

    try {
        const response = await axios.get(`/api/v1/search/tag-suggestions?q=${encodeURIComponent(query)}`, authStore.config)
        if (response.data.status === 'success') {
            tagSuggestions.value = response.data.data.tags || []
            showTagSuggestions.value = tagSuggestions.value.length > 0
        }
    } catch (error) {
        console.error('Error fetching tag suggestions:', error)
        tagSuggestions.value = []
        showTagSuggestions.value = false
    }
}, 300)

// Fetch user suggestions
const fetchUserSuggestions = useDebounceFn(async (query) => {
    if (query.length < 2) {
        authorSuggestions.value = []
        showAuthorSuggestions.value = false
        return
    }

    try {
        const response = await axios.get(`/api/v1/search/user-suggestions?q=${encodeURIComponent(query)}`, authStore.config)
        if (response.data.status === 'success') {
            authorSuggestions.value = response.data.data.users || []
            showAuthorSuggestions.value = authorSuggestions.value.length > 0
        }
    } catch (error) {
        console.error('Error fetching user suggestions:', error)
        authorSuggestions.value = []
        showAuthorSuggestions.value = false
    }
}, 300)

// Handle tag input
const handleTagInput = (e) => {
    tagFilter.value = e.target.value
    fetchTagSuggestions(tagFilter.value)
}

// Add tag
const addTag = (tagName) => {
    const trimmedTag = tagName.trim()
    if (trimmedTag && !selectedTags.value.includes(trimmedTag)) {
        selectedTags.value.push(trimmedTag)
        tagFilter.value = ''
        tagSuggestions.value = []
        showTagSuggestions.value = false
        handleSearch()
    }
}

// Remove tag
const removeTag = (tagName) => {
    selectedTags.value = selectedTags.value.filter(t => t !== tagName)
    handleSearch()
}

// Handle tag input enter key
const handleTagKeydown = (e) => {
    if (e.key === 'Enter' && tagFilter.value.trim()) {
        e.preventDefault()
        addTag(tagFilter.value)
    } else if (e.key === 'Escape') {
        showTagSuggestions.value = false
    }
}

// Select author
const selectAuthor = (user) => {
    selectedAuthor.value = user
    authorFilter.value = user.username
    authorSuggestions.value = []
    showAuthorSuggestions.value = false
    handleSearch()
}

// Clear author
const clearAuthor = () => {
    selectedAuthor.value = null
    authorFilter.value = ''
    authorSuggestions.value = []
    showAuthorSuggestions.value = false
    handleSearch()
}

// Clear all filters
const clearAllFilters = () => {
    selectedAuthor.value = null
    authorFilter.value = ''
    selectedTags.value = []
    tagFilter.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    handleSearch()
}

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return selectedAuthor.value !== null || 
           selectedTags.value.length > 0 || 
           dateFrom.value || 
           dateTo.value
})

// Setup intersection observer for infinite scroll
const setupIntersectionObserver = () => {
    if (observer.value) {
        observer.value.disconnect()
    }

    const options = {
        root: null,
        rootMargin: '200px',
        threshold: 0.1
    }

    observer.value = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore.value && !isLoadingMore.value && !isLoading.value) {
                loadMore()
            }
        })
    }, options)

    // Observe the load more trigger element
    if (loadMoreTrigger.value) {
        observer.value.observe(loadMoreTrigger.value)
    }
}

// Initialize from route query
onMounted(() => {
    searchQuery.value = route.query.q || ''
    selectedType.value = route.query.type || 'all'
    selectedSort.value = route.query.sort || 'relevance'
    currentPage.value = 1

    // Initialize filters from URL
    if (route.query.author) {
        authorFilter.value = route.query.author
        // Try to fetch user data if needed
        fetchUserSuggestions(route.query.author).then(() => {
            const user = authorSuggestions.value.find(u => u.username === route.query.author)
            if (user) {
                selectedAuthor.value = user
            }
        })
    }
    if (route.query.tags) {
        selectedTags.value = route.query.tags.split(',').filter(t => t.trim())
    }
    if (route.query.date_from) {
        dateFrom.value = route.query.date_from
    }
    if (route.query.date_to) {
        dateTo.value = route.query.date_to
    }

    if (searchQuery.value.length >= 3) {
        performSearch(false).then(() => {
            setupIntersectionObserver()
        })
    }
})

// Watch for route query changes
watch(() => route.query, (newQuery) => {
    if (newQuery.q && newQuery.q !== searchQuery.value) {
        searchQuery.value = newQuery.q
        currentPage.value = 1
        selectedType.value = newQuery.type || 'all'
        selectedSort.value = newQuery.sort || 'relevance'
        
        // Update filters from URL
        if (newQuery.author) {
            authorFilter.value = newQuery.author
        } else {
            selectedAuthor.value = null
            authorFilter.value = ''
        }
        if (newQuery.tags) {
            selectedTags.value = newQuery.tags.split(',').filter(t => t.trim())
        } else {
            selectedTags.value = []
        }
        if (newQuery.date_from) {
            dateFrom.value = newQuery.date_from
        } else {
            dateFrom.value = ''
        }
        if (newQuery.date_to) {
            dateTo.value = newQuery.date_to
        } else {
            dateTo.value = ''
        }
        
        performSearch(false).then(() => {
            setupIntersectionObserver()
        })
    }
}, { immediate: false })

// Watch tag filter for suggestions
watch(tagFilter, (newVal) => {
    if (newVal) {
        fetchTagSuggestions(newVal)
    } else {
        tagSuggestions.value = []
        showTagSuggestions.value = false
    }
})

// Watch author filter for suggestions
watch(authorFilter, (newVal) => {
    if (newVal && !selectedAuthor.value) {
        fetchUserSuggestions(newVal)
    } else if (!newVal) {
        authorSuggestions.value = []
        showAuthorSuggestions.value = false
    }
})

// Cleanup observer on unmount
onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect()
    }
})
</script>

<template>
    <div class="min-h-screen">
        <div class="max-w-6xl mx-auto py-5">
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">
                    Search Results
                </h1>
                <div class="mb-6">
                    <div class="flex gap-3 items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 pointer-events-none z-10 text-gray-500 dark:text-gray-400" />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search posts, questions, projects, users..."
                                class="pl-10 pr-4 h-11"
                                @keyup.enter="handleSearch"
                            />
                        </div>
                        <Button 
                            @click="handleSearch" 
                            :disabled="isLoading || searchQuery.length < 3"
                            class="h-11 px-6"
                        >
                            <Search class="h-4 w-4 mr-2" />
                            Search
                        </Button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <Filter class="h-4 w-4 flex-shrink-0" :class="[
                                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                ]" />
                                <select 
                                    v-model="selectedType"
                                    @change="handleSearch"
                                    class="px-3 py-2 rounded-md border text-sm h-10 min-w-[120px] bg-background"
                                    :class="[
                                        themeStore.isDark 
                                            ? 'border-gray-800 bg-gray-900 text-white' 
                                            : 'border-gray-300 bg-white text-gray-900'
                                    ]"
                                >
                                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-sm whitespace-nowrap" :class="[
                                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                ]">Sort by:</span>
                                <select 
                                    v-model="selectedSort"
                                    @change="handleSearch"
                                    class="px-3 py-2 rounded-md border text-sm h-10 min-w-[130px] bg-background"
                                    :class="[
                                        themeStore.isDark 
                                            ? 'border-gray-800 bg-gray-900 text-white' 
                                            : 'border-gray-300 bg-white text-gray-900'
                                    ]"
                                >
                                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <Button
                                variant="outline"
                                size="sm"
                                @click="showAdvancedFilters = !showAdvancedFilters"
                                class="h-10 gap-2"
                            >
                                <Filter class="h-4 w-4" />
                                Advanced Filters
                                <ChevronDown v-if="!showAdvancedFilters" class="h-4 w-4" />
                                <ChevronUp v-else class="h-4 w-4" />
                                <span v-if="hasActiveFilters" class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-primary text-primary-foreground">
                                    {{ (selectedAuthor ? 1 : 0) + selectedTags.length + (dateFrom ? 1 : 0) + (dateTo ? 1 : 0) }}
                                </span>
                            </Button>
                        </div>

                        <div v-if="totalResults > 0" class="text-sm whitespace-nowrap" :class="[
                            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                        ]">
                            <span class="font-medium">{{ totalResults }}</span> results found
                            <span v-if="searchTime > 0" class="text-xs ml-1">
                                ({{ searchTime }}ms)
                            </span>
                        </div>
                    </div>

                    <!-- Advanced Filters Panel -->
                    <Transition name="slide-down">
                        <Card v-if="showAdvancedFilters" :class="[
                            'border-0 shadow-lg',
                            themeStore.isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'
                        ]">
                            <CardContent class="p-6">
                                <div class="space-y-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold" :class="[
                                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                                        ]">
                                            Advanced Filters
                                        </h3>
                                        <Button
                                            v-if="hasActiveFilters"
                                            variant="ghost"
                                            size="sm"
                                            @click="clearAllFilters"
                                            class="text-xs"
                                        >
                                            Clear All
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Author Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium flex items-center gap-2" :class="[
                                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                            ]">
                                                <User class="h-4 w-4" />
                                                Author
                                            </label>
                                            <div class="relative">
                                                <Input
                                                    ref="authorInputRef"
                                                    v-model="authorFilter"
                                                    type="text"
                                                    placeholder="Search by username..."
                                                    class="pr-8"
                                                    @focus="fetchUserSuggestions(authorFilter)"
                                                    @blur="setTimeout(() => showAuthorSuggestions = false, 200)"
                                                />
                                                <X 
                                                    v-if="selectedAuthor"
                                                    @click="clearAuthor"
                                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 h-4 w-4 cursor-pointer" 
                                                    :class="[
                                                        themeStore.isDark ? 'text-gray-400 hover:text-gray-300' : 'text-gray-500 hover:text-gray-700'
                                                    ]"
                                                />
                                                
                                                <!-- Author Suggestions Dropdown -->
                                                <div 
                                                    v-if="showAuthorSuggestions && authorSuggestions.length > 0"
                                                    class="absolute z-50 w-full mt-1 max-h-60 overflow-y-auto rounded-lg border shadow-lg"
                                                    :class="[
                                                        themeStore.isDark 
                                                            ? 'bg-gray-900 border-gray-800' 
                                                            : 'bg-white border-gray-200'
                                                    ]"
                                                >
                                                    <div
                                                        v-for="user in authorSuggestions"
                                                        :key="user.id"
                                                        @click="selectAuthor(user)"
                                                        class="flex items-center gap-3 px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                                    >
                                                        <img 
                                                            v-if="user.avatar" 
                                                            :src="user.avatar" 
                                                            :alt="user.name"
                                                            class="w-8 h-8 rounded-full"
                                                        />
                                                        <div v-else class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold">
                                                            {{ user.name?.charAt(0) || user.username?.charAt(0) }}
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-sm font-medium truncate" :class="[
                                                                themeStore.isDark ? 'text-white' : 'text-gray-900'
                                                            ]">
                                                                {{ user.name }}
                                                            </div>
                                                            <div class="text-xs truncate" :class="[
                                                                themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                                            ]">
                                                                @{{ user.username }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tags Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium flex items-center gap-2" :class="[
                                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                            ]">
                                                <Tag class="h-4 w-4" />
                                                Tags
                                            </label>
                                            <div class="relative">
                                                <Input
                                                    ref="tagInputRef"
                                                    v-model="tagFilter"
                                                    type="text"
                                                    placeholder="Type and press Enter..."
                                                    class="pr-8"
                                                    @input="handleTagInput"
                                                    @keydown="handleTagKeydown"
                                                    @focus="fetchTagSuggestions(tagFilter)"
                                                    @blur="setTimeout(() => showTagSuggestions = false, 200)"
                                                />
                                                
                                                <!-- Tag Suggestions Dropdown -->
                                                <div 
                                                    v-if="showTagSuggestions && tagSuggestions.length > 0"
                                                    class="absolute z-50 w-full mt-1 max-h-60 overflow-y-auto rounded-lg border shadow-lg"
                                                    :class="[
                                                        themeStore.isDark 
                                                            ? 'bg-gray-900 border-gray-800' 
                                                            : 'bg-white border-gray-200'
                                                    ]"
                                                >
                                                    <div
                                                        v-for="tag in tagSuggestions"
                                                        :key="tag"
                                                        @click="addTag(tag)"
                                                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm"
                                                        :class="[
                                                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                                                        ]"
                                                    >
                                                        {{ tag }}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Selected Tags -->
                                            <div v-if="selectedTags.length > 0" class="flex flex-wrap gap-2 mt-2">
                                                <div
                                                    v-for="tag in selectedTags"
                                                    :key="tag"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium"
                                                    :class="[
                                                        themeStore.isDark 
                                                            ? 'bg-blue-900/30 text-blue-400 border border-blue-800' 
                                                            : 'bg-blue-100 text-blue-700 border border-blue-200'
                                                    ]"
                                                >
                                                    {{ tag }}
                                                    <button
                                                        @click="removeTag(tag)"
                                                        class="hover:opacity-70 transition-opacity"
                                                    >
                                                        <X class="h-3 w-3" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Date From Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium flex items-center gap-2" :class="[
                                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                            ]">
                                                <Calendar class="h-4 w-4" />
                                                Date From
                                            </label>
                                            <Input
                                                v-model="dateFrom"
                                                type="date"
                                                @change="handleSearch"
                                                class="w-full"
                                            />
                                        </div>

                                        <!-- Date To Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium flex items-center gap-2" :class="[
                                                themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                            ]">
                                                <Calendar class="h-4 w-4" />
                                                Date To
                                            </label>
                                            <Input
                                                v-model="dateTo"
                                                type="date"
                                                :min="dateFrom"
                                                @change="handleSearch"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </Transition>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex items-center justify-center py-20">
                <Loader2 class="h-8 w-8 animate-spin" :class="[
                    themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                ]" />
            </div>

            <!-- Results -->
            <div v-else-if="allResults.length > 0" class="space-y-4">
                <Card 
                    v-for="(result, index) in allResults" 
                    :key="`${result.type}-${result.id || result.username || index}`"
                    @click="handleResultClick(result)"
                    class="cursor-pointer transition-all duration-200 hover:shadow-lg group"
                    :class="[
                        themeStore.isDark 
                            ? 'bg-gray-900 border-gray-800 hover:border-gray-700' 
                            : 'bg-white border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <CardContent class="p-6">
                        <div class="flex items-start gap-4 w-full">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :class="[
                                result.type === 'post' 
                                    ? (themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600') :
                                result.type === 'question' 
                                    ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600') :
                                result.type === 'project' 
                                    ? (themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600') :
                                    (themeStore.isDark ? 'bg-orange-900/30 text-orange-400' : 'bg-orange-100 text-orange-600')
                            ]">
                                <component :is="result.icon || FileText" class="h-6 w-6" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-medium px-2 py-1 rounded capitalize" :class="[
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
                                    <span v-if="result.author" class="text-xs" :class="[
                                        themeStore.isDark ? 'text-gray-400' : 'text-gray-500'
                                    ]">
                                        by @{{ typeof result.author === 'object' ? result.author.username : result.author }}
                                    </span>
                                    <span v-if="result.posted_at" class="text-xs" :class="[
                                        themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                    ]">
                                        • {{ result.posted_at }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold mb-2" :class="[
                                    themeStore.isDark ? 'text-white' : 'text-gray-900'
                                ]">
                                    {{ result.title || result.name || result.username }}
                                </h3>

                                <p v-if="result.content || result.description" class="text-sm mb-3 line-clamp-2" :class="[
                                    themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                                ]">
                                    {{ result.content || result.description }}
                                </p>

                                <!-- Stats -->
                                <div v-if="result.stats" class="flex items-center gap-4 text-sm" :class="[
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

                                <!-- Tags -->
                                <div v-if="result.tags && result.tags.length > 0" class="flex flex-wrap gap-2 mt-3">
                                    <span 
                                        v-for="tag in result.tags" 
                                        :key="tag"
                                        class="text-xs px-2 py-1 rounded" 
                                        :class="[
                                            themeStore.isDark 
                                                ? 'bg-gray-800 text-gray-300' 
                                                : 'bg-gray-100 text-gray-700'
                                        ]"
                                    >
                                        {{ tag }}
                                    </span>
                                </div>
                            </div>

                            <ChevronRight class="h-5 w-5 flex-shrink-0 mt-1 transition-transform group-hover:translate-x-1" :class="[
                                themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
                            ]" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Load More Trigger (for infinite scroll) -->
                <div ref="loadMoreTrigger" class="h-20 flex items-center justify-center">
                    <div v-if="isLoadingMore" class="flex items-center gap-2 text-sm" :class="[
                        themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                    ]">
                        <Loader2 class="h-5 w-5 animate-spin" />
                        <span>Loading more results...</span>
                    </div>
                    <div v-else-if="!hasMore && allResults.length > 0" class="text-sm py-8" :class="[
                        themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
                    ]">
                        No more results to load
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <EmptyState
                v-else-if="searchQuery.length >= 3"
                icon="Search"
                title="No results found"
                subtitle="Try different keywords or filters"
                size="default"
            />

            <!-- Empty State -->
            <EmptyState
                v-else
                icon="Search"
                title="Start searching"
                subtitle="Enter at least 3 characters to search"
                size="default"
            />
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Slide down transition for advanced filters */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease-out;
}

.slide-down-enter-from {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
}

.slide-down-enter-to {
    opacity: 1;
    transform: translateY(0);
    max-height: 1000px;
}

.slide-down-leave-from {
    opacity: 1;
    transform: translateY(0);
    max-height: 1000px;
}

.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
}
</style>

