<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useThemeStore } from '../../stores/theme'
import { Card, CardContent } from '@/components/ui/card'
import { 
    FileText, 
    MessageSquare, 
    UserPlus, 
    Heart,
    ArrowRight
} from 'lucide-vue-next'

const props = defineProps({
    activity: {
        type: Object,
        required: true
    }
})

const router = useRouter()
const themeStore = useThemeStore()

const activityIcon = computed(() => {
    switch (props.activity.type) {
        case 'post_created':
            return FileText
        case 'question_created':
            return MessageSquare
        case 'answer_created':
            return MessageSquare
        case 'comment_created':
            return MessageSquare
        case 'follow_created':
            return UserPlus
        case 'like_created':
            return Heart
        default:
            return FileText
    }
})

const activityColor = computed(() => {
    switch (props.activity.type) {
        case 'post_created':
            return themeStore.isDark ? 'bg-blue-900/30 text-blue-400' : 'bg-blue-100 text-blue-600'
        case 'question_created':
            return themeStore.isDark ? 'bg-green-900/30 text-green-400' : 'bg-green-100 text-green-600'
        case 'answer_created':
            return themeStore.isDark ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600'
        case 'comment_created':
            return themeStore.isDark ? 'bg-yellow-900/30 text-yellow-400' : 'bg-yellow-100 text-yellow-600'
        case 'follow_created':
            return themeStore.isDark ? 'bg-pink-900/30 text-pink-400' : 'bg-pink-100 text-pink-600'
        case 'like_created':
            return themeStore.isDark ? 'bg-red-900/30 text-red-400' : 'bg-red-100 text-red-600'
        default:
            return themeStore.isDark ? 'bg-gray-800 text-gray-400' : 'bg-gray-100 text-gray-600'
    }
})

const handleClick = () => {
    if (!props.activity.subject) return
    
    switch (props.activity.type) {
        case 'post_created':
            if (props.activity.subject.post_code) {
                router.push(`/posts/${props.activity.subject.post_code}`)
            }
            break
        case 'question_created':
            if (props.activity.subject.slug) {
                router.push(`/questions/${props.activity.subject.slug}`)
            }
            break
        case 'answer_created':
            if (props.activity.subject.question?.slug) {
                router.push(`/questions/${props.activity.subject.question.slug}`)
            }
            break
        case 'follow_created':
            if (props.activity.subject.following?.username) {
                router.push(`/@${props.activity.subject.following.username}`)
            }
            break
    }
}

const isClickable = computed(() => {
    return ['post_created', 'question_created', 'answer_created', 'follow_created'].includes(props.activity.type)
})
</script>

<template>
    <Card 
        :class="[
            'transition-all duration-200 border-0 shadow-sm',
            themeStore.isDark ? 'bg-gray-800/50 hover:bg-gray-800/70' : 'bg-white/50 hover:bg-white/80',
            isClickable ? 'cursor-pointer' : ''
        ]"
        @click="handleClick"
    >
        <CardContent class="p-4">
            <div class="flex items-start gap-4">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <img 
                        :src="activity.user.profile_photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(activity.user.name)}&background=6366f1&color=fff&size=100`"
                        :alt="activity.user.name"
                        class="w-10 h-10 rounded-full object-cover border-2"
                        :class="themeStore.isDark ? 'border-gray-700' : 'border-gray-200'"
                    />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                            {{ activity.user.name }}
                        </span>
                        <span class="text-sm" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-500'">
                            @{{ activity.user.username }}
                        </span>
                        <span class="text-xs" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'">
                            {{ activity.created_at_human }}
                        </span>
                    </div>

                    <p class="text-sm mb-2" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ activity.description }}
                    </p>

                    <!-- Subject Preview -->
                    <div v-if="activity.subject" class="mt-3 p-3 rounded-lg border" :class="[
                        themeStore.isDark ? 'bg-gray-900/50 border-gray-700' : 'bg-gray-50 border-gray-200'
                    ]">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center" :class="activityColor">
                                <component :is="activityIcon" class="h-4 w-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div v-if="activity.type === 'post_created' && activity.subject.title" class="font-medium text-sm mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                    {{ activity.subject.title }}
                                </div>
                                <div v-else-if="activity.type === 'question_created' && activity.subject.title" class="font-medium text-sm mb-1" :class="themeStore.isDark ? 'text-white' : 'text-gray-900'">
                                    {{ activity.subject.title }}
                                </div>
                                <div v-else-if="activity.type === 'answer_created' && activity.subject.question" class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                                    Answered: {{ activity.subject.question.title }}
                                </div>
                                <div v-else-if="activity.type === 'follow_created' && activity.subject.following" class="text-sm" :class="themeStore.isDark ? 'text-gray-300' : 'text-gray-700'">
                                    Now following {{ activity.subject.following.name }}
                                </div>
                                <div v-else-if="activity.subject.content" class="text-sm line-clamp-2" :class="themeStore.isDark ? 'text-gray-400' : 'text-gray-600'">
                                    {{ activity.subject.content }}
                                </div>
                            </div>
                            <ArrowRight v-if="isClickable" class="h-4 w-4 flex-shrink-0" :class="themeStore.isDark ? 'text-gray-500' : 'text-gray-400'" />
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

