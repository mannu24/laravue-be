<template>
    <div class="space-y-4">
        <div v-for="item in items" :key="item.id" class="relative" :class="{ 'pl-[20px]': depth > 0 }">
            <!-- Answer Container -->
            <div class="flex gap-4 p-4 dark:bg-gray-950 border-[1px] rounded-lg shadow">
                <!-- Upvote Section -->
                <div v-if="item.user?.id !== authUserId" class="flex flex-col items-center">
                    <button @click="() => $emit('upvote', item.id)"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        :class="{ 'text-primary': item.hasUpvoted }">
                        <ChevronUp class="h-5 w-5" />
                    </button>
                    <span class="text-sm font-medium">{{ item.upvotes || 0 }}</span>
                </div>

                <!-- Content Section -->
                <div class="flex-1">
                    <div v-html="item.content" class="prose dark:prose-invert max-w-none text-sm"></div>
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <Avatar class="h-6 w-6">
                                    <AvatarImage :src="item.user?.avatar_url" />
                                    <AvatarFallback class="text-white">{{ getInitials(item.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <span>{{ item.user?.name || 'Anonymous' }}</span>
                            </div>
                            <span>{{ formatDate(item.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="ghost" size="sm" @click="toggleReply(item.id)">
                                <MessageSquare class="h-4 w-4 mr-1" />
                                Reply
                            </Button>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    <div v-if="showReplyForm[item.id]" class="mt-4 border-l-2 border-primary pl-4">
                        <MarkDownEditor v-model="replyContent[item.id]" placeholder="Write your reply..."
                            :min-height="100" />
                        <div class="flex justify-end mt-2 space-x-2">
                            <Button variant="outline" size="sm" @click="toggleReply(item.id)">Cancel</Button>
                            <Button size="sm" @click="submitReply(item.id)">Post Reply</Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <div v-if="item.replies && item.replies.length > 0" class="mt-2">
                <div v-for="reply in visibleReplies(item.id)" :key="reply.id" class="ml-6 border-l-2 border-gray-200 pl-4 py-2">
                    <div class="flex gap-4 p-4 dark:bg-gray-950 border-[1px] rounded-lg shadow">
                        <!-- Upvote Section -->
                        <div v-if="reply.user?.id !== authUserId" class="flex flex-col items-center">
                            <button @click="$emit('upvote', { id: reply.id, type: 'answer' })"
                                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <ChevronUp class="h-5 w-5" />
                            </button>
                            <span class="text-sm font-medium">{{ reply.upvotes_count || 0 }}</span>
                        </div>

                        <!-- Content Section -->
                        <div class="flex-1">
                            <div v-html="reply.content" class="prose dark:prose-invert max-w-none text-sm"></div>
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <Avatar class="h-6 w-6">
                                            <AvatarImage :src="reply.user?.avatar_url" />
                                            <AvatarFallback class="text-white">{{ getInitials(reply.user?.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span>{{ reply.user?.name || 'Anonymous' }}</span>
                                    </div>
                                    <span>{{ formatDate(reply.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Show More/Less Button -->
                <div v-if="item.replies.length > maxReplies" class="ml-6 pl-4 mt-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="toggleReplies(item.id)"
                        class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-400"
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
import { ChevronUp, MessageSquare } from 'lucide-vue-next'
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
