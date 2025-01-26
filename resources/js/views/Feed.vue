<script setup>
import { watch, ref } from 'vue';
import PostCard from '../components/feed/PostCard.vue'
import PostForm from '../components/feed/PostForm.vue';
import { useAuthStore } from '../stores/auth.js';

const authStore = useAuthStore();
const pagination = ref(null)
const l_count = ref(null)
const pageNo = ref(1)
const search = ref('')
const posts = ref([])
const loading = ref(false);
const last_item = ref(1)

const fetch = () => {
  pageNo.value = 1
  posts.value = []
}

const index = async () => {
    if (loading.value) return;
    loading.value = true;
    await axios.post('/api/feed?page='+pageNo.value, {
        search: search.value,
    }, authStore.config).then((res) => {
        posts.value.push(...res.data.posts.data);
        l_count.value = res.data.posts.data.length
        loading.value = false;
        pagination.value = {
            "page": res.data.posts.current_page,
            "pageSize": res.data.posts.per_page,
            "pageCount": res.data.posts.last_page,
            "total": res.data.posts.total
        }
    })
}
index()
watch(pageNo, async (value) => { index() })
watch(search, async (value) => { index() })

const view_more = () => {
  if(l_count.value > 0) {
    pageNo.value++
    index()
  }
}
</script>
<template>
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-0 pb-12">
    <PostForm v-if="authStore.isAuthenticated" @fetch="fetch"></PostForm>
  </div>
  <div class="max-w-3xl mx-auto sm:px-6 pb-5 infinite-scroll-container">
    <TransitionGroup name="fade" appear>
      <PostCard v-for="post,index in posts" @load_more="view_more" :class="index == (posts.length - last_item) ? 'last_item' : ''" :key="index" :post="post" />
    </TransitionGroup>
    <Transition name="fade">
      <loader v-if="loading"></loader>
    </Transition>
  </div>
</template>