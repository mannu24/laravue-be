<template>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <BackNavigator :items="askQuestionBreadcrumbs" />
        <Card class="overflow-hidden">
            <CardHeader>
                <CardTitle>Ask a Question</CardTitle>
                <CardDescription>Get help from our community of experts</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submitQuestion" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title">Question Title</Label>
                        <Input v-model="newQuestion.title" id="title" type="text"
                            placeholder="e.g., How do I center a div in CSS?" required />
                        <p class="text-sm text-muted-foreground">
                            Be specific and imagine you're asking a question to another person.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="content">Question Details</Label>
                        <MarkDownEditor v-model="newQuestion.content"
                            placeholder="Provide more context about your question..." :min-height="300" />
                        <p class="text-sm text-muted-foreground">
                            Include all the information someone would need to answer your question.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Tags</Label>
                        <TagInput v-model="newQuestion.tags" :max-tags="5" placeholder="Add up to 5 tags" />
                        <p class="text-sm text-muted-foreground">
                            Add up to 5 tags to describe what your question is about.
                        </p>
                    </div>
                </form>
            </CardContent>
            <CardFooter class="flex justify-between">
                <Button variant="outline" @click="previewQuestion">
                    Preview
                </Button>
                <Button @click="submitQuestion" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Submitting...' : 'Post Your Question' }}
                </Button>
            </CardFooter>
        </Card>

        <QuestionPreviewModal v-if="showPreview" :question="newQuestion" @close="showPreview = false" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
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

const authStore = useAuthStore();
const router = useRouter()
const isSubmitting = ref(false)
const showPreview = ref(false)

const askQuestionBreadcrumbs = [
    { name: 'Questions', href: '/qna' },
    { name: 'Ask', href: '/qna/ask' }
]

const newQuestion = ref({
    title: '',
    content: '',
    tags: [],
    attachments: []
})

const handleFileUpload = (files) => {
    newQuestion.value.attachments = files
}

const submitQuestion = async () => {
    if (!validateForm()) return

    try {
        isSubmitting.value = true

        const formData = new FormData()
        formData.append("title", newQuestion.value.title)
        formData.append("content", newQuestion.value.content)
        newQuestion.value.tags.forEach((tag, index) => {
            formData.append("tags[]", tag)
        });

        // newQuestion.value.attachments.forEach((file, index) => {
        //     formData.append(`attachment_${index}`, file)
        // })

        const response = await axios.post('/api/v1/questions', formData, {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
                'Content-Type': 'multipart/form-data'
            }
        })

        router.push(`/qna/${response.data.data.slug}`)
        toast({
            description: "Your question has been successfully posted."
        });
    } catch (error) {
        console.error('Error submitting question:', error)
        toast({
            variant: 'destructive',
            description: "Failed to submit question. Please try again."
        });
    } finally {
        isSubmitting.value = false
    }
}

const validateForm = () => {
    if (newQuestion.value.title.length < 15) {
        toast({
            variant: 'destructive',
            description: "Title should be at least 15 characters long."
        });
        return false
    }
    if (newQuestion.value.content.length < 30) {
        toast({
            variant: 'destructive',
            description: "Question details should be at least 30 characters long."
        })
        return false
    }
    if (newQuestion.value.tags.length < 1) {
        toast({
            variant: 'destructive',
            description: "Atleast 1 Tag is required!"
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