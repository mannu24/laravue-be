<template>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-slate-950">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Preview Your Question</h3>
                <div class="mt-2 px-7 py-4 border-[1px] rounded-md">
                    <h2 class="text-xl font-semibold mb-2">{{ question.title }}</h2>
                    <div class="prose max-w-none text-left" v-html="markdownToHtml(question.content)"></div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span v-for="tag in question.tags" :key="tag"
                            class="bg-indigo-100 text-indigo-800 text-sm font-medium px-2 py-1 rounded">
                            {{ tag }}
                        </span>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <Button @click="$emit('close')"
                    class="w-full"
                    >
                        Close Preview
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import { Button } from '../ui/button'

const props = defineProps({
    question: Object
})

defineEmits(['close'])

const markdownToHtml = (markdown) => {
    return DOMPurify.sanitize(marked(markdown))
}
</script>