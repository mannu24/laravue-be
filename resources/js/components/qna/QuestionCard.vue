<script setup>
import { onMounted, ref, defineEmits, computed, defineProps } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog'
import Modal from '../elements/Modal.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../ui/card'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from '../ui/toast'
import { Button } from '../ui/button'
import { Skeleton } from '../ui/skeleton'

const { toast } = useToast()
const loading = ref(true)
const element = ref(null)
const showDropdown = ref(false)
const isModalVisible = ref(false)
const { question } = defineProps(['question'])
const emit = defineEmits(['load_more', 'fetch', 'delete_question', 'liked_action', 'share_url'])
const $router = useRouter();
const authStore = useAuthStore()
const question_url = computed(() => '/question/' + question.slug)

const goToQuestion = (id) => {
    $router.push({ name: 'question', params: { id } })
}

onMounted(() => {
    if (element.value.classList.contains('last_item')) {
        checkItem();
    }
});

const action = async (type) => {
    showDropdown.value = false
    await axios.delete(`/api/v1/questions/${question.slug}`, authStore.config).then(() => {
        emit('delete_question', question.slug)
    })
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
        const response = await axios.get(`/api/v1/questions/like-unlike/${question.slug}`, authStore.config)
        if (response.data.status == 'success') {
            emit('liked_action', [question.slug, response.data.liked])
        }
        else {
            toast({
                title: "Error",
                description: "Something went wrong",
                variant: "destructive",
            })
        }
    }
};

const handleComment = () => {
    if(!authStore.isAuthenticated) {
        $router.push('/login')
    }
    else {
        $router.push(question_url.value)
    }
};

const handleShare = () => {
    emit('share_url', question_url.value)
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
        <Card class="mb-4">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>
                        <router-link :to="`/qna/${question.slug}`" @click.prevent="goToQuestion(question.slug)"
                            class="text-primary hover:underline">
                            {{ question.title }}
                        </router-link>
                    </CardTitle>
                    <div class="relative inline-block ml-auto self-start" v-if="authStore.isAuthenticated && question.owner">
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
                                    <div @click.stop="$router.push('/qna/ask/'+question.slug)"
                                        class="block cursor-pointer px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out rounded-md bg-white hover:bg-gray-200 mb-1">
                                        <i class="fas fa-pencil-alt mr-1"></i>
                                        Edit
                                    </div>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <div @click.stop
                                                class="block cursor-pointer px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out rounded-md bg-white hover:bg-gray-200">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </div>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will delete your question.
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
                <CardDescription>
                    Asked by: {{ question.user ? question.user.name : 'Anonymous' }} |
                    Upvotes: {{ question.upvotes_count }} |
                    {{ new Date(question.created_at).toLocaleString() }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <p>{{ question.content.substring(0, 200) }}...</p>
            </CardContent>
            <CardFooter class="flex items-center justify-between p-4 pt-0">
                <div class="flex items-center space-x-4">
                    <span :title="question.likes_count+' Likes'" class="text-white hover:text-red-400"><i class="fa-heart" :class="question.liked ? 'fas text-red-500':'far'" @click.stop="handleLike"></i>&nbsp;{{ question.likes_count }}</span>
                    <span :title="question.comments_count+' Comments'" class="text-white hover:text-red-400"><i class="far fa-messages" @click.stop="handleComment"></i>&nbsp;{{ question.comments_count }}</span>
                    <span :title="question.views_count+' Views'" class="text-white hover:text-red-400"><i class="far fa-chart-column"></i>&nbsp;{{ question.views_count }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-white text-sm">{{ question.posted_at }}</span>
                    <span class="bg-red-400 w-[5px] h-[5px] rounded"></span>
                    <span :title="'Share Post'" class="text-white hover:text-red-400"><i class="far fa-paper-plane-top" @click.stop="handleShare"></i></span>
                </div>
            </CardFooter>
        </Card>

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
