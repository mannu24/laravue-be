<script setup>
import { computed } from 'vue'
import { Button } from './ui/button'
import { useFollow } from '../composables/useFollow'
import { UserPlus, UserMinus, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    username: {
        type: String,
        required: true
    },
    variant: {
        type: String,
        default: 'default'
    },
    size: {
        type: String,
        default: 'default'
    },
    showIcon: {
        type: Boolean,
        default: true
    },
    initialFollowing: {
        type: Boolean,
        default: false
    },
    initialFollowersCount: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['followed', 'unfollowed'])

const { isFollowing, isLoading, toggleFollow } = useFollow()

// Initialize with props
if (props.initialFollowing !== undefined) {
    isFollowing.value = props.initialFollowing
}

const buttonText = computed(() => {
    if (isLoading.value) {
        return 'Loading...'
    }
    return isFollowing.value ? 'Unfollow' : 'Follow'
})

const handleClick = async () => {
    const success = await toggleFollow(props.username)
    
    if (success) {
        if (isFollowing.value) {
            emit('followed', props.username)
        } else {
            emit('unfollowed', props.username)
        }
    }
}
</script>

<template>
    <Button
        :variant="isFollowing ? 'outline' : variant"
        :size="size"
        :disabled="isLoading"
        @click="handleClick"
        class="transition-all duration-200"
        :class="[
            isFollowing 
                ? 'hover:bg-destructive hover:text-destructive-foreground' 
                : ''
        ]"
    >
        <Loader2 v-if="isLoading" class="h-4 w-4 mr-2 animate-spin" />
        <UserPlus v-else-if="!isFollowing && showIcon" class="h-4 w-4 mr-2" />
        <UserMinus v-else-if="isFollowing && showIcon" class="h-4 w-4 mr-2" />
        {{ buttonText }}
    </Button>
</template>

