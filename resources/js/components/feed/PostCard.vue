<script setup>
import { onMounted, ref, defineEmits, computed, defineProps } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog'
import { CardFooter } from '@/components/ui/card';
import Modal from '../elements/Modal.vue'
import PostForm from './PostForm.vue';

const { post } = defineProps(['post'])
const element = ref(null)
const showDropdown = ref(false)
const isModalVisible = ref(false)
const emit = defineEmits(['load_more', 'fetch', 'delete_post', 'liked_action', 'share_url'])
import { useRouter } from 'vue-router';
const $router = useRouter();
const authStore = useAuthStore()
const post_url = computed(() => '/@' + post.user.username + '/' + post.post_code)

onMounted(() => {
    if (element.value.classList.contains('last_item')) {
        checkItem();
    }
});

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
    if(!authStore.isAuthenticated) {
        $router.push('/login')
    }
    else {
        const response = await axios.get(`/api/v1/posts/like-unlike/${post.post_code}`, authStore.config)
        if(response.data.status == 'success') {
            emit('liked_action', [post.post_code, response.data.liked])
        }
        else {
            alert('Something went wrong')
        }
    }
};

const handleComment = () => {
    if(!authStore.isAuthenticated) {
        $router.push('/login')
    }
    else {
        $router.push(post_url.value)
    }
};

const handleShare = () => {
    emit('share_url', post_url.value)
};

const fetch = () => {
    emit('fetch')
}

const closeModal = () => {
    isModalVisible.value = false
};
</script>
<template>
    <div ref="element">
        <div @click="$router.push(post_url)" class="bg-gray-700/40 shadow-md rounded-lg overflow-hidden mb-4 hover:cursor-pointer hover:bg-gray-800/80 transition-all duration-350 ease-in">
            <div class="flex items-center p-4">
                <router-link :to="'/' + post.user.username" @click.stop>
                    <img v-if="post.user?.profile_photo" :src="post.user?.profile_photo" alt="User Avatar"
                        class="w-12 h-12 rounded-full" @error="errorFallbackUserImage" />
                    <img v-else src="/assets/front/images/user.png" alt="User Avatar" class="w-12 h-12 rounded-full" @error="errorFallbackUserImage" />
                </router-link>
                <router-link @click.stop :to="'/' + post.user.username" class="ml-3">
                    <p class="font-semibold text-white text-lg">{{ post.user.name }}</p>
                    <p class="text-sm text-gray-500">@{{ post.user.username }}</p>
                </router-link>
                <div class="relative inline-block ml-auto self-start" v-if="authStore.isAuthenticated && post.owner">
                    <i class="fas fa-ellipsis-v cursor-pointer text-white p-2" @click.stop="showDropdown = !showDropdown"></i>
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="showDropdown"
                            class="absolute right-0 z-10 w-32 origin-top-right divide-y divide-gray-100 ring-1 shadow-lg ring-black/5 focus:outline-hidden"
                            role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                            <div class="" role="none">
                                <!-- Active: "bg-gray-100 text-gray-900 outline-hidden", Not Active: "text-gray-700" -->
                                <div @click.stop="edit_post()"
                                    class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out rounded-md bg-white hover:bg-gray-200 mb-1">
                                    <i class="fas fa-pencil-alt mr-1"></i>
                                    Edit
                                </div>
                                <!-- <a href="#" @click="action('duplicate')" class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out rounded-md bg-white hover:bg-gray-200">
                                    <i class="fas fa-files mr-1"></i>
                                    Duplicate
                                </a> -->
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
                                                This action cannot be undone. This will delete your post.
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
            <div class="px-4">
                <h3 class="font-bold text-xl text-white text-gray-800 mb-0" v-if="post.title">{{ post.title }}</h3>
                <p class="text-gray-700 mb-2 text-white" v-html="renderContent(post.content)" @click.stop></p>
                <div v-if="post.media_urls && post.media_urls.length" class="grid gap-2 mb-4">
                    <img v-if="post.media_urls.length === 1" :src="post.media_urls[0]" alt="Post Media"
                        class="cursor-pointer rounded-lg object-cover w-full h-80" @error="errorFallbackImage" />
                    <div v-else-if="post.media_urls.length === 2" class="grid grid-cols-2 gap-2">
                        <img v-for="(media, index) in post.media_urls" v-if="index < 2" :key="media" :src="media"
                            alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" @error="errorFallbackImage" />
                    </div>
                    <div v-else-if="post.media_urls.length === 3" class="grid grid-cols-2 gap-2">
                        <img v-for="(media, index) in post.media_urls.slice(0, 2)" :key="media" :src="media"
                            alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" @error="errorFallbackImage" />
                        <img :src="post.media_urls[2]" alt="Post Media"
                            class="cursor-pointer col-span-2 rounded-lg object-cover h-40 w-full" @error="errorFallbackImage" />
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2 relative">
                        <img v-for="(media, index) in post.media_urls.slice(0, 4)" :key="media" :src="media"
                            alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" @error="errorFallbackImage" />
                        <div v-if="post.media_urls.length > 4"
                            class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl font-bold rounded-lg"
                            :style="{ gridArea: '2 / 2 / 3 / 3' }">
                            +{{ post.media_urls.length - 4 }} more
                        </div>
                    </div>
                </div>
            </div>
            <CardFooter class="flex items-center justify-between p-4 pt-0">
                <div class="flex items-center space-x-4">
                    <span :title="post.likes_count+' Likes'" class="text-white hover:text-red-400"><i class="fa-heart" :class="post.liked ? 'fas text-red-500':'far'" @click.stop="handleLike"></i>&nbsp;{{ post.likes_count }}</span>
                    <span :title="post.comments_count+' Comments'" class="text-white hover:text-red-400"><i class="far fa-messages" @click.stop="handleComment"></i>&nbsp;{{ post.comments_count }}</span>
                    <span :title="post.views_count+' Views'" class="text-white hover:text-red-400"><i class="far fa-chart-column"></i>&nbsp;{{ post.views_count }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-white text-sm">{{ post.posted_at }}</span>
                    <span class="bg-red-400 w-[5px] h-[5px] rounded"></span>
                    <span :title="'Share Post'" class="text-white hover:text-red-400"><i class="far fa-paper-plane-top" @click.stop="handleShare"></i></span>
                </div>
            </CardFooter>
        </div>
        <Transition name="fade">
            <PostForm v-if="isModalVisible" @close="closeModal" @fetch="fetch" :post="post" />
        </Transition>
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
}
.mention-link:hover {
    text-decoration: underline;
}
</style>
