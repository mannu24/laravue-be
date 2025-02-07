<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../ui/card'
import { useToast } from '../ui/toast'
import { Button } from '../ui/button'
import { Skeleton } from '../ui/skeleton'

const router = useRouter()
const { toast } = useToast()
const questions = ref([])
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/latest-questions')
        questions.value = response.data.data
        loading.value = false
    } catch (error) {
        console.error('Error fetching questions:', error)
        loading.value = false
        toast({
            title: "Error",
            description: "Failed to fetch questions. Please try again.",
            variant: "destructive",
        })
    }
})

const goToQuestion = (id) => {
    router.push({ name: 'question', params: { id } })
}
</script>

<template>
    <div>
        <h1 class="text-3xl font-bold mb-6">Latest Questions</h1>
        <div v-if="loading" class="text-center">
            <div class="grid gap-4">
                <Skeleton v-for="i in 5" class="w-full h-40" />
            </div>
        </div>
        <div v-else class="space-y-4">
            <Card v-for="question in questions" :key="question.id">
                <CardHeader>
                    <CardTitle>
                        <router-link :to="`/qna/${question.id}`" @click.prevent="goToQuestion(question.id)"
                            class="text-primary hover:underline">
                            {{ question.title }}
                        </router-link>
                    </CardTitle>
                    <CardDescription>
                        Asked by: {{ question.user ? question.user.name : 'Anonymous' }} |
                        Upvotes: {{ question.upvotes_count }} |
                        {{ new Date(question.created_at).toLocaleString() }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p>{{ question.content.substring(0, 200) }}...</p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
