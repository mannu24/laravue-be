<script setup>
import { computed } from 'vue'
import { useThemeStore } from '../../stores/theme'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Calendar, Download, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
  version: {
    type: Object,
    required: true
  }
})

const themeStore = useThemeStore()

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}
</script>

<template>
  <Card :class="[
    'border-0 shadow-sm',
    themeStore.isDark ? 'bg-gray-800/50' : 'bg-white'
  ]">
    <CardContent class="p-4">
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2">
          <h4 class="font-semibold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
            v{{ version.version_number }}
          </h4>
          <Badge v-if="version.is_stable" class="bg-green-500 text-white text-xs">
            <CheckCircle class="h-3 w-3 mr-1" />
            Stable
          </Badge>
        </div>
        <div class="flex items-center gap-1 text-xs text-muted-foreground">
          <Calendar class="h-3 w-3" />
          <span>{{ formatDate(version.release_date) }}</span>
        </div>
      </div>

      <p v-if="version.changelog" class="text-sm mb-3 leading-relaxed"
        :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-600'">
        {{ version.changelog }}
      </p>

      <a
        v-if="version.download_url"
        :href="version.download_url"
        target="_blank"
        class="inline-flex items-center gap-2 text-sm text-blue-500 hover:text-blue-600 transition-colors"
      >
        <Download class="h-4 w-4" />
        Download
      </a>
    </CardContent>
  </Card>
</template>

