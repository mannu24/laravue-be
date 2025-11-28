<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { LucideChevronLeft, LucideChevronRight, LucideEllipsisVertical } from 'lucide-vue-next';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import PostForm from './PostForm.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useBookmark } from '../../composables/useBookmark';
import { Bookmark } from 'lucide-vue-next';
import axios from 'axios';

const { post } = defineProps(['post'])
const element = ref(null)
const showDropdown = ref(false)
const isModalVisible = ref(false)
const emit = defineEmits(['load_more', 'fetch', 'delete_post', 'liked_action', 'share_url', 'bookmarked_action'])
const $router = useRouter();
const authStore = useAuthStore()
const post_url = computed(() => '/feed/' + post.post_code)

// Bookmark functionality
const { isBookmarked, bookmarkCount, isLoading: isBookmarkLoading, toggleBookmark, initialize } = useBookmark(post, 'post')

// Initialize bookmark state from post data
watch(() => post, (newPost) => {
    if (newPost) {
        initialize(newPost)
    }
}, { immediate: true })

// Gallery popup state:
const isGalleryVisible = ref(false)
const selectedImageIndex = ref(0)

const handleOpenGallery = (index) => {
    selectedImageIndex.value = index;
    isGalleryVisible.value = true;
};

const closeGallery = () => {
    isGalleryVisible.value = false;
};

const nextImage = () => {
    if (selectedImageIndex.value < post.media_urls.length - 1) {
        selectedImageIndex.value++;
    }
};

const prevImage = () => {
    if (selectedImageIndex.value > 0) {
        selectedImageIndex.value--;
    }
};

const errorFallbackImage = (event) => {
    event.target.src = '/placeholder.svg'
}

const errorFallbackUserImage = (event) => {
    event.target.src = '/assets/front/images/user.png'
}

const edit_post = () => {
    isModalVisible.value = true
    showDropdown.value = false
}

const action = async (type) => {
    if (type === 'delete') {
        showDropdown.value = false
        await axios.delete(`/api/v1/posts/${post.post_code}`, authStore.config).then(() => {
            emit('delete_post', post.post_code)
        })
    } else {
        showDropdown.value = false
        await axios.get(`/api/v1/posts/duplicate/${post.post_code}`, authStore.config).then(() => {
            emit('fetch')
        })
    }
}

const renderContent = (content) => {
    return content.replace(/@(\w+)/g, '<a href="/@$1">@$1</a>');
};

const checkItem = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                emit('load_more')
            }
        });
    });
    observer.observe(element.value);
}

const handleLike = async () => {
    if (!authStore.isAuthenticated) {
        $router.push('/login')
    }
    else {
        const response = await axios.get(`/api/v1/posts/like-unlike/${post.post_code}`, authStore.config)
        if (response.data.status == 'success') {
            emit('liked_action', [post.post_code, response.data.liked])
        }
        else {
            alert('Something went wrong')
        }
    }
};

const handleComment = () => {
    if (!authStore.isAuthenticated) {
        $router.push('/login')
    }
    else {
        $router.push(post_url.value)
    }
};

const handleShare = () => {
    emit('share_url', post_url.value)
};

const handleBookmark = async () => {
    if (!authStore.isAuthenticated) {
        $router.push('/login')
        return
    }
    
    // Posts use 'post_code' as the unique identifier for bookmarks
    const recordId = post.post_code
    if (!recordId) {
        console.error('Post code not found')
        return
    }
    
    const result = await toggleBookmark('post', recordId)
    if (result !== undefined) {
        emit('bookmarked_action', [recordId, result, bookmarkCount.value])
    }
};

const fetch = () => {
    emit('fetch')
}

const closeModal = () => {
    isModalVisible.value = false
};

onMounted(() => {
    if (element.value.classList.contains('last_item')) {
        checkItem();
    }
});

