<template>
    <div class="file-upload">
        <Label for="file-upload" class="cursor-pointer">
            <Button variant="outline" as="span">
                Choose files
            </Button>
        </Label>
        <Input id="file-upload" type="file" multiple @change="handleFileChange" class="hidden" />
        <ul v-if="files.length" class="mt-2 space-y-1">
            <li v-for="file in files" :key="file.name" class="text-sm text-muted-foreground">
                {{ file.name }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const emit = defineEmits(['files-selected'])

const files = ref([])

const handleFileChange = (event) => {
    files.value = Array.from(event.target.files)
    emit('files-selected', files.value)
}
</script>