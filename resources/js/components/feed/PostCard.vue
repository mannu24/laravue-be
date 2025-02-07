<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { LucideChartBar, LucideChevronLeft, LucideChevronRight, LucideDot, LucideEllipsis, LucideHeart, LucideMessageCircle, LucideShare } from 'lucide-vue-next';
import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogClose
} from '@/components/ui/dialog';
import { computed, defineEmits, defineProps, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import PostForm from './PostForm.vue';
import { cn } from '/resources/js/lib/utils';
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

onMounted(() => {
    if (element.value.classList.contains('last_item')) {
        checkItem();
    }
});

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
</script>
<template>
    <div ref="element">
        <div class='flex flex-row border-t-[0.5px] twitter-border-color'>
            <div class='userIcon p-3 pe-1'>
                <router-link :to="'/' + post.user.username" @click.stop class='w-10 h-10 bg-slate-400 rounded-full'>
                    <img v-if="post.user?.profile_photo" :src="post.user?.profile_photo" alt="User Avatar"
                        class="w-12 h-12 rounded-full" />
                    <img v-else src="/assets/front/images/user.png" alt="User Avatar" class="w-12 h-12 rounded-full" />
                </router-link>
            </div>
            <div class='postContent p-1 px-2 w-full'>
                <header class='flex flex-row w-full items-center justify-between'>
                    <div @click="$router.push(post_url)" class='cursor-pointer flex flex-row items-center space-x-1'>
                        <router-link @click.stop :to="'/' + post.user.username" class='font-bold text-sm'>{{
                            post.user.name
                        }}</router-link>
                        <router-link @click.stop :to="'/' + post.user.username" class='text-sm text-gray-500'>@{{
                            post.user.username }}</router-link>
                        <p class='text-sm text-gray-500'>
                            <LucideDot />
                        </p>
                        <p class='text-sm text-gray-500'>{{ post.posted_at }}</p>
                    </div>
                    <div :class="cn(
                        'flex flex-row items-center justify-center h-[40px] w-[40px]',
                        authStore?.isAuthenticated ? 'block' : 'invisible'
                    )">
                        <DropdownMenu>
                            <DropdownMenuTrigger>
                                <p class=' icon-hover text-gray-500 hover:bg-primary/20 hover:text-primary
                        hover:bg-opacity-25'>
                                    <LucideEllipsis />
                                </p>
                            </DropdownMenuTrigger>
                            <AlertDialog>
                                <DropdownMenuContent>
                                    <DropdownMenuItem>
                                        <AlertDialogTrigger as-child>
                                            <div @click.stop>
                                                <i class=" fas fa-trash mr-1"></i> Delete
                                            </div>
                                        </AlertDialogTrigger>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem>
                                        <div @click.stop="edit_post()">
                                            <i class="fas fa-pencil-alt mr-1"></i>
                                            Edit
                                        </div>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action cannot be undone. This will delete your post.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction @click.stop="action('delete')">Continue
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </DropdownMenu>
                    </div>
                </header>
                <div class='postText pe-2 space-y-3'>
                    <div @click="$router.push(post_url)" class="cursor-pointer flex flex-col gap-3">
                        <h3 class='text-lg font-semibold'>
                            {{ post.title }}
                        </h3>
                        <p class='text-sm' v-html="renderContent(post.content)" @click.stop />
                    </div>

                    <!-- Media Images Collage -->
                    <div v-if="post.media_urls && post.media_urls.length" class="mt-2 p-2 rounded-xl">
                        <!-- Single Image -->
                        <template v-if="post.media_urls.length === 1">
                            <img :src="post.media_urls[0]" @click="handleOpenGallery(0)"
                                class="w-full h-auto object-cover rounded-xl cursor-pointer" alt="Post media" />
                        </template>

                        <!-- Two Images: side-by-side -->
                        <template v-else-if="post.media_urls.length === 2">
                            <div class="grid grid-cols-2 gap-1">
                                <img v-for="(url, index) in post.media_urls" :key="index" :src="url"
                                    @click="handleOpenGallery(index)"
                                    class="w-full h-48 object-cover rounded-xl cursor-pointer" alt="Post media" />
                            </div>
                        </template>

                        <!-- Three Images: one big image on the left, two stacked on the right -->
                        <template v-else-if="post.media_urls.length === 3">
                            <div class="grid grid-cols-2 gap-1">
                                <div class="row-span-2">
                                    <img :src="post.media_urls[0]" @click="handleOpenGallery(0)"
                                        class="w-full h-full object-cover rounded-xl cursor-pointer" alt="Post media" />
                                </div>
                                <img :src="post.media_urls[1]" @click="handleOpenGallery(1)"
                                    class="w-full h-24 object-cover rounded-xl cursor-pointer" alt="Post media" />
                                <img :src="post.media_urls[2]" @click="handleOpenGallery(2)"
                                    class="w-full h-24 object-cover rounded-xl cursor-pointer" alt="Post media" />
                            </div>
                        </template>

                        <!-- Four (or more) Images: same layout as three, with overlay on the last image if more than four -->
                        <template v-else>
                            <div class="grid grid-cols-2 gap-1">
                                <div class="row-span-2">
                                    <img :src="post.media_urls[0]" @click="handleOpenGallery(0)"
                                        class="w-full h-full object-cover rounded-xl cursor-pointer" alt="Post media" />
                                </div>
                                <img :src="post.media_urls[1]" @click="handleOpenGallery(1)"
                                    class="w-full h-24 object-cover rounded-xl cursor-pointer" alt="Post media" />
                                <img :src="post.media_urls[2]" @click="handleOpenGallery(2)"
                                    class="w-full h-24 object-cover rounded-xl cursor-pointer" alt="Post media" />
                                <div class="relative" @click="handleOpenGallery(3)">
                                    <img :src="post.media_urls[3]"
                                        class="w-full h-24 object-cover rounded-xl cursor-pointer" alt="Post media" />
                                    <div v-if="post.media_urls.length > 4"
                                        class="absolute cursor-pointer inset-0 bg-black bg-opacity-50 flex items-center justify-center rounded-xl">
                                        <span class="text-white text-xl font-bold">
                                            +{{ post.media_urls.length - 4 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <!-- End of Media Images Collage -->

                    <!-- Icons -->
                    <div class='flex flex-row w-full justify-between space-x-1 py-3 text-slate-500'>
                        <!-- <ReplyDialog tweet={tweet} repliesCount={repliesCount} /> -->
                        <div class='flex flex-row gap-6'>
                            <p @click.stop="handleComment"
                                class='icon-hover hover:bg-primary/20 hover:text-primary hover:bg-opacity-25'>
                                <LucideMessageCircle class="w-5 h-5" />
                                <span :title="post.comments_count + ' Comments'" class='absolute ms-[23px]'>{{
                                    post.comments_count }}</span>
                            </p>
                            <p @click.stop="handleLike"
                                class='icon-hover hover:bg-secondary/20 hover:text-secondary hover:bg-opacity-25'>
                                <LucideHeart class="w-5 h-5" />
                                <span :title="post.likes_count + ' Likes'" class='absolute ms-[23px]'>{{
                                    post.likes_count }}</span>
                            </p>

                            <p class='icon-hover hover:bg-primary/20 hover:text-primary hover:bg-opacity-25'>
                                <LucideChartBar class="w-5 h-5 rotate-[270deg] scale-y-[-1]" />
                                <span :title="post.views_count + ' Views'" class='absolute ms-[23px]'>{{
                                    post.views_count
                                }}</span>
                            </p>
                        </div>
                        <div class='flex flex-row '>
                            <span :title="'Share Post'" @click.stop="handleShare"
                                class='icon-hover hover:bg-secondary/20 hover:text-secondary hover:bg-opacity-25'>
                                <LucideShare class="w-5 h-5" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Transition name="fade">
            <PostForm v-if="isModalVisible" @close="closeModal" @fetch="fetch" :post="post" />
        </Transition>

        <!-- Gallery Popup using shadcn ui Dialog -->
        <Dialog v-model:open="isGalleryVisible">
            <DialogContent class="max-w-4xl p-0">
                <div class="relative">
                    <img :src="post.media_urls[selectedImageIndex]" alt="Gallery image"
                        class="w-full h-auto object-contain" />
                    <button v-if="selectedImageIndex > 0" @click="prevImage"
                        class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-primary/20 text-white p-2 rounded-full">
                        <LucideChevronLeft class="w-5 h-5" />
                    </button>
                    <button v-if="selectedImageIndex < post.media_urls.length - 1" @click="nextImage"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-primary/20 text-white p-2 rounded-full">
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
