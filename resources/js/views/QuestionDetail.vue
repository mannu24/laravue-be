<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../components/ui/card'
import { Textarea } from '../components/ui/textarea'
import { useToast } from '../components/ui/toast'
import { Button } from '../components/ui/button'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const { toast } = useToast()
const authStore = useAuthStore();
const authToken = authStore.token
const question = ref(null)
const answers = ref([])
const newAnswer = ref('')
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await axios.get(`/api/v1/questions/${route.params.id}`)
        question.value = response.data.data.question
        answers.value = response.data.data.answers
        loading.value = false
    } catch (error) {
        console.error('Error fetching question:', error)
        loading.value = false
        toast({
            title: "Error",
            description: "Failed to fetch question details. Please try again.",
            variant: "destructive",
        })
    }
})

const submitAnswer = async () => {
    try {
        const response = await axios.post(`/api/v1/questions/${question.value.id}/answers`, {
            content: newAnswer.value
        }, {
            headers: {
                Authorization: `Bearer ${authToken}`
            }
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
            description: "Failed to submit answer. Please try again.",
            variant: "destructive",
        })
    }
}

const upvoteQuestion = async () => {
    try {
        await axios.post(`/api/v1/questions/${question.value.id}/upvote`, {}, {
            headers: {
                Authorization: `Bearer ${authToken}`
            }
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
            description: "Failed to upvote question. Please try again.",
            variant: "destructive",
        })
    }
}
</script>

<template>
    <div v-if="loading" class="text-center">
        <p>Loading question...</p>
    </div>
    <div v-else-if="question">
        <Card class="mb-6">
            <CardHeader>
                <CardTitle>{{ question.title }}</CardTitle>
                <CardDescription>
                    Asked by: {{ question.user ? question.user.name : 'Anonymous' }} |
                    {{ new Date(question.created_at).toLocaleString() }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-html="question.content"></div>
                <Button @click="upvoteQuestion" variant="outline">
                    Upvote ({{ question.upvotes_count }})
                </Button>
            </CardContent>
        </Card>

        <h2 class="text-2xl font-bold mb-4">{{ answers?.length }} Answers</h2>
        <Card v-for="answer in answers" :key="answer.id" class="mb-4">
            <CardContent class="pt-4">
                <div v-html="answer.content"></div>
                <CardDescription class="text-primary mt-2">
                    Answered by: {{ answer.user ? answer.user.name : 'Anonymous' }} |
                    {{ new Date(answer.created_at).toLocaleString() }}
                </CardDescription>
            </CardContent>
        </Card>

        <Card v-if="authToken" class="mt-6">
            <CardHeader>
                <CardTitle>Your Answer</CardTitle>
            </CardHeader>
            <CardContent>
                <Textarea v-model="newAnswer" placeholder="Write your answer here..." />
            </CardContent>
            <CardFooter>
                <Button @click="submitAnswer">Submit Answer</Button>
            </CardFooter>
        </Card>
        <Card v-else class="mt-6">
            <CardContent class="text-center">
                <p>Please log in to submit an answer.</p>
                <Button @click="router.push('/login')" variant="link">Log in</Button>
            </CardContent>
        </Card>
    </div>
    <div v-else class="text-center">
        <p>Question not found.</p>
    </div>
</template>
