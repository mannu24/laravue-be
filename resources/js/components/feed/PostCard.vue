<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { LucideChevronLeft, LucideChevronRight, LucideEllipsisVertical } from 'lucide-vue-next';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { CardFooter } from '@/components/ui/card';
import PostForm from './PostForm.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useBookmark } from '../../composables/useBookmark';
import { Bookmark } from 'lucide-vue-next';

const { post } = defineProps(['post'])
const element = ref(null)
const showDropdown = ref(false)
const isModalVisible = ref(false)
const emit = defineEmits(['load_more', 'fetch', 'delete_post', 'liked_action', 'share_url', 'bookmarked_action'])
const $router = useRouter();
const authStore = useAuthStore()
const post_url = computed(() => '/@' + post.user.username + '/' + post.post_code)

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
        <article @click="$router.push(post_url)" 
            class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm dark:shadow-gray-900/50 hover:shadow-xl dark:hover:shadow-gray-900 rounded-2xl overflow-hidden hover:cursor-pointer transition-all duration-300 ease-out hover:border-gray-300 dark:hover:border-gray-600">
            <!-- Header Section -->
            <div class="flex items-start justify-between p-5 pb-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <router-link :to="post.user.username != authStore.user?.username ? '/@' + post.user.username : '/dashboard'" @click.stop class="flex-shrink-0">
                        <div class="relative">
                            <img v-if="post.user?.profile_photo" :src="post.user?.profile_photo" alt="User Avatar"
                                class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 transition-all duration-300 group-hover:ring-green-500/50" 
                                @error="errorFallbackUserImage" />
                            <img v-else src="/assets/front/images/user.png" alt="User Avatar" 
                                class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 transition-all duration-300 group-hover:ring-green-500/50" 
                                @error="errorFallbackUserImage" />
                        </div>
                    </router-link>
                    <router-link @click.stop :to="post.user.username != authStore.user?.username ? '/@' + post.user.username : '/dashboard'" class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-base truncate group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                {{ post.user.name }}
                            </h4>
                            <span class="text-sm text-gray-500 dark:text-gray-400 truncate">@{{ post.user.username }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ post.posted_at }}</p>
                    </router-link>
                </div>
                <div class="relative inline-block ml-2 self-start" v-if="authStore.isAuthenticated && post.owner">
                    <button @click.stop="showDropdown = !showDropdown" 
                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <LucideEllipsisVertical class="w-5 h-5" />
                    </button>
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="showDropdown" @click.stop
                            class="absolute right-0 top-full mt-2 z-20 w-40 origin-top-right bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
                            role="menu" aria-orientation="vertical">
                            <div role="none">
                                <button @click.stop="edit_post()"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-2">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                    Edit Post
                                </button>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <button @click.stop
                                            class="w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center gap-2">
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
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Content Section -->
            <div class="px-5 pb-4">
                <h3 v-if="post.title" 
                    class="font-bold text-xl text-gray-900 dark:text-white mb-3 leading-tight line-clamp-2">
                    {{ post.title }}
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none mb-4">
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap break-words" 
                       v-html="renderContent(post.content)" 
                       @click.stop></p>
                </div>

                <!-- Media Gallery -->
                <div v-if="post.media_urls && post.media_urls.length" 
                     class="rounded-xl overflow-hidden mb-4 border border-gray-200 dark:border-gray-700">
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
            </div>

            <!-- Actions Footer -->
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <button @click.stop="handleLike" 
                            :class="[
                                'flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200',
                                post.liked 
                                    ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20' 
                                    : 'text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                            ]">
                            <i :class="post.liked ? 'fas fa-heart' : 'far fa-heart'" class="text-base"></i>
                            <span class="text-sm font-medium">{{ post.likes_count || 0 }}</span>
                        </button>
                        <button @click.stop="handleComment" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <i class="far fa-comment text-base"></i>
                            <span class="text-sm font-medium">{{ post.comments_count || 0 }}</span>
                        </button>
                        <button @click.stop="handleShare" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <i class="far fa-paper-plane text-base"></i>
                            <span class="text-sm font-medium">{{ post.views_count || 0 }}</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="far fa-eye"></i>
                            <span>{{ post.views_count || 0 }} views</span>
                        </div>
                        <!-- Bookmark/Save Button (Instagram style) -->
                        <button 
                            @click.stop="handleBookmark"
                            :disabled="isBookmarkLoading || !authStore.isAuthenticated"
                            :class="[
                                'p-2 rounded-lg transition-all duration-200',
                                isBookmarked
                                    ? 'text-yellow-600 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20'
                                    : 'text-gray-600 dark:text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700/50',
                                isBookmarkLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                            ]"
                            :title="isBookmarked ? 'Remove from bookmarks' : 'Save to bookmarks'"
                        >
                            <Bookmark 
                                :class="[
                                    'w-5 h-5 transition-transform duration-200',
                                    isBookmarked ? 'fill-current' : '',
                                    isBookmarkLoading ? 'animate-pulse' : ''
                                ]"
                            />
                        </button>
                    </div>
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
