<template>
  <Card class="w-full max-w-3xl mx-auto mb-4">
    <CardHeader>
      <div class="flex items-center">
        <Avatar v-if="post.user?.profile_photo" :src="post.user.profile_photo" fallback="U" />
        <Avatar v-else src="/assets/front/images/user.png" fallback="U" />
        <div class="ml-3">
          <CardTitle>{{ post.user.name }}</CardTitle>
          <CardDescription>{{ $filters.date(post.created_at) }}</CardDescription>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <h3 class="text-xl font-bold text-gray-800 mb-2">{{ post.title }}</h3>
      <p class="text-gray-700 mb-4">{{ post.content }}</p>
      <div v-if="post.media_urls && post.media_urls.length" class="grid gap-2">
        <!-- Media display logic remains the same -->
      </div>
      {{ isCommentDialogOpen.value }}
    </CardContent>
    <CardFooter class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Button variant="ghost" @click="handleLike">
          <ThumbsUpIcon class="mr-2 h-4 w-4" />
          Like
        </Button>
        <Button variant="ghost" @click="handleComment">
          <MessageSquareIcon class="mr-2 h-4 w-4" />
          Comment
        </Button>
        <Button variant="ghost" @click="handleShare">
          <ShareIcon class="mr-2 h-4 w-4" />
          Share
        </Button>
      </div>
      <Badge variant="secondary">Views: {{ post.views }}</Badge>
    </CardFooter>
  </Card>

  <Dialog v-model:open="isCommentDialogOpen">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Add a comment</DialogTitle>
        <DialogDescription>
          Write your comment below. Click save when you're done.
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <Textarea v-model="commentText" placeholder="Type your comment here." />
      </div>
      <DialogFooter>
        <Button @click="submitComment">Save comment</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { ThumbsUpIcon, MessageSquareIcon, ShareIcon } from 'lucide-vue-next';

const props = defineProps(['post']);
const emit = defineEmits(['load_more']);

const element = ref(null);
const isCommentDialogOpen = ref(false);
const commentText = ref('');

onMounted(() => {
  if (element.value.classList.contains('last_item')) {
    checkItem();
  }
});

const checkItem = () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        emit('load_more');
      }
    });
  });
  observer.observe(element.value);
};

const handleLike = () => {
  // Implement like functionality
  console.log('Liked post');
};

const handleComment = () => {
  isCommentDialogOpen.value = true;
};

const handleShare = () => {
  // Implement share functionality
  console.log('Shared post');
};

const submitComment = () => {
  // Implement comment submission
  console.log('Submitted comment:', commentText.value);
  isCommentDialogOpen.value = false;
  commentText.value = '';
};
</script>