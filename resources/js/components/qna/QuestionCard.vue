<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import axios from 'axios';
import { computed, defineEmits, defineProps, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useBookmark } from '../../composables/useBookmark';
import { useToast } from '../ui/toast';
import { LucideEllipsisVertical, Bookmark, MessageSquare } from 'lucide-vue-next';

const { toast } = useToast()
const element = ref(null)
const showDropdown = ref(false)
const { question } = defineProps(['question'])
const emit = defineEmits(['load_more', 'fetch', 'delete_question', 'liked_action', 'share_url', 'bookmarked_action'])
const router = useRouter();
const authStore = useAuthStore()
const question_url = computed(() => '/qna/' + question.slug)

// Bookmark functionality
const { isBookmarked, bookmarkCount, isLoading: isBookmarkLoading, toggleBookmark, initialize } = useBookmark(question, 'question')

// Initialize bookmark state from question data
watch(() => question, (newQuestion) => {
    if (newQuestion) {
        initialize(newQuestion)
    }
}, { immediate: true })

const goToQuestion = (id) => {
    router.push({ name: 'question', params: { id } })
}

onMounted(() => {
    if (element?.value?.classList.contains('last_item')) {
        checkItem();
    }
});

const delete_record = async () => {
    showDropdown.value = false
    await axios.delete(`/api/v1/questions/${question.slug}`, authStore.config).then(() => {
        emit('delete_question', question.slug)
    })
}

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
        router.push('/login')
    }
    else {
        try {
            const response = await axios.get(`/api/v1/questions/like-unlike/${question.slug}`, authStore.config)
            if (response.data.status == 'success') {
                // Update local state immediately - ensure reactivity
                if (question.liked !== undefined) {
                    question.liked = response.data.liked
                }
                if (response.data.likes_count !== undefined) {
                    question.likes_count = response.data.likes_count
                }
                emit('liked_action', [question.slug, response.data.liked, response.data.likes_count])
            }
            else {
                toast({
                    title: "Error",
                    description: "Something went wrong",
                    variant: "destructive",
                })
            }
        } catch (error) {
            toast({
                title: "Error",
                description: error.response?.data?.message || "Something went wrong",
                variant: "destructive",
            })
        }
    }
};

const handleComment = () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
    }
    else {
        router.push(question_url.value)
    }
};

const handleShare = () => {
    emit('share_url', question_url.value)
};

const handleBookmark = async () => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
        return
    }
    
    // Questions use 'slug' as the unique identifier for bookmarks
    const recordId = question.slug
    if (!recordId) {
        console.error('Question slug not found')
        return
    }
    
    const result = await toggleBookmark('question', recordId)
    if (result !== undefined) {
        emit('bookmarked_action', [recordId, result, bookmarkCount.value])
    }
};

const errorFallbackUserImage = (event) => {
    event.target.src = '/assets/front/images/user.png'
}

</script>
<template>
    <div ref="element">
        <article @click="router.push(question_url)" 
            class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm dark:shadow-gray-900/50 hover:shadow-xl dark:hover:shadow-gray-900 rounded-2xl overflow-hidden hover:cursor-pointer transition-all duration-300 ease-out hover:border-gray-300 dark:hover:border-gray-600 mb-4">
            <!-- Header Section -->
            <div class="flex items-start justify-between p-5 pb-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <router-link :to="question.user?.username ? (question.user.username != authStore.user?.username ? '/@' + question.user.username : '/dashboard') : '#'" @click.stop class="flex-shrink-0">
                        <div class="relative">
                            <img v-if="question.user?.profile_photo" :src="question.user?.profile_photo" alt="User Avatar"
                                class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 transition-all duration-300 group-hover:ring-laravel/50" 
                                @error="errorFallbackUserImage" />
                            <img v-else src="/assets/front/images/user.png" alt="User Avatar" 
                                class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 transition-all duration-300 group-hover:ring-laravel/50" 
                                @error="errorFallbackUserImage" />
                        </div>
                    </router-link>
                    <router-link @click.stop :to="question.user?.username ? (question.user.username != authStore.user?.username ? '/@' + question.user.username : '/dashboard') : '#'" class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-base truncate group-hover:text-laravel dark:group-hover:text-laravel transition-colors">
                                {{ question.user?.name || 'Anonymous' }}
                            </h4>
                            <span v-if="question.user?.username" class="text-sm text-gray-500 dark:text-gray-400 truncate">@{{ question.user.username }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ question.posted_at || question.created_at }}</p>
                    </router-link>
                </div>
                <div class="relative inline-block ml-2 self-start" v-if="authStore.isAuthenticated && question.owner">
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
                                <button @click.stop="$router.push('/qna/ask/' + question.slug); showDropdown = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-2">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                    Edit Question
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
                                                This action cannot be undone. This will permanently delete your question.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click.stop="delete_record">Continue</AlertDialogAction>
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
                <h3 
                    class="font-bold text-xl text-gray-900 dark:text-white mb-3 leading-tight line-clamp-2 cursor-pointer hover:text-laravel dark:hover:text-laravel transition-colors"
                    @click.stop="router.push(question_url)">
                    {{ question.title }}
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none mb-4">
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap break-words line-clamp-3" 
                       v-html="question.content?.substring(0, 200) + (question.content?.length > 200 ? '...' : '')" 
                       @click.stop></p>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <button @click.stop="handleLike" 
                            :class="[
                                'flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200',
                                question.liked 
                                    ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20' 
                                    : 'text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                            ]">
                            <i :class="question.liked ? 'fas fa-heart' : 'far fa-heart'" class="text-base"></i>
                            <span class="text-sm font-medium">{{ question.likes_count || 0 }}</span>
                        </button>
                        <button @click.stop="handleComment" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <MessageSquare class="w-4 h-4" />
                            <span class="text-sm font-medium">{{ question.comments_count || question.answers_count || 0 }}</span>
                        </button>
                        <button @click.stop="handleShare" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <i class="far fa-paper-plane text-base"></i>
                            <span class="text-sm font-medium">Share</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="far fa-eye"></i>
                            <span>{{ question.views_count || question.views || 0 }} views</span>
                        </div>
                        <!-- Bookmark/Save Button -->
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
    </div>
</template>
<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>