</script>
<template>
    <div ref="element">
        <article
            @click="$router.push(post_url)"
            class="group bg-white/95 dark:bg-card border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-[0_20px_70px_rgba(15,23,42,0.08)] dark:shadow-none hover:shadow-[0_25px_80px_rgba(15,23,42,0.12)] dark:hover:shadow-xl transition-all duration-300 hover:cursor-pointer hover:border-gray-300 dark:hover:border-gray-700"
        >
            <div class="flex flex-col gap-4">
                <!-- Title and Content Section -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 v-if="post.title" 
                            class="text-lg cursor-pointer group-hover:text-green-600 dark:group-hover:text-green-400 lg:text-xl font-semibold text-gray-900 dark:text-white leading-tight mb-2 line-clamp-2">
                            {{ post.title }}
                        </h3>
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 leading-relaxed whitespace-pre-wrap break-words" 
                                v-html="renderContent(post.content)" 
                                @click.stop></p>
                        </div>
                    </div>

                    <div
                        v-if="authStore.isAuthenticated && post.owner"
                        class="relative flex-shrink-0"
                        @click.stop
                    >
                        <button
                            @click="showDropdown = !showDropdown"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-500 dark:text-gray-400"
                        >
                            <LucideEllipsisVertical class="w-5 h-5" />
                        </button>
                        <transition name="fade">
                            <div
                                v-if="showDropdown"
                                @click.stop
                        class="absolute right-2 top-full mt-2 z-20 w-40 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden"
                                role="menu"
                                aria-orientation="vertical"
                            >
                                <button
                                    @click.stop="edit_post()"
                                    class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300"
                                >
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                    Edit Post
                                </button>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <button
                                            @click.stop
                                            class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400"
                                        >
                                            <i class="fas fa-trash text-xs"></i>
                                            Delete
                                        </button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently delete your post.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click.stop="action('delete')">Continue</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Media Gallery -->
                <div v-if="post.media_urls && post.media_urls.length" 
                    class="rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div v-if="post.media_urls.length === 1" 
                            class="relative group/media">
                            <img :src="post.media_urls[0]" alt="Post Media"
                                class="w-full h-auto max-h-[500px] object-cover cursor-pointer transition-transform duration-300 group-hover:scale-[1.02]" 
                                @click.stop="handleOpenGallery(0)"
                                @error="errorFallbackImage" />
                        </div>
                        <div v-else-if="post.media_urls.length === 2" 
                            class="grid grid-cols-2 gap-1">
                            <template v-for="(media, index) in post.media_urls" :key="media">
                                <img v-if="index < 2" :src="media" alt="Post Media" 
                                    class="w-full h-64 object-cover cursor-pointer transition-transform duration-300 hover:scale-105" 
                                    @click.stop="handleOpenGallery(index)"
                                    @error="errorFallbackImage" />
                            </template>
                        </div>
                        <div v-else-if="post.media_urls.length === 3" 
                            class="grid grid-cols-2 gap-1">
                            <template v-for="(media, index) in post.media_urls.slice(0, 2)" :key="index">
                                <img :src="media" alt="Post Media" 
                                    class="w-full h-48 object-cover cursor-pointer transition-transform duration-300 hover:scale-105" 
                                    @click.stop="handleOpenGallery(index)"
                                    @error="errorFallbackImage" />
                            </template>
                            <img :src="post.media_urls[2]" alt="Post Media" 
                                class="col-span-2 w-full h-64 object-cover cursor-pointer transition-transform duration-300 hover:scale-105" 
                                @click.stop="handleOpenGallery(2)"
                                @error="errorFallbackImage" />
                        </div>
                        <div v-else 
                            class="grid grid-cols-2 gap-1 relative">
                            <template v-for="(media, index) in post.media_urls.slice(0, 4)" :key="index">
                                <div class="relative overflow-hidden">
                                    <img :src="media" alt="Post Media" 
                                        class="w-full h-48 object-cover cursor-pointer transition-transform duration-300 hover:scale-110" 
                                        @click.stop="handleOpenGallery(index)"
                                        @error="errorFallbackImage" />
                                    <div v-if="index === 3 && post.media_urls.length > 4"
                                        class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-lg font-bold cursor-pointer backdrop-blur-sm"
                                        @click.stop="handleOpenGallery(3)">
                                        +{{ post.media_urls.length - 4 }} more
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                <!-- User Info Section -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <router-link :to="post.user.username != authStore.user?.username ? '/@' + post.user.username : '/dashboard'" @click.stop class="flex-shrink-0">
                                <img v-if="post.user?.profile_photo" :src="post.user?.profile_photo" alt="User Avatar"
                                    class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                    @error="errorFallbackUserImage" />
                                <img v-else src="/assets/front/images/user.png" alt="User Avatar"
                                    class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                    @error="errorFallbackUserImage" />
                            </router-link>
                            <router-link @click.stop :to="post.user.username != authStore.user?.username ? '/@' + post.user.username : '/dashboard'">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ post.user.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ post.posted_at }}</p>
                                </div>
                            </router-link>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ post.views_count || 0 }} views &bull; {{ post.likes_count || 0 }} likes</span>
                    </div>
                </div>

                <!-- Actions Footer -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center">
                        <button
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-colors"
                            :class="post.liked ? 'bg-red-600/10 text-red-600 dark:bg-red-600/20' : 'bg-gray-50 text-gray-600 dark:bg-card dark:text-gray-300'"
                            @click.stop="handleLike"
                        >
                            <i :class="post.liked ? 'fas fa-heart' : 'far fa-heart'" class="text-base"></i>
                            <span>{{ post.likes_count || 0 }}</span>
                        </button>
                        <button
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-gray-50 text-gray-600 dark:bg-card dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300"
                            @click.stop="handleComment"
                        >
                            <i class="far fa-comment text-base"></i>
                            <span>{{ post.comments_count || 0 }}</span>
                        </button>
                        <button
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-gray-50 dark:bg-card hover:text-vue transition-all duration-300 text-gray-600 dark:text-gray-300"
                            @click.stop="handleShare"
                        >
                            <i class="far fa-paper-plane text-base"></i>
                            <span>Share</span>
                        </button>
                    </div>
                    <button
                        :disabled="isBookmarkLoading || !authStore.isAuthenticated"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-colors"
                        :class="isBookmarked ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-gray-50 text-gray-600 dark:bg-card dark:text-gray-300'"
                        @click.stop="handleBookmark"
                    >
                        <Bookmark :class="['w-4 h-4', isBookmarked ? 'fill-current' : '']" />
                        <span>{{ bookmarkCount || post.bookmark_count || 0 }}</span>
                    </button>
                </div>
            </div>
        </article>

        <PostForm v-if="isModalVisible" @close="closeModal" @fetch="fetch" :post="post" />
        
        <Dialog v-model:open="isGalleryVisible">
            <DialogContent class="max-w-5xl p-0 border-0 bg-transparent shadow-2xl">
                <div class="relative bg-black/90 rounded-xl overflow-hidden">
                    <img :src="post.media_urls[selectedImageIndex]" alt="Gallery image" 
                        class="w-full h-auto max-h-[80vh] object-contain" />
                    <button v-if="selectedImageIndex > 0" 
                        @click="prevImage" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-3 rounded-full transition-all duration-200 hover:scale-110">
                        <LucideChevronLeft class="w-6 h-6" />
                    </button>
                    <button v-if="selectedImageIndex < post.media_urls.length - 1" 
                        @click="nextImage" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white p-3 rounded-full transition-all duration-200 hover:scale-110">
                        <LucideChevronRight class="w-6 h-6" />
                    </button>
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm">
                        {{ selectedImageIndex + 1 }} / {{ post.media_urls.length }}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
<style scoped>
.mention-link {
    --tw-text-opacity: 1;
    color: rgb(42 97 70 / var(--tw-text-opacity, 1));
    padding-left: 0.25rem;
    padding-right: 0.25rem;
    --tw-bg-opacity: 1;
    background-color: rgb(209 235 223 / var(--tw-bg-opacity, 1));
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.mention-link:hover {
    text-decoration: underline;
    background-color: rgb(187 227 210 / var(--tw-bg-opacity, 1));
}

.dark .mention-link {
    color: rgb(134 239 172 / var(--tw-text-opacity, 1));
    background-color: rgb(20 83 45 / var(--tw-bg-opacity, 1));
}

.dark .mention-link:hover {
    background-color: rgb(22 101 52 / var(--tw-bg-opacity, 1));
}

.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
