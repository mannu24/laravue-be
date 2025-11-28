<script setup>
import { useRouter } from 'vue-router'
import { useThemeStore } from '../../stores/theme'
import { Card, CardContent } from '../../components/ui/card'
import PostCard from '../feed/PostCard.vue'
import QuestionCard from '../qa/QuestionCard.vue'
import { Bookmark, FileText, MessageSquare, FolderOpen } from 'lucide-vue-next'

const props = defineProps({
  bookmark: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['bookmark-removed', 'bookmarked-action'])

const router = useRouter()
const themeStore = useThemeStore()

const getBookmarkIcon = (type) => {
  switch (type) {
    case 'post':
      return FileText
    case 'question':
      return MessageSquare
    case 'project':
      return FolderOpen
    default:
      return Bookmark
  }
}

const getBookmarkUrl = (bookmark) => {
  const record = bookmark.record
  if (!record) return '#'
  
  if (bookmark.type === 'post') {
    return `/@${record.user?.username || 'unknown'}/${record.post_code || record.id}`
  } else if (bookmark.type === 'question') {
    return `/qna/${record.slug || record.id}`
  } else if (bookmark.type === 'project') {
    return `/projects/${record.slug || record.id}`
  }
  return '#'
}

const handleBookmarkedAction = (data) => {
  emit('bookmarked-action', data)
}

const handleCardClick = () => {
  router.push(getBookmarkUrl(props.bookmark))
}
</script>

<template>
  <Card
    :class="[
      'transition-all duration-200 hover:shadow-lg',
      themeStore.isDark 
        ? 'bg-gray-900 border-gray-800 hover:border-gray-700' 
        : 'bg-white border-gray-200 hover:border-gray-300'
    ]"
  >
    <CardContent class="p-4">
      <!-- Bookmark Header -->
      <div class="flex items-center gap-3 mb-3 pb-3 border-b" :class="[
        themeStore.isDark ? 'border-gray-800' : 'border-gray-200'
      ]">
        <div class="p-2 rounded-lg" :class="[
          bookmark.type === 'post' 
            ? (themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600') :
          bookmark.type === 'question' 
            ? (themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600') :
            (themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600')
        ]">
          <component :is="getBookmarkIcon(bookmark.type)" class="h-4 w-4" />
        </div>
        <div>
          <div class="text-sm font-medium capitalize" :class="[
            themeStore.isDark ? 'text-gray-300' : 'text-gray-700'
          ]">
            {{ bookmark.type }}
          </div>
          <div class="text-xs" :class="[
            themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
          ]">
            Saved {{ new Date(bookmark.bookmarked_at).toLocaleDateString() }}
          </div>
        </div>
      </div>

      <!-- Bookmark Content -->
      <div v-if="bookmark.type === 'post' && bookmark.record" @click="handleCardClick" class="cursor-pointer">
        <PostCard 
          :post="{
            ...bookmark.record,
            user: bookmark.record.user,
            post_code: bookmark.record.post_code || bookmark.record.id,
            bookmarked: true
          }"
          @bookmarked_action="handleBookmarkedAction"
        />
      </div>
      
      <div v-else-if="bookmark.type === 'question' && bookmark.record" @click="handleCardClick" class="cursor-pointer">
        <QuestionCard 
          :question="{
            ...bookmark.record,
            user: bookmark.record.user,
            slug: bookmark.record.slug || bookmark.record.id,
            bookmarked: true
          }"
          :navigate-on-click="false"
          @bookmarked_action="handleBookmarkedAction"
        />
      </div>
      
      <div 
        v-else-if="bookmark.type === 'project' && bookmark.record" 
        @click="handleCardClick"
        class="cursor-pointer p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
      >
        <h3 class="text-lg font-semibold mb-2" :class="[
          themeStore.isDark ? 'text-white' : 'text-gray-900'
        ]">
          {{ bookmark.record.title }}
        </h3>
        <p v-if="bookmark.record.description" class="text-sm line-clamp-3" :class="[
          themeStore.isDark ? 'text-gray-400' : 'text-gray-600'
        ]">
          {{ bookmark.record.description }}
        </p>
        <div v-if="bookmark.record.user" class="flex items-center gap-2 mt-3 text-sm" :class="[
          themeStore.isDark ? 'text-gray-500' : 'text-gray-400'
        ]">
          <img 
            v-if="bookmark.record.user.profile_photo" 
            :src="bookmark.record.user.profile_photo" 
            :alt="bookmark.record.user.name"
            class="w-5 h-5 rounded-full"
          />
          <span>by @{{ bookmark.record.user.username }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

