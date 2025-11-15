<script setup>
import { watch, ref, defineEmits, computed } from 'vue';
import PostCard from '../feed/PostCard.vue'
import QuestionCard from '../qna/QuestionCard.vue'
import { Skeleton } from '../ui/skeleton'
import EmptyState from '../ui/EmptyState.vue'
import { useAuthStore } from '../../stores/auth.js';
import { useThemeStore } from '../../stores/theme.js';
import { FileText } from 'lucide-vue-next';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const pagination = ref(null)
const l_count = ref(null)
const pageNo = ref(1)
const records = ref([])
const loading = ref(false);
const last_item = ref(1)
const hasLoaded = ref(false)
const { fetchKey, username, scrolling } = defineProps(['fetchKey', 'username', 'scrolling'])
const emit = defineEmits(['share_url'])

const url = computed(() => {
    if (scrolling == 'post') return '/api/v1/feed?page=' + pageNo.value;
    else if (scrolling == 'qna') return '/api/v1/questions-feed?page=' + pageNo.value;
})

const fetch = () => {
    pageNo.value = 1
    records.value = []
    hasLoaded.value = false
}

const index = async () => {
    if (loading.value) return;
    loading.value = true;
    await axios.post(url.value, {
        username: username,
    }, authStore.config).then((res) => {
        records.value.push(...res.data.records.data);
        l_count.value = res.data.records.data.length
        loading.value = false;
        hasLoaded.value = true;
        pagination.value = {
            "page": res.data.records.current_page,
            "pageSize": res.data.records.per_page,
            "pageCount": res.data.records.last_page,
            "total": res.data.records.total
        }
    }).catch(() => {
        loading.value = false;
        hasLoaded.value = true;
    })
}

const post_deleted = (value) => {
    records.value = records.value.filter(i => i.post_code !== value)
}

const question_deleted = (value) => {
    records.value = records.value.filter(i => i.slug !== value)
}

const view_more = () => {
    if (l_count.value > 0) {
        pageNo.value++
        index()
    }
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

index()
watch(pageNo, async (value) => { index() })
watch(() => fetchKey, async (value) => { fetch() })
</script>
<template>
    <div class="max-w-2xl mx-auto flex flex-col gap-4 sm:px-6 pb-5 infinite-scroll-container">
        <TransitionGroup name="fade" appear>
            <component
                :is="scrolling === 'post' ? PostCard : QuestionCard"
                v-for="(item, index) in records"
                :key="index"
                @share_url="share_url"
                @fetch="fetch"
                @liked_action="like_action"
                @delete_post="post_deleted"
                @delete_question="question_deleted"
                @load_more="view_more"
                :class="index === (records.length - last_item) ? 'last_item' : ''"
                v-bind="{ [scrolling === 'post' ? 'post' : 'question']: item }"
            />
        </TransitionGroup>
        <Transition class="w-full" name="fade">
            <div v-if="loading" class="gap-6 grid w-full">
                <Skeleton v-for="i in 5" :key="i" class="w-full rounded-2xl" :class="scrolling == 'post' ? 'h-[400px]' : 'h-[250px]'" />
            </div>
        </Transition>
        <!-- Empty State for Posts -->
        <EmptyState
            v-if="!loading && hasLoaded && records.length === 0 && scrolling === 'post'"
            icon="FileText"
            title="No Posts Yet"
            subtitle="This user hasn't posted anything yet."
            size="small"
        />
        <!-- Empty State for Questions -->
        <EmptyState
            v-if="!loading && hasLoaded && records.length === 0 && scrolling === 'qna'"
            icon="FileText"
            title="No Questions Yet"
            subtitle="This user hasn't asked any questions yet."
            size="small"
        />
    </div>
</template>
