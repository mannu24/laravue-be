<template>
    <div ref="element" class="bg-white shadow-md rounded-lg overflow-hidden mb-4">
      <div class="flex items-center p-4">
        <img
            v-if="post.post.user?.profile_photo"
            :src="post.post.user?.profile_photo"
            alt="User Avatar"
            class="w-12 h-12 rounded-full"
        />
        <img
            v-else
            src="/assets/front/images/user.png"
            alt="User Avatar"
            class="w-12 h-12 rounded-full"
        />
        <div class="ml-3">
          <h2 class="font-semibold text-lg">{{ post.post.user.name }}</h2>
          <p class="text-sm text-gray-500">{{ $filters.date(post.post.created_at) }}</p>
        </div>
      </div>
      <div class="px-4">
        <h3 class="font-bold text-xl text-gray-800 mb-1">{{ post.post.title }}</h3>
        <p class="text-gray-700 mb-4">{{ post.post.content }}</p>
        <div v-if="post.post.media_urls && post.post.media_urls.length" class="grid gap-2">
            <img
                v-if="post.post.media_urls.length === 1"
                :src="post.post.media_urls[0]"
                alt="Post Media"
                class="cursor-pointer rounded-lg object-cover w-full h-80"
            />
            <div v-else-if="post.post.media_urls.length === 2" class="grid grid-cols-2 gap-2">
                <img
                    v-for="(media, index) in post.post.media_urls"
                    v-if="index < 2"
                    :key="media"
                    :src="media"
                    alt="Post Media"
                    class="cursor-pointer rounded-lg object-cover h-40 w-full"
                />
            </div>
            <div v-else-if="post.post.media_urls.length === 3" class="grid grid-cols-2 gap-2">
                <img
                v-for="(media, index) in post.post.media_urls.slice(0, 2)"
                :key="media"
                :src="media"
                alt="Post Media"
                class="cursor-pointer rounded-lg object-cover h-40 w-full"
                />
                <img
                :src="post.post.media_urls[2]"
                alt="Post Media"
                class="cursor-pointer col-span-2 rounded-lg object-cover h-40 w-full"
                />
            </div>
            <div v-else class="grid grid-cols-2 gap-2 relative">
                <img
                    v-for="(media, index) in post.post.media_urls.slice(0, 4)"
                    :key="media"
                    :src="media"
                    alt="Post Media"
                    class="cursor-pointer rounded-lg object-cover h-40 w-full"
                />
                <div
                    v-if="post.post.media_urls.length > 4"
                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl font-bold rounded-lg"
                    :style="{ gridArea: '2 / 2 / 3 / 3' }"
                >
                    +{{ post.post.media_urls.length - 4 }} more
                </div>
            </div>
        </div>
      </div>
      <div class="px-4 py-2 border-t flex items-center justify-between text-gray-600">
        <div class="flex items-center space-x-4">
          <button class="flex items-center space-x-1 hover:text-blue-500">
            <i class="fa fa-thumbs-up"></i>
            <span>Like</span>
          </button>
          <button class="flex items-center space-x-1 hover:text-blue-500">
            <i class="fa fa-comment"></i>
            <span>Comment</span>
          </button>
          <button class="flex items-center space-x-1 hover:text-blue-500">
            <i class="fa fa-share"></i>
            <span>Share</span>
          </button>
        </div>
        <p class="text-sm">Views: {{ post.post.views }}</p>
      </div>
    </div>
</template>
<script setup>
import { onMounted, ref, defineEmits } from 'vue';

const post = defineProps(['post'])
const element = ref(null)
const emit = defineEmits(['load_more'])


onMounted(() => {
  if(element.value.classList.contains('last_item')) {
    checkItem()
  }
})

const checkItem = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                emit('load_more')
            }
        });
    });
    observer.observe(element.value);
}

</script>