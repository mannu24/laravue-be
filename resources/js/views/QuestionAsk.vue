<template>
    <div class="max-w-3xl mx-auto py-8 px-4">
        <BackNavigator :items="askQuestionBreadcrumbs" />

        <div v-if="!isAiQnaEnabled">
            <!-- Legacy Form -->
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
                                placeholder="Provide more context about your question..." :minHeight="500" />
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
        
        <div v-else>
            <!-- ── AI Flow Step 1: Write the question body ─────────────────────────── -->
            <Card v-if="step === 1" class="overflow-hidden">
                <CardHeader>
                    <CardTitle>Ask a Question</CardTitle>
                    <CardDescription>Describe your problem and our AI will generate a title & tags for you.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="content">Question Details</Label>
                        <MarkDownEditor v-model="newQuestion.content"
                            placeholder="Describe your Laravel or Vue.js problem in detail. Include any error messages, code snippets, or what you've already tried..."
                            :minHeight="300" />
                        <p class="text-sm text-muted-foreground">
                            Include all the information someone would need to answer your question (min 30 characters).
                        </p>
                    </div>
                </CardContent>
                <CardFooter class="flex justify-end">
                    <Button @click="generateMeta" :disabled="isGenerating">
                        <Sparkles v-if="!isGenerating" class="w-4 h-4 mr-2" />
                        <Loader2 v-else class="w-4 h-4 mr-2 animate-spin" />
                        {{ isGenerating ? 'AI is thinking...' : 'Generate Title & Tags with AI' }}
                    </Button>
                </CardFooter>
            </Card>

            <!-- ── AI Flow Step 2: Review AI suggestions ──────────────────────────── -->
            <Card v-if="step === 2" class="overflow-hidden">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <Sparkles class="h-4 w-4" />
                        </div>
                        <div>
                            <CardTitle>Review AI Suggestions</CardTitle>
                            <CardDescription>Edit the title or tags before posting.</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-6">

                    <!-- AI notice if no key configured -->
                    <div v-if="aiUnavailable"
                        class="flex items-start gap-3 rounded-lg border border-amber-400/40 bg-amber-400/10 p-3 text-sm text-amber-700 dark:text-amber-300">
                        <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>AI suggestion unavailable (no API key). Please fill in the title and tags manually.</span>
                    </div>

                    <!-- Title -->
                    <div class="space-y-2">
                        <Label for="title">Question Title</Label>
                        <Input id="title" v-model="newQuestion.title"
                            placeholder="e.g., How do I implement authentication in Laravel 11?" />
                        <p class="text-xs text-muted-foreground">Min 15 characters.</p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <Label>Tags</Label>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="tag in newQuestion.tags" :key="tag" type="button"
                                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary transition hover:bg-destructive/10 hover:text-destructive"
                                @click="removeTag(tag)">
                                {{ tag }}
                                <X class="h-3 w-3" />
                            </button>
                            <input v-if="newQuestion.tags.length < 5" v-model="tagInput" @keydown.enter.prevent="addTag"
                                @keydown.comma.prevent="addTag" placeholder="Add tag…"
                                class="min-w-[100px] flex-1 rounded-full border border-dashed border-gray-300 bg-transparent px-3 py-1 text-xs outline-none placeholder:text-gray-400 dark:border-gray-600" />
                        </div>
                        <p class="text-xs text-muted-foreground">Up to 5 tags. Press Enter or comma to add.</p>
                    </div>

                    <!-- Question body preview -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Your question body</p>
                        <div class="prose prose-sm max-w-none dark:prose-invert line-clamp-4" v-html="newQuestion.content">
                        </div>
                        <button type="button" class="mt-2 text-xs text-primary underline-offset-2 hover:underline"
                            @click="step = 1">
                            ← Edit body
                        </button>
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between">
                    <Button variant="outline" @click="step = 1">
                        ← Back
                    </Button>
                    <Button @click="submitQuestion" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Posting...' : 'Post Question' }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import MarkDownEditor from '../components/elements/MarkDownEditor.vue'
