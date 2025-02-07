<script setup>
import { ref, inject } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { Button } from '../components/ui/button'
import { Input } from '../components/ui/input'
import { Textarea } from '../components/ui/textarea'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '../components/ui/card'
import { useToast } from '../components/ui/toast'
import { useAuthStore } from '../stores/auth';

const router = useRouter()
const { toast } = useToast()
const authStore = useAuthStore();
const isSubmitting = ref(false);
const newQuestion = ref({
    title: '',
    content: ''
})

const submitQuestion = async () => {
    try {
        isSubmitting.value = true;
        
        const formData = new FormData();
        formData.append("title", newQuestion.value.title);
        formData.append("content", newQuestion.value.content);

        const response = await axios.post('/api/v1/questions', formData, {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
                'Content-Type': 'multipart/form-data'
            }
        })
        router.push(`/qna/${response.data.data.slug}`);
        toast({
            title: "Question submitted",
            description: "Your question has been successfully posted.",
        })
    } catch (error) {
        console.error('Error submitting question:', error)
        toast({
            title: "Error",
            description: "Failed to submit question. Please try again.",
            variant: "destructive",
        })
    }

}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Ask a Question</CardTitle>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submitQuestion" class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium mb-1">Title</label>
                    <Input v-model="newQuestion.title" id="title" placeholder="Enter your question title" required />
                </div>
                <div>
                    <label for="content" class="block text-sm font-medium mb-1">Content</label>
                    <Textarea v-model="newQuestion.content" id="content" placeholder="Describe your question in detail"
                        rows="6" required />
                </div>
            </form>
        </CardContent>
        <CardFooter>
            <Button type="submit" @click="submitQuestion" :disabled="isSubmitting">
                {{ isSubmitting ? 'Submitting...' : 'Submit Question' }}
            </Button>
        </CardFooter>
    </Card>
</template>
