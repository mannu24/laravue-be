<template>
    <QuillEditor v-model:content="content" contentType="html" :options="editorOptions" :min-height="minHeight" />
</template>

<script setup>
import { ref, watch, defineProps, defineEmits, computed } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const props = defineProps({
    modelValue: String,
    minHeight: {
        type: Number,
        default: 300
    },
    placeholder: String
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)

watch(() => props.modelValue, (newValue) => {
    content.value = newValue
})

watch(content, (newContent) => {
    emit('update:modelValue', newContent)
}, { immediate: true })

const editorOptions = computed(() => ({
    theme: 'snow',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean'],
            ['link']
        ],
    },
    placeholder: props.placeholder
}))
</script>