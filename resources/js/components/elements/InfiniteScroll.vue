<script setup>
import { watch, ref, defineEmits, computed } from 'vue'
import axios from 'axios'
import PostCard from '../feed/PostCard.vue'
import QuestionCard from '../qa/QuestionCard.vue'
import ProjectCard from '../projects/ProjectCard.vue'
import { Skeleton } from '../ui/skeleton'
import EmptyState from '../ui/EmptyState.vue'
import LoadingSpinner from '../ui/LoadingSpinner.vue'
import { useAuthStore } from '../../stores/auth.js'

const props = defineProps({
  fetchKey: {
    type: [Number, String, null],
    default: null
  },
  username: {
    type: [String, null],
    default: null
  },
  scrolling: {
    type: String,
    default: 'post'
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  perPage: {
    type: Number,
    default: 12
  },
  userId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['share_url'])
const authStore = useAuthStore()
const pagination = ref(null)
const l_count = ref(null)
const pageNo = ref(1)
const records = ref([])
const loading = ref(false)
const last_item = ref(1)
const hasLoaded = ref(false)
const hasMore = ref(true)

const cardComponent = computed(() => {
  if (props.scrolling === 'post') return PostCard
  if (props.scrolling === 'qna') return QuestionCard
  return ProjectCard
})

const cardPropKey = computed(() => {
  if (props.scrolling === 'post') return 'post'
  if (props.scrolling === 'qna') return 'question'
  return 'project'
})

const resetRecords = () => {
  pageNo.value = 1
  records.value = []
  hasLoaded.value = false
  hasMore.value = true
  l_count.value = null
}

const refetch = () => {
  resetRecords()
  index()
}

async function fetchPosts() {
  const params = {
    page: pageNo.value,
    per_page: props.perPage || 10,
    ...(props.username && { username: props.username }),
    ...(props.filters?.sort && { sort: props.filters.sort }),
    ...(props.filters?.tags && { tags: Array.isArray(props.filters.tags) ? props.filters.tags.join(',') : props.filters.tags })
  }
  const response = await axios.get('/api/v1/posts', {
    params,
    ...(authStore.isAuthenticated ? authStore.config : {})
  })
  const responseData = response.data.records || response.data
  const newRecords = responseData.data || []
  records.value.push(...newRecords)
  l_count.value = newRecords.length
  pagination.value = {
    page: responseData.current_page || pageNo.value,
    pageSize: responseData.per_page || props.perPage,
    pageCount: responseData.last_page || 1,
    total: responseData.total || 0
  }
  hasMore.value = pagination.value.page < pagination.value.pageCount
  if (newRecords.length === 0) {
    hasMore.value = false
  }
}

async function fetchQuestions() {
  const url = `/api/v1/questions-feed?page=${pageNo.value}`
  const response = await axios.post(
    url,
    {
      username: props.username
    },
    authStore.config
  )
  const newRecords = response.data.records.data
  records.value.push(...newRecords)
  l_count.value = newRecords.length
  pagination.value = {
    page: response.data.records.current_page,
    pageSize: response.data.records.per_page,
    pageCount: response.data.records.last_page,
    total: response.data.records.total
  }
  if (newRecords.length === 0) {
    hasMore.value = false
  }
}

async function fetchProjects() {
      const params = {
        page: pageNo.value,
        per_page: props.perPage,
        ...props.filters
      }
      if (props.userId) {
        params.user_id = props.userId
      }
      const response = await axios.get('/api/v1/projects', {
        params,
        ...(authStore.isAuthenticated ? authStore.config : {})
      })
      const newRecords = response.data.data || []
      records.value.push(...newRecords)
      l_count.value = newRecords.length
      pagination.value = {
        page: response.data.current_page,
        pageSize: response.data.per_page,
        pageCount: response.data.last_page,
        total: response.data.total
      }
      hasMore.value = response.data.current_page < response.data.last_page
      if (hasMore.value) {
        pageNo.value = response.data.current_page + 1
      }
}

const index = async () => {
  if (loading.value || !hasMore.value) return
  loading.value = true

  try {
    if (props.scrolling === 'project') {
      await fetchProjects()
    } else if (props.scrolling === 'post') {
      await fetchPosts()
    } else if (props.scrolling === 'qna') {
      await fetchQuestions()
    }
  } catch (error) {
    console.error('Error fetching records:', error)
    hasMore.value = false
  } finally {
    loading.value = false
    hasLoaded.value = true
  }
}

const post_deleted = (value) => {
  records.value = records.value.filter((i) => i.post_code !== value)
}

const question_deleted = (value) => {
  records.value = records.value.filter((i) => i.slug !== value)
}

const handleProjectUpvoted = (project) => {
  if (props.scrolling !== 'project') return
  const index = records.value.findIndex((p) => p.id === project.id)
  if (index !== -1) {
    records.value[index] = { ...project }
  }
}

const view_more = () => {
  if (loading.value) return
  if (props.scrolling !== 'project') {
    pageNo.value++
  }
  index()
}

const share_url = (url) => {
  emit('share_url', url)
}

const like_action = async (data) => {
  records.value.find((post, index) => {
    if (post.post_code === data[0]) {
      records.value[index].liked = data[1]
      if (data[1]) {
        records.value[index].likes_count++
      } else {
        records.value[index].likes_count--
      }
    }
  })
}

const rootClass = computed(() => {
  if (props.scrolling === 'project') {
    return 'w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-5'
  }
  return 'max-w-2xl mx-auto flex flex-col gap-4 sm:px-6 pb-5'
})

const heightClass = computed(() => {
  switch (props.scrolling) {
    case 'post':
      return 'h-[400px]'
    case 'qna':
      return 'h-[250px]'
    case 'project':
      return 'h-[600px]'
  }
})

const skeletonCount = computed(() => {
  switch (props.scrolling) {
    case 'post':
      return 5
    case 'qna':
      return 5
    case 'project':
      return 6
  }
})

const isInitialLoading = computed(() => loading.value && !hasLoaded.value)

const loadingMessage = computed(() => {
  switch (props.scrolling) {
    case 'post':
      return 'Fetching posts...'
    case 'qna':
      return 'Fetching questions...'
    case 'project':
      return 'Fetching projects...'
    default:
      return 'Loading content...'
  }
})
index()

watch(
  () => props.fetchKey,
  () => {
    refetch()
  }
)

watch(
  () => props.filters,
  () => {
    refetch()
  },
  { deep: true }
)

watch(
  () => props.userId,
  () => {
    refetch()
  }
)

watch(
  () => props.perPage,
  () => {
    refetch()
  }
)
</script>
<template>
    <div>
        <div
            v-if="isInitialLoading"
            class="min-h-[40vh] flex flex-col items-center justify-center gap-3 text-center text-gray-500 dark:text-gray-300"
        >
            <LoadingSpinner size="lg" />
            <p class="text-sm font-medium">{{ loadingMessage }}</p>
        </div>
        <div v-else :class="[rootClass, 'infinite-scroll-container']">
        <TransitionGroup name="fade" appear>
            <component
                :is="cardComponent"
                v-for="(item, index) in records"
                :key="index"
                @share_url="share_url"
                @fetch="refetch"
                @liked_action="like_action"
                @delete_post="post_deleted"
                @delete_question="question_deleted"
                @load_more="view_more"
                @upvoted="handleProjectUpvoted"
                :class="index === (records.length - last_item) ? 'last_item' : ''"
                v-bind="{ [cardPropKey]: item }"
            />
        </TransitionGroup>
        <template v-if="loading">
            <TransitionGroup name="fade">
              <Skeleton v-for="i in skeletonCount" :key="i" class="w-full rounded-2xl" :class="heightClass" />
            </TransitionGroup>
        </template>
        <!-- Empty State for Posts -->
        <EmptyState
            v-if="!loading && hasLoaded && records.length === 0"
            icon="FileText"
            :title="scrolling === 'post' ? 'No Posts Yet' : scrolling === 'qna' ? 'No Questions Yet' : 'No Projects Yet'"
            :subtitle="scrolling === 'post'
                ? 'This user hasn\'t posted anything yet.'
                : scrolling === 'qna'
                    ? 'This user hasn\'t asked any questions yet.'
                    : 'No projects match your criteria.'"
            size="small"
        />
        </div>
    </div>
</template>
