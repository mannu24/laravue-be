<script setup>
import { computed, onMounted, ref, toRef, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger
} from '@/components/ui/alert-dialog'
import { useAuthStore } from '@/stores/auth'
import { useBookmark } from '@/composables/useBookmark'
import { useToast } from '../ui/toast'
import { LucideEllipsisVertical, Bookmark, MessageSquare, CheckCircle2, Tag as TagIcon, CircleChevronUp } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  navigateOnClick: {
    type: Boolean,
    default: true
  },
  showDescription: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits([
  'load_more',
  'fetch',
  'delete_question',
  'liked_action',
  'share_url',
  'bookmarked_action',
  'open'
])

const question = toRef(props, 'question')
const router = useRouter()
const authStore = useAuthStore()
const { toast } = useToast()
const element = ref(null)
const showDropdown = ref(false)
const isDeleting = ref(false)

const questionUrl = computed(() => `/qna/${question.value?.slug || question.value?.id}`)

const tagList = computed(() => {
  const tags = question.value?.tags || question.value?.topics || question.value?.labels
  if (!tags) return []
  if (Array.isArray(tags)) return tags
  if (typeof tags === 'string') {
    return tags.split(',').map((tag) => tag.trim()).filter(Boolean)
  }
  return []
})

const hasVerifiedAnswer = computed(() => {
  return question.value?.is_verified || false
})

const excerpt = computed(() => {
  return (
    question.value?.body ||
    question.value?.content ||
    question.value?.ai_generated_summary ||
    'No description'
  )
})

const postedAt = computed(() => formatRelativeDate(question.value?.created_at || question.value?.posted_at))

const { isBookmarked, bookmarkCount, isLoading: isBookmarkLoading, toggleBookmark, initialize } = useBookmark(
  question.value,
  'question'
)

watch(
  question,
  (newQuestion) => {
    if (newQuestion) {
      initialize(newQuestion)
    }
  },
  { immediate: true }
)

const handleCardClick = () => {
  emit('open', question.value?.id || question.value?.slug)
  if (props.navigateOnClick) {
    router.push(questionUrl.value)
  }
}

const handleComment = (event) => {
  event?.stopPropagation()
  if (!authStore.isAuthenticated) {
    router.push('/login')
  } else {
    router.push(questionUrl.value)
  }
}

const handleShare = (event) => {
  event?.stopPropagation()
  emit('share_url', questionUrl.value)
}

const handleBookmark = async (event) => {
  event?.stopPropagation()
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  const recordId = question.value?.slug || question.value?.id
  if (!recordId) return
  const result = await toggleBookmark('question', recordId)
  emit('bookmarked_action', [recordId, result, bookmarkCount.value])
}

const handleUpvote = async (event) => {
  event?.stopPropagation()
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  try {
    const response = await axios.post(`/api/v1/questions/${question.value.id}/toggle-upvote`, {}, authStore.config)
    question.value.upvoted = response.data.data.upvoted
    question.value.upvotes_count = response.data.data.upvotes_count
  } catch (error) {
    toast({
      title: 'Error',
      description: error.response?.data?.message || 'Something went wrong',
      variant: 'destructive'
    })
  }
}

const handleDelete = async () => {
  if (isDeleting.value) return
  isDeleting.value = true
  try {
    showDropdown.value = false
    await axios.delete(`/api/v1/questions/${question.value.slug}`, authStore.config)
    emit('delete_question', question.value.slug)
  } finally {
    isDeleting.value = false
  }
}

const errorFallbackUserImage = (event) => {
  event.target.src = '/assets/front/images/user.png'
}

const checkItem = () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        emit('load_more')
      }
    })
  })
  if (element.value) observer.observe(element.value)
}

onMounted(() => {
  if (element.value?.classList.contains('last_item')) {
    checkItem()
  }
})

const formatRelativeDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (hours < 1) return 'Just now'
  if (hours < 24) return `${hours}h ago`
  if (days === 1) return 'Yesterday'
  if (days < 7) return `${days} days ago`
  if (days < 30) return `${Math.floor(days / 7)} weeks ago`
  if (days < 365) return `${Math.floor(days / 30)} months ago`
  return `${Math.floor(days / 365)} years ago`
}
const upvoteCount = computed(() => {
  return (
    question.value?.upvotes_count ?? 0
  )
})

const isUpvoted = computed(() => {
  if (question.value?.upvoted !== undefined) return question.value.upvoted
  return false
})
</script>

<template>
  <div ref="element" class="question-card-wrapper">
    <article
      class="group bg-card border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
      @click="handleCardClick"
      data-test="question-card"
    >
      <div class="flex flex-col gap-4">
        <div v-if="tagList.length || hasVerifiedAnswer" class="flex flex-wrap items-center gap-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
          <span
            v-for="tag in tagList"
            :key="`tag-${typeof tag === 'string' ? tag : tag.id || tag.name}`"
            class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300"
          >
            <TagIcon class="h-3.5 w-3.5" />
            {{ typeof tag === 'string' ? tag : tag.name }}
          </span>
          <span
            v-if="hasVerifiedAnswer"
            class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
          >
            <CheckCircle2 class="h-3.5 w-3.5" />
            Solved
          </span>
        </div>
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <h3 class="text-lg cursor-pointer group-hover:text-laravel lg:text-xl font-semibold text-gray-900 dark:text-white leading-tight mb-2 line-clamp-2">
              {{ question.title }}
            </h3>
            <p v-if="showDescription" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3" v-html="excerpt" />
          </div>

          <div
            v-if="authStore.isAuthenticated && question.owner"
            class="relative flex-shrink-0"
            @click.stop
          >
            <button
              @click="showDropdown = !showDropdown"
              class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-500 dark:text-gray-400"
            >
              <LucideEllipsisVertical class="w-5 h-5" />
            </button>
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="showDropdown"
                class="absolute right-0 top-full mt-2 z-20 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden"
              >
                <button
                  class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-300"
                  @click.stop="$router.push('/qna/ask/' + question.slug); showDropdown = false"
                >
                  <i class="fas fa-pencil-alt text-xs"></i>
                  Edit Question
                </button>
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <button
                      class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400"
                    >
                      <i class="fas fa-trash text-xs"></i>
                      Delete
                    </button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Delete question?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This action cannot be undone. This will permanently delete your question.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction @click="handleDelete">
                        Delete
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </transition>
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
          <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
              <img
                v-if="question.user?.profile_photo"
                :src="question.user.profile_photo"
                :alt="question.user?.name"
                class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                @error="errorFallbackUserImage"
              />
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                  {{ question.user?.name || 'Anonymous' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ postedAt }}</p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
            <span>{{ question.views_count || question.views || 0 }} views &bull; {{ upvoteCount }} upvotes</span>
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center">
            <button
              class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-colors"
              :class="isUpvoted ? 'bg-red-600/10 text-red-600 dark:bg-red-600/20' : 'bg-card text-gray-600 dark:text-gray-300'"
              @click.stop="handleUpvote"
            >
              <CircleChevronUp class="w-4 h-4" />
              <span>{{ upvoteCount }}</span>
            </button>
            <button
              class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-card text-gray-600 dark:text-gray-300"
              @click.stop="handleComment"
            >
              <MessageSquare class="w-4 h-4" />
              <span>{{ question.comments_count || question.answers_count || 0 }}</span>
            </button>
            <button
              class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-card text-vue"
              @click.stop="handleShare"
            >
              <i class="far fa-paper-plane text-base"></i>
              <span>Share</span>
            </button>
          </div>
          <button
            :disabled="isBookmarkLoading || !authStore.isAuthenticated"
            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-colors"
            :class="isBookmarked ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-card text-gray-600 dark:text-gray-300'"
            @click.stop="handleBookmark"
          >
            <Bookmark :class="['w-4 h-4', isBookmarked ? 'fill-current' : '']" />
            <span>{{ bookmarkCount || question.bookmark_count || 0 }}</span>
          </button>
        </div>
      </div>
    </article>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>