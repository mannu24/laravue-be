<template>
    <div class="space-y-4">
        <div v-for="item in items" :key="item.id" class="relative">
        <!-- Answer Container -->
        <div class="bg-card border border-gray-200 dark:border-gray-800 rounded-lg shadow">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 px-4 md:px-5 py-4">
                <div class="flex-1 min-w-0">
                    <div v-html="item.body" class="prose dark:prose-invert max-w-none text-sm mb-3 md:mb-4"></div>
                    <div class="flex items-center gap-2 md:gap-4 text-sm text-gray-500 flex-wrap">
                        <div class="flex items-center gap-2">
                            <Avatar class="h-6 w-6 bg-red-700">
                                <AvatarImage v-if="item.user?.avatar_url" :src="item.user.avatar_url" />
                                <AvatarFallback class="text-white">{{ getInitials(item.user?.name) }}</AvatarFallback>
                            </Avatar>
                            <div class="text-xs md:text-sm">{{ item.user?.name || 'Anonymous' }} &bull; {{ formatDate(item.created_at) }}</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-start gap-2 md:gap-2 w-full md:w-auto">
                    <Button @click="$emit('upvote', item.id)" variant="outline" size="sm" class="flex items-center space-x-1">
                        <CircleChevronUp class="h-5 w-5 md:h-6 md:w-6" />
                        <span class="text-xs md:text-sm">{{ item.upvotes_count || item.upvotes || 0 }}</span>
                    </Button>

                    <Button variant="ghost" size="sm" @click="toggleReply(item.id)" class="flex-shrink-0">
                        <MessageSquare class="h-4 w-4 mr-1" />
                        <span class="hidden sm:inline">Reply</span>
                    </Button>
                </div>
            </div>
            <div v-if="showReplyForm[item.id]" class="px-4 md:px-5 pb-4 border-t border-gray-200 dark:border-gray-800 md:border-l-2 md:border-t-0 md:border-l-primary md:ml-4 md:pl-4 md:pr-5">
                <MarkDownEditor v-model="replyContent[item.id]" placeholder="Write your reply..."
                    :min-height="100" />
                <div class="flex flex-row justify-end gap-2 mt-2">
                    <Button variant="outline" size="sm" @click="toggleReply(item.id)" class="w-full sm:w-auto">Cancel</Button>
                    <Button size="sm" @click="submitReply(item.id)" class="w-full sm:w-auto">Post Reply</Button>
                </div>
            </div>
        </div>
        <!-- Replies -->
        <div v-if="item.replies && item.replies.length > 0" class="mt-2">
            <div v-for="reply in visibleReplies(item.id)" :key="reply.id" class="ml-2 md:ml-6 border-l-2 border-gray-200 dark:border-gray-700 pl-2 md:pl-4 py-2">
                <div class="flex flex-col md:flex-row gap-3 md:gap-4 p-3 md:p-4 bg-card border border-gray-200 dark:border-gray-800 rounded-lg shadow">
                    <!-- Content Section -->
                    <div class="flex-1 min-w-0">
                        <div v-html="reply.content" class="prose dark:prose-invert max-w-none text-sm"></div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mt-3 md:mt-4">
                            <div class="flex items-center gap-2 md:gap-4 text-xs md:text-sm text-gray-500 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <Avatar class="h-5 w-5 md:h-6 md:w-6">
                                        <AvatarImage v-if="reply.user?.avatar_url" :src="reply.user.avatar_url" />
                                        <AvatarFallback class="text-white text-xs">{{ getInitials(reply.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span>{{ reply.user?.name || 'Anonymous' }}</span>
                                </div>
                                <span class="text-xs">{{ formatDate(reply.created_at) }}</span>
                            </div>
                            <!-- Upvote Section -->
                            <div v-if="reply.user?.id !== authUserId" class="flex items-center gap-1">
                                <button @click="$emit('upvote', { id: reply.id, type: 'answer' })"
                                    class="p-1.5 md:p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <ChevronUp class="h-3.5 w-3.5 md:h-4 md:w-4" />
                                </button>
                                <span class="text-xs md:text-sm font-medium">{{ reply.upvotes_count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Show More/Less Button -->
            <div v-if="item.replies.length > maxReplies" class="ml-2 md:ml-6 pl-2 md:pl-4 mt-2">
                <Button
                    variant="ghost"
                    size="sm"
                    @click="toggleReplies(item.id)"
                    class="text-xs md:text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
                >
                    {{ showAllReplies[item.id] ? 'Show Less' : `Show More (${item.replies.length - maxReplies} more)` }}
                </Button>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { ChevronUp, CircleChevronUp, MessageSquare } from 'lucide-vue-next'
import MarkDownEditor from './MarkDownEditor.vue'

const props = defineProps({
    items: { type: Array, required: true },
    authUserId: { type: Number, required: true },
    maxReplies: { type: Number, default: 0 }
})

const emit = defineEmits(['reply', 'upvote'])

const showReplyForm = ref({})
const replyContent = ref({})
const showMore = ref({}) // Track which items' deeper replies are expanded
const showAllReplies = ref({}) // Track if all replies are shown for each item

const toggleReply = (id) => {
    showReplyForm.value[id] = !showReplyForm.value[id]
    if (!showReplyForm.value[id]) replyContent.value[id] = ''
}

const submitReply = (parentId) => {
    const content = replyContent.value[parentId]
    if (!content?.trim()) return
    emit('reply', { parentId, content })
    toggleReply(parentId)
}

const toggleShowMore = (id) => {
    showMore.value[id] = !showMore.value[id]
}

const toggleReplies = (id) => {
    showAllReplies.value[id] = !showAllReplies.value[id]
}

const visibleReplies = (id) => {
    const item = props.items.find(item => item.id === id)
    if (!item || !item.replies) return []

    if (showAllReplies.value[id]) {
        return item.replies
    } else {
        return item.replies.slice(0, props.maxReplies)
    }
}

const formatDate = (dateString) => {
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(dateString))
}

const getInitials = (name) => {
    return name ? name.split(' ').map(n => n[0]).join('').toUpperCase() : '?'
}
</script>
