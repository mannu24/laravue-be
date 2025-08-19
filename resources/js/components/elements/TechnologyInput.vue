<template>
  <div class="technology-input relative">
    <!-- Selected Technologies Display -->
    <div class="flex flex-wrap gap-2 mb-3">
      <Badge
        v-for="tech in modelValue"
        :key="tech.id || tech.name"
        variant="secondary"
        class="cursor-pointer hover:bg-red-100 dark:hover:bg-red-900"
        @click="removeTechnology(tech)"
      >
        {{ tech.name || tech }}
        <X class="h-3 w-3 ml-1" />
      </Badge>
    </div>

    <!-- Search Input -->
    <div class="relative">
      <Input
        v-model="searchQuery"
        :placeholder="placeholder"
        @input="handleSearch"
        @focus="showDropdown = true"
        @blur="handleBlur"
        @keydown.enter.prevent="handleEnter"
        @keydown.arrow-down.prevent="navigateDown"
        @keydown.arrow-up.prevent="navigateUp"
        @keydown.escape="hideDropdown"
      />

      <!-- Search Results Dropdown -->
      <div
        v-if="showDropdown && (filteredTechnologies.length > 0 || searchQuery.trim())"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
      >
        <!-- Create New Technology Option -->
        <div
          v-if="searchQuery.trim() && !filteredTechnologies.some(t => t.name.toLowerCase() === searchQuery.toLowerCase())"
          class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600"
          @click="createNewTechnology"
        >
          <div class="flex items-center">
            <Plus class="h-4 w-4 mr-2 text-green-600" />
            <span class="text-green-600 font-medium">Create "{{ searchQuery }}"</span>
          </div>
        </div>

        <!-- Existing Technologies -->
        <div
          v-for="(tech, index) in filteredTechnologies"
          :key="tech.id"
          :class="[
            'px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-green-600 font-medium',
            selectedIndex === index ? 'bg-gray-100 dark:bg-gray-700' : ''
          ]"
          @click="selectTechnology(tech)"
          @mouseenter="selectedIndex = index"
        >
          {{ tech.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { X, Plus } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  placeholder: {
    type: String,
    default: 'Search technologies...'
  },
  availableTechnologies: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')
const showDropdown = ref(false)
const selectedIndex = ref(-1)
const filteredTechnologies = ref([])

// Watch for changes in available technologies
watch(() => props.availableTechnologies, (newTechs) => {
  if (newTechs.length > 0) {
    filteredTechnologies.value = newTechs
  }
}, { immediate: true })

const handleSearch = () => {
  if (!searchQuery.value.trim()) {
    filteredTechnologies.value = props.availableTechnologies
    return
  }

  const query = searchQuery.value.toLowerCase()
  filteredTechnologies.value = props.availableTechnologies.filter(tech =>
    tech.name.toLowerCase().includes(query)
  )
}

const handleBlur = () => {
  // Delay hiding dropdown to allow click events
  setTimeout(() => {
    showDropdown.value = false
    selectedIndex.value = -1
  }, 200)
}

const handleEnter = () => {
  if (selectedIndex.value >= 0 && filteredTechnologies.value[selectedIndex.value]) {
    selectTechnology(filteredTechnologies.value[selectedIndex.value])
  } else if (searchQuery.value.trim()) {
    createNewTechnology()
  }
}

const navigateDown = () => {
  if (selectedIndex.value < filteredTechnologies.value.length - 1) {
    selectedIndex.value++
  }
}

const navigateUp = () => {
  if (selectedIndex.value > 0) {
    selectedIndex.value--
  } else if (selectedIndex.value === 0) {
    selectedIndex.value = -1
  }
}

const hideDropdown = () => {
  showDropdown.value = false
  selectedIndex.value = -1
}

const selectTechnology = (tech) => {
  // Check if technology is already selected
  const isAlreadySelected = props.modelValue.some(selected =>
    selected.id === tech.id || selected.name === tech.name
  )

  if (!isAlreadySelected) {
    emit('update:modelValue', [...props.modelValue, tech])
  }

  searchQuery.value = ''
  showDropdown.value = false
  selectedIndex.value = -1
}

const createNewTechnology = async () => {
  const techName = searchQuery.value.trim()
  if (!techName) return

  try {
    // Create new technology via API
    const response = await axios.post('/api/v1/technologies', {
      name: techName
    })

    const newTech = response.data.data

    // Add to selected technologies
    emit('update:modelValue', [...props.modelValue, newTech])

    // Clear search
    searchQuery.value = ''
    showDropdown.value = false

  } catch (error) {
    console.error('Error creating technology:', error)
    // Fallback: add as string if API fails
    emit('update:modelValue', [...props.modelValue, { name: techName }])
    searchQuery.value = ''
    showDropdown.value = false
  }
}

const removeTechnology = (tech) => {
  emit('update:modelValue', props.modelValue.filter(t =>
    t.id !== tech.id && t.name !== tech.name
  ))
}
</script>

<style scoped>
.technology-input {
  position: relative;
}
</style>
