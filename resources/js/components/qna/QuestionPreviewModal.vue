<template>
    <Dialog :open="true" @close="$emit('close')">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Preview Your Question</DialogTitle>
            </DialogHeader>
            <div class="mt-2 space-y-4">
                <h2 class="text-xl font-semibold">{{ question.title }}</h2>
                <div class="prose dark:prose-invert max-w-none" v-html="markdownToHtml(question.content)"></div>
                <div class="flex flex-wrap gap-2">
                    <Badge v-for="tag in question.tags" :key="tag" variant="secondary">
                        {{ tag }}
                    </Badge>
                </div>
            </div>
            <DialogFooter>
                <Button @click="$emit('close')">Close Preview</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const props = defineProps({
    question: Object
})

defineEmits(['close'])

const markdownToHtml = (markdown) => {
    return DOMPurify.sanitize(marked(markdown))
}
</script>