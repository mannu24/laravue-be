<template>
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div v-if="loading" class="text-center">
            <Spinner class="h-8 w-8 text-primary" />
            <p class="mt-2 text-gray-600">Loading question...</p>
        </div>

        <div v-else-if="question" class="space-y-6">
            <BackNavigator :items="questionBreadcrumbs" />
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-2xl font-bold">{{ question.title }}</CardTitle>
                        <Button @click="upvoteQuestion" variant="outline" size="sm" class="flex items-center space-x-1">
                            <CircleChevronUp class="h-6 w-6" />
                            <span>{{ question.upvotes_count }}</span>
                        </Button>
                    </div>
                    <CardDescription class="flex items-center space-x-2 text-sm text-gray-500">
                        <UserIcon class="h-4 w-4" />
                        <span>{{ question.user ? question.user.name : 'Anonymous' }}</span>
                        <span>•</span>
                        <span>{{ (question.created_at) }}</span>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-html="truncatedContent" class="prose max-w-none"></div>
                    <Button v-if="isContentTruncated" @click="showFullContent = !showFullContent" variant="link"
                        class="mt-2">
                        {{ showFullContent ? 'Show less' : 'Read more' }}
                    </Button>
                </CardContent>
                <CardFooter class="flex justify-between items-center">
                    <div class="flex space-x-4">
                        <span class="flex items-center space-x-1">
                            <MessageSquareIcon class="h-4 w-4 text-gray-500" />
                            <span class="text-sm text-gray-500">{{ answers.length }} Answers</span>
                        </span>
                        <span v-if="question.views_count" class="flex items-center space-x-1">
                            <EyeIcon class="h-4 w-4 text-gray-500" />
                            <span class="text-sm text-gray-500">{{ question.views_count }} Views</span>
                        </span>
                    </div>
                </CardFooter>
            </Card>

            <!-- Add this button after the question card and before the answers section -->
            <div class="flex justify-end mt-4">
                <Button variant="outline" size="lg" @click="showAnswerForm = !showAnswerForm" class="gap-2">
                    <PenLine class="h-4 w-4" />
                    {{ showAnswerForm ? 'Hide Answer Form' : 'Write an Answer' }}
                </Button>
            </div>

            <!-- Replace the existing answer form Card with this -->
            <Card v-if="showAnswerForm && authToken" class="mt-4">
                <CardHeader>
                    <CardTitle>Your Answer</CardTitle>
                </CardHeader>
                <CardContent>
                    <!-- <MarkDownEditor v-model="newAnswer" placeholder="Write your answer..." :min-height="300" /> -->
                </CardContent>
                <CardFooter class="flex justify-end">
                    <Button @click="submitAnswer">Post Your Answer</Button>
                </CardFooter>
            </Card>

            <Card v-else-if="showAnswerForm && !authToken">
                <CardContent class="text-center py-6">
                    <LockIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-lg font-semibold mb-2">Sign up or log in to answer</p>
                    <p class="text-gray-500 mb-4">You need to be logged in to post an answer to this question.</p>
                    <div class="space-x-4">
                        <Button @click="router.push('/login')">Log in</Button>
                        <Button @click="router.push('/signup')" variant="outline">Sign up</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Answers Section -->
            <div class="space-y-4">
                <h2 class="text-lg font-medium">{{ answers.length }} Answers</h2>
                <AnswersWithReplies :items="answers" :auth-user-id="authUserId" :max-replies="2" @reply="handleReply"
                    @upvote="handleUpvote" />
            </div>
        </div>

        <div v-else class="text-center py-12">
            <AlertCircleIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-xl font-semibold">Question not found</p>
            <p class="text-gray-500 mt-2">The question you're looking for doesn't seem to exist.</p>
            <Button @click="router.push('/questions')" variant="link" class="mt-4">Browse all questions</Button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../components/ui/card'
import { Textarea } from '../components/ui/textarea'
import { Button } from '../components/ui/button'
import { Avatar, AvatarImage, AvatarFallback } from '../components/ui/avatar'
import { useToast } from '../components/ui/toast'
import { useAuthStore } from '../stores/auth'
import { CircleChevronUp, UserIcon, MessageSquareIcon, EyeIcon, LockIcon, AlertCircleIcon } from 'lucide-vue-next'
import BackNavigator from '../components/elements/BackNavigator.vue'
import MarkDownEditor from '../components/elements/MarkDownEditor.vue'
import RecursiveAnswers from '../components/elements/RecursiveAnswers.vue'
import AnswersWithReplies from '../components/elements/AnswersWithReplies.vue'
import { defineEmits } from 'vue'
import { PenLine } from 'lucide-vue-next'

