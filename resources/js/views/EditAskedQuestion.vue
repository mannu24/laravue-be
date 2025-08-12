<template>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <BackNavigator :items="editQuestionBreadcrumbs" />
        <Card class="overflow-hidden">
            <CardHeader>
                <CardTitle>Edit Question</CardTitle>
                <CardDescription>Update your question details</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitEditedQuestion" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title">Question Title</Label>
                        <Input v-model="question.title" id="title" type="text"
                            placeholder="e.g., How do I center a div in CSS?" required />
                        <p class="text-sm text-muted-foreground">
                            Be specific and imagine you're updating a question for others.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="content">Question Details</Label>
                        <MarkDownEditor v-model="question.content"
                            placeholder="Update the context about your question..." :min-height="300" />
                        <p class="text-sm text-muted-foreground">
                            Include all the information someone would need to understand your updated question.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Tags</Label>
                        <TagInput v-model="question.tags" :max-tags="5" placeholder="Update up to 5 tags" />
                        <p class="text-sm text-muted-foreground">
                            Update up to 5 tags to describe what your question is about.
                        </p>
                    </div>
                </form>
            </CardContent>
            <CardFooter class="flex justify-between">
                <Button variant="outline" @click="previewQuestion">
                    Preview
                </Button>
                <Button @click="submitEditedQuestion" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Updating...' : 'Update Question' }}
                </Button>
            </CardFooter>
        </Card>

        <QuestionPreviewModal v-if="showPreview" :question="question" @close="showPreview = false" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import QuestionPreviewModal from '../components/qna/QuestionPreviewModal.vue'
import TagInput from '../components/elements/TagInput.vue'
import MarkDownEditor from '../components/elements/MarkDownEditor.vue'
import { toast } from '../components/ui/toast'
import { useAuthStore } from '../stores/auth'
import BackNavigator from '../components/elements/BackNavigator.vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)
const showPreview = ref(false)

const editQuestionBreadcrumbs = [
    { name: 'Questions', href: '/qna' },
    { name: 'Edit', href: `/qna/edit/${route.params.slug}` }
]

const question = ref({
    title: '',
    content: '',
    tags: [],
    id: null
})

onMounted(async () => {
    await fetchQuestion()
})

const fetchQuestion = async () => {
    try {
        const response = await axios.get(`/api/v1/questions/${route.params.slug}`, {
            headers: {
                Authorization: `Bearer ${authStore.token}`
            }
        })
        const questionData = response.data.data.question
        question.value = {
            id: questionData.id,
            title: questionData.title,
            content: questionData.content,
            tags: (questionData.tags || []).map(tag => tag.name)
        }
    } catch (error) {
        console.error('Error fetching question:', error)
        toast({
            variant: 'destructive',
            description: 'Failed to load question. Please try again.'
        })
        router.push('/qna')
    }
}

const submitEditedQuestion = async () => {
    if (!validateForm()) return

    try {
        isSubmitting.value = true

        const formData = new FormData()
        formData.append("title", question.value.title)
        formData.append("content", question.value.content)
        formData.append("_method", "PUT")
        question.value.tags.forEach((tag, index) => {
            formData.append("tags[]", tag)
        })

        const response = await axios.post(`/api/v1/questions/${question.value.id}`, formData, {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
                'Content-Type': 'multipart/form-data'
            }
        })

        router.push(`/qna/${response.data.data.slug}`)
        toast({
            description: "Your question has been successfully updated."
        })
    } catch (error) {
        console.error('Error updating question:', error)
        toast({
            variant: 'destructive',
            description: "Failed to update question. Please try again."
        })
    } finally {
        isSubmitting.value = false
    }
}

const validateForm = () => {
    if (question.value.title.length < 15) {
        toast({
            variant: 'destructive',
            description: "Title should be at least 15 characters long."
        })
        return false
    }
    if (question.value.content.length < 30) {
        toast({
            variant: 'destructive',
            description: "Question details should be at least 30 characters long."
        })
        return false
    }
    if (question.value.tags.length < 1) {
        toast({
            variant: 'destructive',
            description: "At least 1 Tag is required!"
        })
        return false
    }
    return true
}

const previewQuestion = () => {
    if (validateForm()) {
        showPreview.value = true
    }
}
</script>