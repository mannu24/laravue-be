<template>
    <div class="tag-input">
        <div class="flex flex-wrap gap-2 mb-2">
            <Badge v-for="tag in modelValue" :key="tag" variant="secondary" class="text-sm">
                {{ tag }}
                <Button variant="ghost" size="sm" class="h-4 w-4 p-0 ml-1" @click="removeTag(tag)">
                    <XIcon class="h-3 w-3" />
                </Button>
            </Badge>
        </div>
        <Input v-model="newTag" @keydown.enter.prevent="addTag" @keydown.tab.prevent="addTag"
            @keydown.188.prevent="addTag" :placeholder="placeholder" />
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { XIcon } from 'lucide-vue-next'

const props = defineProps({
    modelValue: Array,
    maxTags: {
        type: Number,
        default: 5
    },
    placeholder: String
})

const emit = defineEmits(['update:modelValue'])

const newTag = ref('')

const addTag = () => {
    const tag = newTag.value.trim().toLowerCase()
    if (tag && !props.modelValue.includes(tag) && props.modelValue.length < props.maxTags) {
        emit('update:modelValue', [...props.modelValue, tag])
        newTag.value = ''
    }
}

const removeTag = (tagToRemove) => {
    emit('update:modelValue', props.modelValue.filter(tag => tag !== tagToRemove))
}
</script>