<template>
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div v-if="loading" class="text-center">
            <Spinner class="h-8 w-8 text-primary" />
            <p class="mt-2 text-gray-600">Loading question...</p>
        </div>

        <div v-else-if="question" class="space-y-6">
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
                        <span>{{ formatDate(question.created_at) }}</span>
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

            <div ref="answersSection">
                <h2 class="text-xl font-bold mb-4">{{ answers.length }} Answers</h2>
                <Card v-for="answer in answers" :key="answer.id" class="mb-4">
                    <CardContent class="pt-4">
                        <div class="flex space-x-4">
                            <Avatar>
                                <AvatarImage :src="answer.user?.avatar_url" />
                                <AvatarFallback>{{ getInitials(answer.user?.name) }}</AvatarFallback>
                            </Avatar>
                            <div class="flex-1">
                                <div v-html="answer.content" class="prose max-w-none"></div>
                                <div class="flex justify-between items-center mt-4">
                                    <CardDescription class="text-sm text-gray-500">
                                        Answered by {{ answer.user ? answer.user.name : 'Anonymous' }} • {{
                                            formatDate(answer.created_at) }}
                                    </CardDescription>
                                    <Button variant="ghost" size="sm">Share</Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="authToken">
                <CardHeader>
                    <CardTitle>Your Answer</CardTitle>
                </CardHeader>
                <CardContent>
                    <Textarea v-model="newAnswer" placeholder="Write your answer here..." class="min-h-[150px]" />
                </CardContent>
                <CardFooter class="flex justify-end">
                    <Button @click="submitAnswer">Post Your Answer</Button>
                </CardFooter>
            </Card>

            <Card v-else>
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

const route = useRoute()
const router = useRouter()
const { toast } = useToast()
const authStore = useAuthStore()
const authToken = computed(() => authStore.token)

const question = ref(null)
const answers = ref([])
const newAnswer = ref('')
const loading = ref(true)
const showFullContent = ref(false)
const answersSection = ref(null)

const truncatedContent = computed(() => {
    if (!question.value) return ''
    const maxLength = 300
    if (question.value.content.length <= maxLength || showFullContent.value) {
        return question.value.content
    }
    return question.value.content.slice(0, maxLength) + '...'
})

const isContentTruncated = computed(() => {
    return question.value && question.value.content.length > 300
})

onMounted(async () => {
    try {
        const response = await axios.get(`/api/v1/questions/${route.params.slug}`)
        question.value = response.data.data.question
        answers.value = response.data.data.answers
        loading.value = false
    } catch (error) {
        console.error('Error fetching question:', error)
        loading.value = false
        toast({
            title: "Error",
            description: error?.response?.data?.message ??"Failed to fetch question details. Please try again.",
            variant: "destructive",
        })
    }
})

const submitAnswer = async () => {
    try {
        const response = await axios.post(`/api/v1/questions/${question.value.id}/answers`, {
            content: newAnswer.value
        }, {
            headers: { Authorization: `Bearer ${authToken.value}` }
        })
        answers.value.push(response.data.data)
        newAnswer.value = ''
        toast({
            title: "Answer submitted",
            description: "Your answer has been successfully posted.",
        })
    } catch (error) {
        console.error('Error submitting answer:', error)
        toast({
            title: "Error",
            description: error?.response?.data?.message ?? "Failed to submit answer. Please try again.",
            variant: "destructive",
        })
    }
}

const upvoteQuestion = async () => {
    if (!authToken.value) {
        toast({
            title: "Authentication required",
            description: "Please log in to upvote questions.",
            variant: "warning",
        })
        return
    }
    try {
        await axios.post(`/api/v1/questions/${question.value.id}/upvote`, {}, {
            headers: { Authorization: `Bearer ${authToken.value}` }
        })
        question.value.upvotes_count++
        toast({
            title: "Upvoted",
            description: "You have successfully upvoted this question.",
        })
    } catch (error) {
        console.error('Error upvoting question:', error)
        toast({
            title: "Error",
            description: error?.response?.data?.message ?? "Failed to upvote question. Please try again.",
            variant: "destructive",
        })
    }
}

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }
    return new Date(dateString).toLocaleDateString('en-US', options)
}

const getInitials = (name) => {
    return name ? name.split(' ').map(n => n[0]).join('').toUpperCase() : '?'
}
</script>