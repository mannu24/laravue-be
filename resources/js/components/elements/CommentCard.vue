<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { CardFooter } from '@/components/ui/card';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog'

const showDropdown = ref(false)
const authStore = useAuthStore()
const { comment } = defineProps(['comment'])
const emit = defineEmits(['commentDeleted', 'commentLiked'])

const handleLike = async () => {
    if(!authStore.isAuthenticated) {
        $router.push('/login')
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
    return content.replace(/@(\w+)/g, '<a href="/$1">@$1</a>');
};
</script>
<template>
    <div class="bg-gray-800/80 mt-4 shadow-md rounded-lg overflow-hidden mb-4 hover:cursor-pointer hover:bg-gray-700/40 transition-all duration-350 ease-in">
        <div class="flex items-center p-4">
            <router-link :to="'/' + comment.user.username" @click.stop>
                <img v-if="comment.user?.profile_photo" :src="comment.user?.profile_photo" alt="User Avatar"
                    class="w-8 h-8 rounded-full object-cover" />
                <img v-else src="/assets/front/images/user.png" alt="User Avatar" class="w-8 h-8 rounded-full" />
            </router-link>
            <router-link @click.stop :to="'/' + comment.user.username" class="ml-3">
                <p class="font-semibold text-white">{{ comment.user.name }}</p>
                <p class="text-xs text-gray-500">@{{ comment.user.username }}</p>
            </router-link>
            <div class="relative inline-block ml-auto self-start" v-if="authStore.isAuthenticated && comment.owner">
                <i class="fas fa-ellipsis-v cursor-pointer text-white p-2" @click.stop="showDropdown = !showDropdown"></i>
                <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95">
                    <div v-if="showDropdown"
                        class="absolute right-0 z-10 mt-2 w-32 origin-top-right divide-y divide-gray-100 ring-1 shadow-lg ring-black/5 focus:outline-hidden"
                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="" role="none">
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <div @click.stop
                                        class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out rounded-md bg-white hover:bg-gray-200">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </div>
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
                    </div>
                </transition>
            </div>
        </div>
        <div class="px-4">
            <h3 class="font-bold text-xl text-white text-gray-800 mb-0">{{ comment.title }}</h3>
            <p class="text-gray-700 mb-2 text-white" v-html="renderContent(comment.content)" @click.stop></p>
        </div>
        <CardFooter class="flex items-center justify-between p-4 pt-0">
            <div class="flex items-center space-x-4">
                <span :title="comment.likes_count+' Likes'" class="text-white hover:text-red-400"><i class="fa-heart" :class="comment.liked ? 'fas text-red-500':'far'" @click.stop="handleLike"></i>&nbsp;{{ comment.likes_count }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-white text-sm">{{ comment.posted_at }}</span>
            </div>
        </CardFooter>
    </div>
</template>