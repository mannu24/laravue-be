
<script setup>
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import axios from 'axios'
import { ref } from 'vue'
import MarkDownEditor from '../components/elements/MarkDownEditor.vue'
import { useAuthStore } from '../stores/auth'
import router from '../routes'
// import QuestionPreviewModal from '../components/qna/QuestionPreviewModal.vue'

const isSubmitting = ref(false)
const showPreview = ref(false)
const authStore = useAuthStore() ;

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
        formData.append("tags", JSON.stringify(newQuestion.value.tags))

        newQuestion.value.attachments.forEach((file, index) => {
            formData.append(`attachment_${index}`, file)
        })

        const response = await axios.post('/api/v1/questions', formData, {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
                'Content-Type': 'multipart/form-data'
            }
        })

        router.push(`/qna/${response.data.data.slug}`)
        alert("Your question has been successfully posted.")
    } catch (error) {
        console.error('Error submitting question:', error)
        alert("Failed to submit question. Please try again.")
    } finally {
        isSubmitting.value = false
    }
}

const validateForm = () => {
    if (newQuestion.value.title.length < 15) {
        alert("Title should be at least 15 characters long.")
        return false
    }
    if (newQuestion.value.content.length < 30) {
        alert("Question details should be at least 30 characters long.")
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
<template>
    <div class="max-w-3xl mx-auto py-8 px-4">
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
                        <Label for="content">Question Details (Markdown supported)</Label>
                        <MarkDownEditor v-model="newQuestion.content" id="content"
                            placeholder="Provide more context about your question..." rows="10" class="font-mono" />
                        <p class="text-sm text-muted-foreground">
                            Include all the information someone would need to answer your question. Markdown is
                            supported.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Tags</Label>
                        <TagInput v-model="newQuestion.tags" :max-tags="5" placeholder="Add up to 5 tags" />
                        <p class="text-sm text-muted-foreground">
                            Add up to 5 tags to describe what your question is about.
                        </p>
                    </div>

                    <!-- <div class="space-y-2">
                        <Label>Attachments (optional)</Label>
                        <FileUpload @files-selected="handleFileUpload" />
                        <p class="text-sm text-muted-foreground">
                            You can attach images or code snippets to better illustrate your question.
                        </p>
                    </div> -->
                </form>
            </CardContent>
            <CardFooter class="flex justify-between">
                <!-- <Button variant="outline" @click="previewQuestion">
                    Preview
                </Button> -->
                <Button @click="submitQuestion" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Submitting...' : 'Post Your Question' }}
                </Button>
            </CardFooter>
        </Card>

        <!-- <QuestionPreviewModal v-if="showPreview" :question="newQuestion" @close="showPreview = false" /> -->
    </div>
</template>