<template>
    <div class="space-y-4">
        <div v-for="item in items" :key="item.id" class="relative">
            <!-- Answer Container -->
            <div class="flex gap-4 p-4 bg-white dark:bg-gray-950 border-[1px] rounded-lg shadow"
                :class="{ 'ml-[40px]': item.depth > 0 }">
                <!-- Rest of the answer container content remains same -->
                <div v-if="item.user?.id !== authUserId" class="flex flex-col items-center">
                    <button @click="() => $emit('upvote', item.id)"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        :class="{ 'text-primary': item.hasUpvoted }">
                        <ChevronUp class="h-5 w-5" />
                    </button>
                    <span class="text-sm font-medium">{{ item.upvotes_count }}</span>
                </div>

                <div class="flex-1">
                    <div v-html="item.content" class="prose dark:prose-invert max-w-none text-sm"></div>

                    <!-- Answer Metadata -->
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <Avatar class="h-6 w-6">
                                    <AvatarImage :src="item.user?.avatar_url" />
                                    <AvatarFallback class="text-white">{{ getInitials(item.user?.name) }}</AvatarFallback>
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
                            <Button variant="outline" size="sm" @click="toggleReply(item.id)">
                                Cancel
                            </Button>
                            <Button size="sm" @click="submitReply(item.id)">
                                Post Reply
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nested Replies -->
            <div v-if="item.replies && item.replies.length > 0" class="mt-2">
                <RecursiveAnswers :items="item.replies" :auth-user-id="authUserId" @reply="$emit('reply', $event)"
                    @upvote="$emit('upvote', $event)" />
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
    items: {
        type: Array,
        required: true
    },
    authUserId: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['reply', 'upvote'])

const showReplyForm = ref({})
const replyContent = ref({})

const toggleReply = (id) => {
    showReplyForm.value[id] = !showReplyForm.value[id]
    if (!showReplyForm.value[id]) {
        replyContent.value[id] = ''
    }
}

const submitReply = (parentId) => {
    const content = replyContent.value[parentId]
    if (!content?.trim()) return

    emit('reply', { parentId, content })
    toggleReply(parentId)
}

const formatDate = (dateString) => {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(dateString))
}

const getInitials = (name) => {
    return name ? name.split(' ').map(n => n[0]).join('').toUpperCase() : '?'
}
</script>