<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { LucideChevronLeft, LucideChevronRight, LucideEllipsisVertical } from 'lucide-vue-next';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { CardFooter } from '@/components/ui/card';
import PostForm from './PostForm.vue';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const { post } = defineProps(['post'])
const element = ref(null)
const showDropdown = ref(false)
const isModalVisible = ref(false)
const emit = defineEmits(['load_more', 'fetch', 'delete_post', 'liked_action', 'share_url'])
const $router = useRouter();
const authStore = useAuthStore()
const post_url = computed(() => '/@' + post.user.username + '/' + post.post_code)

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
        <div class="flex flex-row border-b-[0.5px] twitter-border-color">
            <div class="postContent w-full">
                <header class="flex flex-row w-full items-center gap-2">
                    <div class="userIcon p-3 pe-1">
                        <router-link :to="'/' + post.user.username" @click.stop class="w-10 h-10 bg-slate-400 rounded-full">
                            <img v-if="post.user?.profile_photo" :src="post.user?.profile_photo" alt="User Avatar"
                                class="w-12 h-12 rounded-full" />
                            <img v-else src="/assets/front/images/user.png" alt="User Avatar" class="w-12 h-12 rounded-full" />
                        </router-link>
                    </div>
                    <div class="cursor-pointer flex flex-col">
                        <router-link @click.stop :to="'/' + post.user.username" class='font-bold text-sm'>{{ post.user.name }}</router-link>
                        <router-link @click.stop :to="'/' + post.user.username" class='text-sm text-gray-500'>@{{ post.user.username }}</router-link>
                    </div>
                    <div class="relative inline-block ml-auto" v-if="authStore.isAuthenticated && post.owner">
                        <p :title="'More Options'" class="icon-hover text-gray-500 hover:bg-primary/20 hover:text-primary hover:bg-opacity-25" @click.stop="showDropdown = !showDropdown">
                            <LucideEllipsisVertical />
                        </p>
                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <div v-if="showDropdown" v-click-outside="{ closeCondition: () => showDropdown, closeAction: () => showDropdown = false }" class="absolute mt-1 right-2 z-10 w-32 origin-top-right divide-y divide-gray-100 rounded-md focus:outline-hidden"
                                role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                                <div class="" role="none">
                                    <!-- Active: "bg-gray-100 text-gray-900 outline-hidden", Not Active: "text-gray-700" -->
                                    <!-- <a href="#" @click="action('duplicate')" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer transition-all duration-150 ease-in-out rounded-md bg-white dark:text-white dark:bg-gray-900 dark:hover:bg-gray-800/80 hover:bg-gray-200">
                                        <i class="fas fa-files mr-1"></i>
                                        Duplicate
                                    </a> -->
                                    <div @click.stop="edit_post()"
                                        class="block px-4 py-2 text-sm text-gray-700 cursor-pointer transition-all duration-150 ease-in-out rounded-md shadow dark:shadow-white/10 bg-white dark:text-white dark:bg-gray-900 dark:hover:bg-gray-800/80 hover:bg-gray-200 mb-1">
                                        <i class="fas fa-pencil-alt mr-1"></i>
                                        Edit
                                    </div>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <div @click.stop
                                                class="block px-4 py-2 text-sm text-gray-700 cursor-pointer transition-all duration-150 ease-in-out rounded-md shadow dark:shadow-white/10 bg-white dark:text-white dark:bg-gray-900 dark:hover:bg-gray-800/80 hover:bg-gray-200">
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
                </header>
                <div class='postText space-y-3'>
                    <div @click.stop="$router.push(post_url)" class="cursor-pointer px-4">
                        <h3 class='text-lg font-semibold'>{{ post.title }}</h3>
                        <p class='text-sm' v-html="renderContent(post.content)" />
                    </div>
                    <div v-if="post.media_urls && post.media_urls.length" class="px-2 grid gap-2 mb-4">
                        <img @click="handleOpenGallery(0)" v-if="post.media_urls.length === 1" :src="post.media_urls[0]" alt="Post Media" class="cursor-pointer rounded-lg object-cover w-full h-80" />
                        <div v-else-if="post.media_urls.length === 2" class="grid grid-cols-2 gap-2">
                            <img @click="handleOpenGallery(index)" v-for="(media, index) in post.media_urls" v-if="index < 2" :key="media" :src="media" alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" />
                        </div>
                        <div v-else-if="post.media_urls.length === 3" class="grid grid-cols-2 gap-2">
                            <img @click="handleOpenGallery(index)" v-for="(media, index) in post.media_urls.slice(0, 2)" :key="media" :src="media" alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" />
                            <img @click="handleOpenGallery(index)" :src="post.media_urls[2]" alt="Post Media" class="cursor-pointer col-span-2 rounded-lg object-cover h-40 w-full" />
                        </div>
                        <div v-else class="grid grid-cols-2 gap-2 relative">
                            <img @click="handleOpenGallery(index)" v-for="(media, index) in post.media_urls.slice(0, 4)" :key="media" :src="media" alt="Post Media" class="cursor-pointer rounded-lg object-cover h-40 w-full" />
                            <div @click="handleOpenGallery(3)" v-if="post.media_urls.length > 4" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl font-bold rounded-lg cursor-pointer" :style="{ gridArea: '2 / 2 / 3 / 3' }">
                                +{{ post.media_urls.length - 4 }} more
                            </div>
                        </div>
                    </div>
                    <CardFooter class="flex items-center justify-between py-2 px-4 pt-0">
                        <div class="flex items-center space-x-4">
                            <span :title="post.likes_count+' Likes'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="fa-heart" :class="post.liked ? 'fas text-red-500':'far'" @click.stop="handleLike"></i>&nbsp;{{ post.likes_count }}</span>
                            <span :title="post.comments_count+' Comments'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-messages" @click.stop="handleComment"></i>&nbsp;{{ post.comments_count }}</span>
                            <span :title="post.views_count+' Views'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-chart-column"></i>&nbsp;{{ post.views_count }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span :title="'Posted At'" class="text-gray-500 cursor-pointer text-sm">{{ post.posted_at }}</span>
                            <span class="bg-red-400 w-[5px] h-[5px] rounded"></span>
                            <span :title="'Share Post'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-paper-plane-top" @click.stop="handleShare"></i></span>
                        </div>
                    </CardFooter>
                </div>
            </div>
        </div>
        <!-- <Transition name="fade"> -->
            <PostForm v-if="isModalVisible" @close="closeModal" @fetch="fetch" :post="post" />
        <!-- </Transition> -->
        <Dialog v-model:open="isGalleryVisible">
            <DialogContent class="max-w-4xl p-0 border-0 shadow-md shadow-white">
                <div class="relative">
                    <img :src="post.media_urls[selectedImageIndex]" alt="Gallery image" class="w-full h-auto object-contain rounded-lg" />
                    <button v-if="selectedImageIndex > 0" @click="prevImage" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-primary/20 text-white p-2 rounded-full">
                        <LucideChevronLeft class="w-5 h-5" />
                    </button>
                    <button v-if="selectedImageIndex < post.media_urls.length - 1" @click="nextImage" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-primary/20 text-white p-2 rounded-full">
                        <LucideChevronRight class="w-5 h-5" />
                    </button>
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
}

.mention-link:hover {
    text-decoration: underline;
}
</style>
