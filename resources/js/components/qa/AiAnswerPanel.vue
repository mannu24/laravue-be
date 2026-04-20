<script setup>
import { onMounted } from 'vue'
import { useAiAnswer } from '../../composables/useAiAnswer'
import { Sparkles, ThumbsUp, ThumbsDown, Loader2 } from 'lucide-vue-next'
import { Button } from '../ui/button'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '../ui/card'

const props = defineProps({
    questionId: {
        type: [Number, String],
        required: true
    }
})

const { aiAnswer, isStreaming, error, streamAiAnswer, validateAiAnswer } = useAiAnswer()

onMounted(() => {
    streamAiAnswer(props.questionId)
})

const handleFeedback = (helpful) => {
    // Feedback handled via API when AI answer ID is available
}
</script>

<template>
    <Card class="border-2 border-primary/20 bg-primary/5 shadow-inner overflow-hidden transition-all duration-500">
        <CardHeader class="pb-2">
            <div class="flex items-center justify-between">
                <CardTitle class="text-sm font-bold flex items-center gap-2 text-primary">
                    <Sparkles class="w-4 h-4 fill-primary" />
                    AI SUGGESTED ANSWER
                </CardTitle>
                <div v-if="isStreaming" class="flex items-center gap-2 text-xs text-muted-foreground animate-pulse">
                    <Loader2 class="w-3 h-3 animate-spin" />
                    Generating...
                </div>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="error" class="text-destructive text-sm p-3 bg-destructive/10 rounded-lg">
                {{ error }}
            </div>
            <div 
                class="prose prose-sm dark:prose-invert max-w-none prose-pre:bg-black/50 overflow-x-auto"
                v-html="aiAnswer || (isStreaming ? '' : 'Waiting for AI...')"
            ></div>
        </CardContent>
        <CardFooter v-if="!isStreaming && aiAnswer" class="flex justify-between items-center border-t border-primary/10 pt-4 mt-2 bg-primary/5">
            <p class="text-xs text-muted-foreground italic">
                AI can make mistakes. Please verify important code.
            </p>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-muted-foreground">Is this helpful?</span>
                <Button variant="ghost" size="icon" class="h-8 w-8 hover:text-green-500 hover:bg-green-500/10" @click="handleFeedback(true)">
                    <ThumbsUp class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8 hover:text-red-500 hover:bg-red-500/10" @click="handleFeedback(false)">
                    <ThumbsDown class="w-4 h-4" />
                </Button>
            </div>
        </CardFooter>
    </Card>
</template>

<style scoped>
.prose {
    line-height: 1.6;
}
</style>
