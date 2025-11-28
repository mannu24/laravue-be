<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { LucideEllipsisVertical } from 'lucide-vue-next';
import axios from 'axios';

const showDropdown = ref(false)
const authStore = useAuthStore()
const router = useRouter()
const { comment } = defineProps(['comment'])
const emit = defineEmits(['commentDeleted', 'commentLiked'])

const handleLike = async () => {
    if(!authStore.isAuthenticated) {
        router.push('/login')
    }
    else {
        const response = await axios.get(`/api/v1/comment/like-unlike/${comment.id}`, authStore.config)
        if(response.data.status == 'success') {
            emit('commentLiked', [comment.id, response.data.liked])
        }
        else {
            alert('Something went wrong')
        }
    }
};

const deletePost = async () => {
    showDropdown.value = false
    await axios.delete(`/api/v1/post/comment/${comment.id}`, authStore.config).then(() => {
        emit('commentDeleted', comment.id)
    })
}

const renderContent = (content) => {
    return content.replace(/@(\w+)/g, '<a href="/@$1" class="mention-link">@$1</a>');
};

const errorFallbackUserImage = (event) => {
    event.target.src = '/assets/front/images/user.png'
}
</script>
<template>
    <div class="flex items-start gap-2 py-1.5 group">
        <!-- Avatar -->
        <router-link 
            :to="comment.user.username != authStore.user?.username ? '/@' + comment.user.username : '/dashboard'" 
            @click.stop 
            class="flex-shrink-0"
        >
            <img 
                v-if="comment.user?.profile_photo" 
                :src="comment.user?.profile_photo" 
                alt="User Avatar"
                class="w-6 h-6 rounded-full object-cover"
                @error="errorFallbackUserImage" 
            />
            <img 
                v-else 
                src="/assets/front/images/user.png" 
                alt="User Avatar"
                class="w-6 h-6 rounded-full object-cover"
                @error="errorFallbackUserImage" 
            />
        </router-link>

        <!-- Comment Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div class="inline-flex items-baseline gap-1.5 flex-wrap">
                        <router-link 
                            @click.stop 
                            :to="comment.user.username != authStore.user?.username ? '/@' + comment.user.username : '/dashboard'"
                            class="font-semibold text-sm text-gray-900 dark:text-gray-100 hover:underline"
                        >
                            {{ comment.user.name }}
                        </router-link>
                        <span 
                            class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap break-words" 
                            v-html="renderContent(comment.content)" 
                            @click.stop
                        ></span>
                    </div>
                    
                    <!-- Actions Row -->
                    <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="text-xs">{{ comment.posted_at }}</span>
                        <button
                            class="font-semibold hover:underline flex items-center gap-1"
                            :class="comment.liked ? 'text-red-600 dark:text-red-400' : ''"
                            @click.stop="handleLike"
                        >
                            <i :class="comment.liked ? 'fas fa-heart' : 'far fa-heart'" class="text-xs"></i>
                            <span>{{ comment.likes_count > 0 ? comment.likes_count : 'Like' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Ellipsis Dropdown -->
                <div
                    v-if="authStore.isAuthenticated && comment.owner"
                    class="relative flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity"
                    @click.stop
                >
                    <button
                        @click="showDropdown = !showDropdown"
                        class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-500 dark:text-gray-400"
                    >
                        <LucideEllipsisVertical class="w-4 h-4" />
                    </button>
                    <transition name="fade">
                        <div
                            v-if="showDropdown"
                            @click.stop
                            class="absolute right-0 top-full mt-1 z-20 w-32 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden"
                            role="menu"
                            aria-orientation="vertical"
                        >
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <button
                                        @click.stop
                                        class="w-full text-left px-3 py-2 text-xs flex items-center gap-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400"
                                    >
                                        <i class="fas fa-trash text-xs"></i>
                                        Delete
                                    </button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action cannot be undone. This will delete your comment.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction @click.stop="deletePost">Continue</AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>