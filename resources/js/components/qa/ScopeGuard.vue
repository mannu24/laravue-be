<script setup>
import { ref } from 'vue'
import { AlertCircle, ArrowRight } from 'lucide-vue-next'
import { Button } from '../ui/button'

const props = defineProps({
    content: String
})

const emit = defineEmits(['proceed'])

const isChecking = ref(false)
const outOfScope = ref(false)
const suggestion = ref('')

const checkScope = async () => {
    isChecking.value = true
    try {
        // Mocking an AI scope check call
        // In reality, this would call /api/v1/questions/ai-analyze
        const response = await fetch('/api/v1/questions/ai-analyze', {
            method: 'POST',
            body: JSON.stringify({ content: props.content }),
            headers: { 'Content-Type': 'application/json' }
        })
        const data = await response.json()

        if (data.out_of_scope) {
            outOfScope.value = true
            suggestion.value = data.suggestion
        } else {
            emit('proceed')
        }
    } catch (e) {
        // Fallback to proceed if check fails
        emit('proceed')
    } finally {
        isChecking.value = false
    }
}
</script>

<template>
    <div v-if="outOfScope" class="space-y-4">
        <div
            class="relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 border-orange-500 bg-orange-500/10 text-orange-600 dark:text-orange-400">
            <AlertCircle class="h-4 w-4" />
            <h5 class="mb-1 font-medium leading-none tracking-tight">Wait! This seems out of scope.</h5>
            <div class="text-sm [&_p]:leading-relaxed">
                Our AI Assistant specializes in **Laravel** and **Vue.js**.
                {{ suggestion || 'Your question might be better suited for a general programming forum.' }}
            </div>
        </div>
        <div class="flex gap-3">
            <Button variant="outline" @click="outOfScope = false">Edit Question</Button>
            <Button variant="default" @click="emit('proceed')">Post Anyway
                <ArrowRight class="ml-2 w-4 h-4" />
            </Button>
        </div>
    </div>
</template>
