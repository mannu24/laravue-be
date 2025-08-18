
<script setup>
defineProps({
  project: Object
});
</script><template>
  <article class="flex flex-col items-start bg-gray-800 rounded-lg overflow-hidden">
    <div class="relative w-full">
      <img :src="project.image" :alt="project.title"
        class="aspect-[16/9] w-full bg-gray-100 object-cover sm:aspect-[2/1] lg:aspect-[3/2]">
      <div class="absolute inset-0 rounded-t-lg ring-1 ring-inset ring-gray-900/10"></div>
    </div>
    <div class="max-w-xl p-6">
      <div class="flex items-center gap-x-4 text-xs">
        <span :class="[
          project.type === 'Premium' ? 'bg-yellow-500' : 'bg-green-500',
          'rounded-full px-3 py-1.5 font-medium text-white'
        ]">{{ project.type }}</span>
        <div class="flex items-center gap-x-1">
          <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
            </path>
          </svg>
          <span class="text-gray-300">{{ project.stars }}</span>
        </div>
      </div>
      <div class="group relative">
        <h3 class="mt-3 text-lg font-semibold leading-6 text-white">
          <router-link :to="'/projects/' + project.id">
            <span class="absolute inset-0"></span>
            {{ project.title }}
          </router-link>
        </h3>
        <p class="mt-5 line-clamp-3 text-sm leading-6 text-gray-300">{{ project.description }}</p>
      </div>
      <div v-if="project.price" class="mt-4">
        <span class="text-primary-400 font-semibold">{{ project.price }}</span>
      </div>
    </div>
  </article>
</template>
<style scoped>
/* Add your styles here, if necessary */
</style>