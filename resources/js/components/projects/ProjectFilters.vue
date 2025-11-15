<script setup>
import { ref, watch, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Search,
  Filter,
  X,
  Sparkles,
  TrendingUp
} from 'lucide-vue-next'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue', 'reset'])

const filters = ref({
  search: props.modelValue.search || '',
  type: props.modelValue.type || 'all',
  category: props.modelValue.category || '',
  difficulty: props.modelValue.difficulty || '',
  industry: props.modelValue.industry || '',
  featured: props.modelValue.featured || false,
  verified: props.modelValue.verified || false,
  sort: props.modelValue.sort || 'recent'
})

const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.value.search) count++
  if (filters.value.type !== 'all') count++
  if (filters.value.category) count++
  if (filters.value.difficulty) count++
  if (filters.value.industry) count++
  if (filters.value.featured) count++
  if (filters.value.verified) count++
  if (filters.value.sort !== 'recent') count++
  return count
})

watch(filters, (newFilters) => {
  emit('update:modelValue', { ...newFilters })
}, { deep: true })

const resetFilters = () => {
  filters.value = {
    search: '',
    type: 'all',
    category: '',
    difficulty: '',
    industry: '',
    featured: false,
    verified: false,
    sort: 'recent'
  }
  emit('reset')
}

const removeFilter = (key) => {
  if (key === 'type') {
    filters.value[key] = 'all'
  } else if (key === 'featured' || key === 'verified') {
    filters.value[key] = false
  } else {
    filters.value[key] = ''
  }
}
</script>

<template>
  <div class="space-y-4">
    <!-- Search & Quick Filters -->
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1 relative">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="filters.search"
          placeholder="Search projects..."
          class="pl-10"
        />
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          @click="filters.featured = !filters.featured"
          :class="filters.featured ? 'bg-purple-50 border-purple-500' : ''"
        >
          <Sparkles class="h-4 w-4 mr-2" />
          Featured
        </Button>
        <Button
          variant="outline"
          size="sm"
          @click="filters.verified = !filters.verified"
          :class="filters.verified ? 'bg-blue-50 border-blue-500' : ''"
        >
          <TrendingUp class="h-4 w-4 mr-2" />
          Verified
        </Button>
      </div>
    </div>

    <!-- Advanced Filters -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <Select v-model="filters.type">
        <SelectTrigger>
          <SelectValue placeholder="Type" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Types</SelectItem>
          <SelectItem value="open">Open Source</SelectItem>
          <SelectItem value="sellable">Premium</SelectItem>
        </SelectContent>
      </Select>

      <Select v-model="filters.difficulty">
        <SelectTrigger>
          <SelectValue placeholder="Difficulty" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="">All Levels</SelectItem>
          <SelectItem value="beginner">Beginner</SelectItem>
          <SelectItem value="intermediate">Intermediate</SelectItem>
          <SelectItem value="advanced">Advanced</SelectItem>
          <SelectItem value="expert">Expert</SelectItem>
        </SelectContent>
      </Select>

      <Input
        v-model="filters.industry"
        placeholder="Industry"
      />

      <Select v-model="filters.sort">
        <SelectTrigger>
          <SelectValue placeholder="Sort" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="recent">Recent</SelectItem>
          <SelectItem value="popular">Popular</SelectItem>
          <SelectItem value="trending">Trending</SelectItem>
          <SelectItem value="rating">Highest Rated</SelectItem>
          <SelectItem value="views">Most Viewed</SelectItem>
        </SelectContent>
      </Select>
    </div>

    <!-- Active Filters -->
    <div v-if="activeFiltersCount > 0" class="flex items-center gap-2 flex-wrap">
      <span class="text-sm text-muted-foreground">Active filters:</span>
      <Badge
        v-if="filters.search"
        variant="secondary"
        class="cursor-pointer"
        @click="removeFilter('search')"
      >
        Search: {{ filters.search }}
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Badge
        v-if="filters.type !== 'all'"
        variant="secondary"
        class="cursor-pointer"
        @click="removeFilter('type')"
      >
        {{ filters.type === 'open' ? 'Open Source' : 'Premium' }}
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Badge
        v-if="filters.difficulty"
        variant="secondary"
        class="cursor-pointer capitalize"
        @click="removeFilter('difficulty')"
      >
        {{ filters.difficulty }}
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Badge
        v-if="filters.industry"
        variant="secondary"
        class="cursor-pointer"
        @click="removeFilter('industry')"
      >
        {{ filters.industry }}
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Badge
        v-if="filters.featured"
        variant="secondary"
        class="cursor-pointer"
        @click="removeFilter('featured')"
      >
        Featured
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Badge
        v-if="filters.verified"
        variant="secondary"
        class="cursor-pointer"
        @click="removeFilter('verified')"
      >
        Verified
        <X class="h-3 w-3 ml-1" />
      </Badge>
      <Button
        variant="ghost"
        size="sm"
        @click="resetFilters"
        class="text-xs"
      >
        Clear All
      </Button>
    </div>
  </div>
</template>