const route = useRoute();
const router = useRouter();
const { toast } = useToast();
const authStore = useAuthStore();
const authToken = computed(() => authStore.token);
const showAnswerForm = ref(false);

const question = ref(null);
const answers = ref([]);
const newAnswer = ref('');
const loading = ref(true);
const showFullContent = ref(false);

const questionBreadcrumbs = [
    { name: 'Questions', href: '/qna' },
    { name: route.params.slug, href: '/qna/' + route.params.slug }
];

const truncatedContent = computed(() => {
    if (!question.value) return '';
    const maxLength = 300;
    return question.value.content.length <= maxLength || showFullContent.value
        ? question.value.content
        : question.value.content.slice(0, maxLength) + '...';
});

const isContentTruncated = computed(() => question.value && question.value.content.length > 300);

onMounted(async () => {
    try {
        await fetchQuestionData();
    } catch (error) {
        toast({
            title: 'Error',
            description: error?.response?.data?.message || 'Failed to fetch question details.',
            variant: 'destructive'
        });
    } finally {
        loading.value = false;
    }
});

const fetchQuestionData = async () => {
    const response = await axios.get(`/api/v1/questions/${route.params.slug}`);
    question.value = response.data.data.question;
    answers.value = response.data.data.answers;
}

const submitAnswer = async () => {
    try {
        const response = await axios.post(
            `/api/v1/questions/${question.value.id}/answers`,
            { content: newAnswer.value },
            { headers: { Authorization: `Bearer ${authToken.value}` } }
        );
        answers.value.push(response.data.data);
        newAnswer.value = '';
        showAnswerForm.value = false
        toast({ title: 'Answer submitted', description: 'Your answer has been posted.' });
    } catch (error) {
        toast({
            title: 'Error',
            description: error?.response?.data?.message || 'Failed to submit answer.',
            variant: 'destructive'
        });
    }
};

const upvoteQuestion = async () => {
    if (!authToken.value) {
        return toast({ title: 'Authentication required', description: 'Log in to upvote.', variant: 'warning' });
    }
    try {
        await axios.post(
            `/api/v1/questions/${question.value.id}/upvote`,
            {},
            { headers: { Authorization: `Bearer ${authToken.value}` } }
        );
        question.value.upvotes_count++;
        toast({ title: 'Upvoted', description: 'You upvoted this question.' });
    } catch (error) {
        toast({
            title: 'Error',
            description: error?.response?.data?.message || 'Failed to upvote question.',
            variant: 'destructive'
        });
    }
};

const handleReply = async ({ parentId, content }) => {
    if (!authToken.value) {
        return toast({ title: 'Authentication required', description: 'Log in to reply.', variant: 'warning' });
    }
    try {
        const response = await axios.post(
            `/api/v1/answers/${parentId}/replies`,
            { content },
            { headers: { Authorization: `Bearer ${authToken.value}` } }
        );
        toast({ title: 'Reply submitted', description: 'Your reply has been posted.' });
        await fetchQuestionData();
    } catch (error) {
        toast({
            title: 'Error',
            description: error?.response?.data?.message || 'Failed to submit reply.',
            variant: 'destructive'
        });
    }
};

const handleUpvote = async (answerId) => {
    if (!authToken.value) {
        return toast({ title: 'Authentication required', description: 'Log in to upvote.', variant: 'warning' });
    }
    try {
        await axios.post(
            `/api/v1/answers/${answerId}/upvote`,
            {},
            { headers: { Authorization: `Bearer ${authToken.value}` } }
        );
        const answer = answers.value.find(a => a.id === answerId);
        if (answer) answer.upvotes_count++;
        toast({ title: 'Upvoted', description: 'You upvoted this answer.' });
    } catch (error) {
        toast({
            title: 'Error',
            description: error?.response?.data?.message || 'Failed to upvote answer.',
            variant: 'destructive'
        });
    }
};
</script>