import TagInput from '../components/elements/TagInput.vue'
import QuestionPreviewModal from '../components/qna/QuestionPreviewModal.vue'
import { toast } from '../components/ui/toast'
import { useAuthStore } from '../stores/auth'
import { useFeatureFlagsStore } from '../stores/featureFlags'
import BackNavigator from '../components/elements/BackNavigator.vue'
import { Sparkles, Loader2, X, AlertCircle } from 'lucide-vue-next'

const authStore = useAuthStore()
const featureFlags = useFeatureFlagsStore()
const router = useRouter()

const isAiQnaEnabled = computed(() => featureFlags.isAiQnaEnabled)

// ── state ──────────────────────────────────────────────────────────────────
const step = ref(1)             // 1 = write body, 2 = review suggestions
const isGenerating = ref(false)
const isSubmitting = ref(false)
const showPreview = ref(false)
const aiUnavailable = ref(false)
const tagInput = ref('')

const newQuestion = ref({
    title: '',
    content: '',
    tags: [],
})

const askQuestionBreadcrumbs = [
    { name: 'Questions', href: '/qna' },
    { name: 'Ask', href: '/qna/ask' },
]

// ── Step 1 → generate meta ─────────────────────────────────────────────────
const generateMeta = async () => {
    if (newQuestion.value.content.trim().length < 30) {
        toast({ variant: 'destructive', description: 'Please write at least 30 characters before generating.' })
        return
    }

    isGenerating.value = true
    try {
        const { data } = await axios.post('/api/v1/questions/ai-suggest-meta', {
            content: newQuestion.value.content,
        }, { headers: { Authorization: `Bearer ${authStore.token}` } })

        aiUnavailable.value = !!data.ai_unavailable
        newQuestion.value.title = data.title || ''
        newQuestion.value.tags = data.tags || []
        step.value = 2
    } catch (err) {
        toast({ variant: 'destructive', description: 'Failed to generate suggestions. Please try again.' })
    } finally {
        isGenerating.value = false
    }
}

// ── Tag helpers ─────────────────────────────────────────────────────────────
const addTag = () => {
    const tag = tagInput.value.trim().toLowerCase().replace(/,+$/, '')
    if (tag && !newQuestion.value.tags.includes(tag) && newQuestion.value.tags.length < 5) {
        newQuestion.value.tags.push(tag)
    }
    tagInput.value = ''
}

const removeTag = (tag) => {
    newQuestion.value.tags = newQuestion.value.tags.filter(t => t !== tag)
}

// ── Submit ─────────────────────────────────────────────────────────
const submitQuestion = async () => {
    if (newQuestion.value.title.trim().length < 15) {
        toast({ variant: 'destructive', description: 'Title must be at least 15 characters.' })
        return
    }
    if (newQuestion.value.content.trim().length < 30) {
        toast({ variant: 'destructive', description: 'Question details should be at least 30 characters long.' })
        return
    }
    if (newQuestion.value.tags.length < 1) {
        toast({ variant: 'destructive', description: 'At least 1 tag is required.' })
        return
    }

    isSubmitting.value = true
    try {
        const formData = new FormData()
        formData.append('title', newQuestion.value.title)
        formData.append('content', newQuestion.value.content)
        newQuestion.value.tags.forEach(tag => formData.append('tags[]', tag))

        const { data } = await axios.post('/api/v1/questions', formData, {
            headers: {
                Authorization: `Bearer ${authStore.token}`,
                'Content-Type': 'multipart/form-data',
            },
        })

        toast({ description: 'Your question has been successfully posted.' })
        router.push(`/qna/${data.data.slug}`)
    } catch (err) {
        toast({ variant: 'destructive', description: 'Failed to submit question. Please try again.' })
    } finally {
        isSubmitting.value = false
    }
}

const previewQuestion = () => {
    if (newQuestion.value.title.trim().length >= 15 && newQuestion.value.content.trim().length >= 30 && newQuestion.value.tags.length >= 1) {
        showPreview.value = true
    } else {
         toast({ variant: 'destructive', description: 'Please fill all fields before previewing.' })
    }
}
</script>