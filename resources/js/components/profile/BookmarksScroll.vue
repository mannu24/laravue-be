<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../../stores/theme'
import { useAuthStore } from '../../stores/auth'
import { Card, CardContent } from '../../components/ui/card'
import { Skeleton } from '../../components/ui/skeleton'
import PostCard from '../feed/PostCard.vue'
import QuestionCard from '../qna/QuestionCard.vue'
import { Bookmark, FileText, MessageSquare, FolderOpen, Loader2 } from 'lucide-vue-next'
import axios from 'axios'

const themeStore = useThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const bookmarks = ref([])
const loading = ref(false)
const hasMore = ref(true)
const pageNo = ref(1)
const observer = ref(null)
const loadMoreTrigger = ref(null)
const hasLoaded = ref(false)

const fetchBookmarks = async (isLoadMore = false) => {
    if (loading.value) return
    
    if (isLoadMore) {
        if (!hasMore.value) return
        pageNo.value++
    } else {
        pageNo.value = 1
        bookmarks.value = []
        hasMore.value = true
        hasLoaded.value = false
    }

    loading.value = true
    
    try {
        const response = await axios.get(
            `/api/v1/bookmarks?page=${pageNo.value}&per_page=20`,
            authStore.config
        )

        if (response.data.status === 'success') {
            const data = response.data.data
            const newBookmarks = data.bookmarks || []
            
            if (isLoadMore) {
                bookmarks.value.push(...newBookmarks)
            } else {
                bookmarks.value = newBookmarks
            }

            const pagination = data.pagination
            hasMore.value = pagination.current_page < pagination.last_page
            hasLoaded.value = true

            // Setup observer after results are loaded
            if (hasMore.value) {
                await nextTick()
                setupIntersectionObserver()
            }
        }
    } catch (error) {
        console.error('Error fetching bookmarks:', error)
        hasLoaded.value = true
    } finally {
        loading.value = false
    }
}

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
            if (entry.isIntersecting && hasMore.value && !loading.value) {
                fetchBookmarks(true)
            }
        })
    }, options)

    if (loadMoreTrigger.value) {
        observer.value.observe(loadMoreTrigger.value)
    }
}

const handleBookmarkRemoved = async (bookmarkId) => {
    try {
        await axios.delete(`/api/v1/bookmarks/${bookmarkId}`, authStore.config)
        bookmarks.value = bookmarks.value.filter(b => b.id !== bookmarkId)
    } catch (error) {
        console.error('Error removing bookmark:', error)
    }
}

const getBookmarkUrl = (bookmark) => {
    const record = bookmark.record
    if (!record) return '#'
    
    if (bookmark.type === 'post') {
        return `/@${record.user?.username || 'unknown'}/${record.post_code || record.id}`
    } else if (bookmark.type === 'question') {
        return `/qna/${record.slug || record.id}`
    } else if (bookmark.type === 'project') {
        return `/projects/${record.id}`
    }
    return '#'
}

const getBookmarkIcon = (type) => {
    switch (type) {
        case 'post':
            return FileText
        case 'question':
            return MessageSquare
        case 'project':
            return FolderOpen
        default:
            return Bookmark
    }
}

onMounted(() => {
    fetchBookmarks(false).then(() => {
        setupIntersectionObserver()
    })
})

onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect()
    }
})
</script>

