<template>
  <!-- @click="$router.push(post_url)"  -->
    <div @click="$router.push(post_url)" ref="element" class="bg-white shadow-md rounded-lg overflow-hidden mb-4 hover:cursor-pointer hover:bg-blue transition-all duration-350 ease-in">
      <div class="flex items-center p-4">
        <router-link :to="'/'+post.post.user.username" @click.stop>
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
        </router-link>
        <div class="ml-3">
          <router-link @click.stop :to="'/'+post.post.user.username" class="font-semibold text-lg">{{ post.post.user.name }}</router-link>
          <p class="text-sm text-gray-500">{{ $filters.date(post.post.created_at) }}</p>
        </div>
        <div class="relative inline-block ml-auto self-start" v-if="post.post.owner">
          <i class="fas fa-ellipsis-v cursor-pointer p-2" @click.stop="showDropdown = !showDropdown"></i>
          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <div v-if="showDropdown" class="absolute right-0 z-10 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white ring-1 shadow-lg ring-black/5 focus:outline-hidden" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
              <div class="" role="none">
                <!-- Active: "bg-gray-100 text-gray-900 outline-hidden", Not Active: "text-gray-700" -->
                <a href="#" @click="edit_record()" class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:text-gray-900">
                  <i class="fas fa-pencil-alt mr-1"></i>
                  Edit
                </a>
                <!-- <a href="#" @click="action('duplicate')" class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:text-gray-900">
                  <i class="fas fa-files mr-1"></i>
                  Duplicate
                </a> -->
                <a href="#" @click="action('delete')" class="block px-4 py-2 text-sm text-gray-700 transition-all duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:bg-gray-100 focus:text-gray-900">
                  <i class="fas fa-trash mr-1"></i>
                  Delete
                </a>
              </div>
            </div>
          </transition>
        </div>
      </div>
      <div class="px-4">
        <h3 class="font-bold text-xl text-gray-800 mb-0">{{ post.post.title }}</h3>
        <p class="text-gray-700 mb-2" v-html="renderContent(post.post.content)" @click.stop></p>
        <div v-if="post.post.media_urls && post.post.media_urls.length" class="grid gap-2 mb-4">
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
import { onMounted, ref, defineEmits, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';

const post = defineProps(['post'])
const element = ref(null)
const showDropdown = ref(false)
const emit = defineEmits(['load_more', 'fetch', 'delete_post'])

const authStore = useAuthStore()
const post_url = computed(() => '/@'+post.post.user.username+'/'+post.post.post_code)
onMounted(() => {
  if(element.value.classList.contains('last_item')) {
    checkItem()
  }
})

const action = async (type) => {
  if(type === 'delete') {
    await Swal.fire({
      title: 'Are you sure?',
      text: 'You will not be able to recover this post!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'No, keep it',
    }).then( async (result) => {
      if (result.isConfirmed) {
        showDropdown.value = false
        await axios.delete(`/api/v1/posts/${post.post.post_code}`, authStore.config).then(() => {
          emit('delete_post', post.post.post_code)
          // Swal.fire('Deleted!', 'Your post has been deleted.', 'success')
        })
      }
    })
  } else {
    showDropdown.value = false
    await axios.get(`/api/v1/posts/duplicate/${post.post.post_code}`, authStore.config).then(() => {
			emit('fetch')
    })
  }
}

const renderContent = (content) => {
  return content.replace(/@(\w+)/g, '<a href="/$1">@$1</a>');
};

// const renderContent = (feed) => {
//     return feed.content.replace(
//         /<mention data-user-id='(\d+)'>@([^<]+)<\/mention>/g,
//         (match, id, username) => {
//             return `<a href="/users/${id}" class="text-blue-500 hover:underline">@${username}</a>`;
//         }
//     );
// };

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
<style  scoped>
.mention-link {
  --tw-text-opacity: 1;
  color: rgb(42 97 70 / var(--tw-text-opacity, 1));
  padding-left: 0.25rem;
  padding-right: 0.25rem;
  --tw-bg-opacity: 1;
  background-color: rgb(209 235 223 / var(--tw-bg-opacity, 1));
  border-radius: 0.25rem;
}
.mention-link:hover {
}
</style>