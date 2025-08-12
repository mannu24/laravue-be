<script setup>
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import axios from 'axios';
import { computed, defineEmits, defineProps, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '../ui/card';
import { useToast } from '../ui/toast';
import { LucideEllipsisVertical } from 'lucide-vue-next';

const { toast } = useToast()
const element = ref(null)
const showDropdown = ref(false)
const { question } = defineProps(['question'])
const emit = defineEmits(['load_more', 'fetch', 'delete_question', 'liked_action', 'share_url'])
const router = useRouter();
const authStore = useAuthStore()
const question_url = computed(() => '/question/' + question.slug)

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

</script>
<template>
    <Card :class="[
        'mb-4 transition-colors duration-200',
        'bg-white text-gray-800 border-gray-200',
        'dark:bg-gray-800 dark:text-white dark:border-gray-700'
    ]">
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle class="text-xl font-bold">
                    <router-link :to="`/qna/${question.slug}`" @click.prevent="goToQuestion(question.slug)" :class="[
                        'hover:underline',
                        'text-blue-600 hover:text-blue-700',
                        'dark:text-blue-400 dark:hover:text-blue-300'
                    ]">
                        {{ question.title }}
                    </router-link>
                </CardTitle>
                <div class="relative inline-block ml-auto self-start" v-if="authStore.isAuthenticated && question.owner">
                    <p :title="'More Options'" class="icon-hover text-gray-500 hover:bg-primary/20 hover:text-primary hover:bg-opacity-25" @click.stop="showDropdown = !showDropdown">
                        <LucideEllipsisVertical />
                    </p>
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="showDropdown" v-click-outside="{ closeCondition: () => showDropdown, closeAction: () => showDropdown = false }" class="absolute mt-1 right-2 z-10 w-32 origin-top-right divide-y divide-gray-100 rounded-md focus:outline-hidden" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                            <div class="" role="none">
                                <!-- Active: "bg-gray-100 text-gray-900 outline-hidden", Not Active: "text-gray-700" -->
                                <!-- <a href="#" @click="action('duplicate')" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer transition-all duration-150 ease-in-out rounded-md bg-white dark:text-white dark:bg-gray-900 dark:hover:bg-gray-800/80 hover:bg-gray-200">
                                    <i class="fas fa-files mr-1"></i>
                                    Duplicate
                                </a> -->
                                <div @click.stop="$router.push('/qna/ask/' + question.slug)"
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
                                            <AlertDialogAction @click.stop="delete_record">Continue</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                    </transition>
                        <!-- <div v-if="showDropdown" :class="[
                            'absolute right-0 z-10 mt-2 w-48 rounded-md shadow-lg ring-1 ring-black ring-opacity-5',
                            'bg-white',
                            'dark:bg-gray-700'
                        ]">
                            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                <a @click.stop="$router.push('/qna/ask/' + question.slug)" :class="[
                                    'block px-4 py-2 text-sm',
                                    'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                                    'dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white'
                                ]" role="menuitem">
                                    <i class="fas fa-pencil-alt mr-2"></i> Edit
                                </a>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <a :class="[
                                            'block px-4 py-2 text-sm',
                                            'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
                                            'dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white'
                                        ]" role="menuitem">
                                            <i class="fas fa-trash mr-2"></i> Delete
                                        </a>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent :class="[
                                        'dark:bg-gray-800 dark:text-white',
                                        'bg-white text-gray-800'
                                    ]">
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                            <AlertDialogDescription :class="[
                                                'dark:text-gray-400',
                                                'text-gray-600'
                                            ]">
                                                This action cannot be undone. This will delete your question.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel :class="[
                                                'dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600',
                                                'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                            ]">
                                                Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click.stop="delete_record"
                                                class="bg-red-600 text-white hover:bg-red-700">Delete
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div> -->
                </div>
            </div>
            <CardDescription :class="[
                'dark:text-gray-400',
                'text-gray-600'
            ]">
                <div class="flex items-center space-x-2 mt-2">
                    <img :src="question.user?.profile_photo || '/assets/front/images/user.png'" alt="User Avatar"
                        class="w-6 h-6 rounded-full border" :class="[
                            'dark:border-gray-600',
                            'border-gray-300'
                        ]">
                    <span>{{ question.user ? question.user.name : 'Anonymous' }}</span>
                </div>
            </CardDescription>
        </CardHeader>
        <CardContent :class="[
            'dark:text-gray-300',
            'text-gray-700'
        ]">
            <p>{{ question.content.substring(0, 200) }}...</p>
        </CardContent>
        <CardFooter class="flex items-center justify-between py-2 px-4 pt-0">
            <div class="flex items-center space-x-4">
                <span :title="question.likes_count+' Likes'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="fa-heart" :class="question.liked ? 'fas text-red-500':'far'" @click.stop="handleLike"></i>&nbsp;{{ question.likes_count }}</span>
                <span :title="question.comments_count+' Comments'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-messages" @click.stop="handleComment"></i>&nbsp;{{ question.comments_count }}</span>
                <span :title="question.views_count+' Views'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-chart-column"></i>&nbsp;{{ question.views_count }}</span>
            </div>
            <div class="flex items-center space-x-2">
                <span :title="'Posted At'" class="text-gray-500 cursor-pointer text-sm">{{ question.posted_at }}</span>
                <span class="bg-red-400 w-[5px] h-[5px] rounded"></span>
                <span :title="'Share Post'" class="text-gray-500 cursor-pointer hover:text-red-400"><i class="far fa-paper-plane-top" @click.stop="handleShare"></i></span>
            </div>
        </CardFooter>
    </Card>
</template>
<style scoped>
.mention-link {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
    border-radius: 0.25rem;
}

.mention-link.dark {
    color: rgb(96 165 250);
    background-color: rgba(96, 165, 250, 0.1);
}

.mention-link.light {
    color: rgb(37 99 235);
    background-color: rgba(37, 99, 235, 0.1);
}

.mention-link:hover {
    text-decoration: underline;
}
</style>