<template>
    <div class="bookmarks-scroll-container max-w-2xl mx-auto flex flex-col gap-4 sm:px-6 pb-5">
        <TransitionGroup name="fade" appear>
            <Card
                v-for="(bookmark, index) in bookmarks"
                :key="bookmark.id"
                :class="[
                    'transition-all duration-200 hover:shadow-lg',
                    index === bookmarks.length - 1 ? 'last-bookmark-item' : '',
                    themeStore.isDark 
                        ? 'bg-gray-900 border-gray-800 hover:border-gray-700' 
                        : 'bg-white border-gray-200 hover:border-gray-300'
                ]"
            >
                <CardContent class="p-6">
                    <!-- Bookmark Header -->
                    <div class="flex items-center justify-between mb-4 pb-4 border-b" :class="[
                        themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
                    ]">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="[
                                bookmark.type === 'post' 
                                    ? (themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600') :
                                bookmark.type === 'question' 
                                    ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600') :
                                    (themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600')
                            ]">
                                <component :is="getBookmarkIcon(bookmark.type)" class="h-4 w-4" />
                            </div>
                            <div>
                                <div class="text-sm font-medium capitalize" :class="[
                                    themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
                                ]">
                                    {{ bookmark.type }}
                                </div>
                                <div class="text-xs" :class="[
                                    themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                                ]">
                                    Saved {{ new Date(bookmark.bookmarked_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                        <button
                            @click="handleBookmarkRemoved(bookmark.id)"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            :class="[
                                themeStore.isDark ? 'text-gray-400 hover:text-gray-300' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            <Bookmark class="h-4 w-4 fill-current" />
                        </button>
                    </div>

                    <!-- Bookmark Content -->
                    <div v-if="bookmark.type === 'post' && bookmark.record" @click="router.push(getBookmarkUrl(bookmark))" class="cursor-pointer">
                        <PostCard :post="{
                            ...bookmark.record,
                            user: bookmark.record.user,
                            post_code: bookmark.record.post_code || bookmark.record.id
                        }" />
                    </div>
                    <div v-else-if="bookmark.type === 'question' && bookmark.record" @click="router.push(getBookmarkUrl(bookmark))" class="cursor-pointer">
                        <QuestionCard :question="{
                            ...bookmark.record,
                            user: bookmark.record.user,
                            slug: bookmark.record.slug || bookmark.record.id
                        }" />
                    </div>
                    <div v-else-if="bookmark.type === 'project' && bookmark.record" 
                         @click="router.push(getBookmarkUrl(bookmark))"
                         class="cursor-pointer p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <h3 class="text-lg font-semibold mb-2" :class="[
                            themeStore.isDark ? 'text-white' : 'text-gray-900'
                        ]">
                            {{ bookmark.record.title }}
                        </h3>
                        <p v-if="bookmark.record.description" class="text-sm line-clamp-3" :class="[
                            themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
                        ]">
                            {{ bookmark.record.description }}
                        </p>
                        <div v-if="bookmark.record.user" class="flex items-center gap-2 mt-3 text-sm" :class="[
                            themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
                        ]">
                            <img 
                                v-if="bookmark.record.user.profile_photo" 
                                :src="bookmark.record.user.profile_photo" 
                                :alt="bookmark.record.user.name"
                                class="w-5 h-5 rounded-full"
                            />
                            <span>by @{{ bookmark.record.user.username }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </TransitionGroup>

        <!-- Load More Trigger -->
        <div ref="loadMoreTrigger" class="h-20 flex items-center justify-center">
            <div v-if="loading" class="flex items-center gap-2 text-sm" :class="[
                themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
            ]">
                <Loader2 class="h-5 w-5 animate-spin" />
                <span>Loading more bookmarks...</span>
            </div>
            <div v-else-if="!hasMore && bookmarks.length > 0" class="text-sm py-8" :class="[
                themeStore.isDark ? 'text-gray-500' : 'text-gray-500'
            ]">
                No more bookmarks to load
            </div>
        </div>

        <!-- Loading State -->
        <Transition name="fade">
            <div v-if="loading && bookmarks.length === 0" class="gap-4 grid w-full">
                <Skeleton v-for="i in 3" :key="i" class="w-full rounded-xl h-48" />
            </div>
        </Transition>

        <!-- Empty State -->
        <div v-if="!loading && hasLoaded && bookmarks.length === 0" class="text-center py-12">
            <Bookmark class="h-16 w-16 mx-auto mb-4 opacity-50" :class="[
                themeStore.isDark ? 'text-gray-600' : 'text-gray-400'
            ]" />
            <p class="text-lg font-medium mb-2" :class="[
                themeStore.isDark ? 'text-white' : 'text-gray-900'
            ]">
                No saved items yet
            </p>
            <p class="text-sm" :class="[
                themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
            ]">
                Start bookmarking posts, questions, and projects to see them here
            </p>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

