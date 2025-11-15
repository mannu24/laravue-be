<script setup>
import { ref, onMounted, watch } from 'vue'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: [Number, String, null],
    default: null
  },
  placeholder: {
    type: String,
    default: 'Select category'
  }
})

const emit = defineEmits(['update:modelValue'])

const categories = ref([])
const loading = ref(false)
const selectedCategory = ref(props.modelValue)

const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/v1/projects/categories')
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching categories:', error)
  } finally {
    loading.value = false
  }
}

const handleChange = (value) => {
  selectedCategory.value = value ? parseInt(value) : null
  emit('update:modelValue', selectedCategory.value)
}

watch(() => props.modelValue, (newValue) => {
  selectedCategory.value = newValue
})

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <div class="space-y-2">
    <Label>Category</Label>
    <Select 
      :model-value="selectedCategory?.toString()" 
      @update:model-value="handleChange"
      :disabled="loading"
    >
      <SelectTrigger>
        <SelectValue :placeholder="loading ? 'Loading...' : placeholder" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem value="">None</SelectItem>
        <SelectItem 
          v-for="category in categories" 
          :key="category.id" 
          :value="category.id.toString()"
        >
          {{ category.name }}
        </SelectItem>
      </SelectContent>
    </Select>
  </div>
</template>

