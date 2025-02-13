<script setup >
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute()
const project = ref({
  id: 1,
  title: 'Project Management Tool',
  description: 'A comprehensive project management solution built with Vue and Laravel. Features include task management, team collaboration, real-time updates, and more.',
  type: 'Premium',
  price: '$49',
  stars: 245,
  image: 'https://picsum.photos/800/400',
  github: 'https://github.com/example/project',
  demo: 'https://demo.example.com',
  author: {
    name: 'John Doe',
    avatar: 'https://picsum.photos/50/50',
    username: 'johndoe'
  },
  technologies: ['Vue.js', 'Laravel', 'Tailwind CSS', 'PostgreSQL']
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-gray-800 rounded-lg overflow-hidden">
      <img :src="project.image" :alt="project.title" class="w-full h-64 object-cover">
      <div class="p-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-3xl font-bold text-white">{{ project.title }}</h1>
          <div class="flex items-center space-x-4">
            <span :class="[
              project.type === 'Premium' ? 'bg-yellow-500' : 'bg-green-500',
              'rounded-full px-4 py-1 text-white font-medium'
            ]">{{ project.type }}</span>
            <div class="flex items-center">
              <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
              <span class="ml-1 text-white">{{ project.stars }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center mb-6">
          <router-link :to="`/profile/${project.author.username}`" class="flex items-center">
            <img :src="project.author.avatar" :alt="project.author.name" class="w-10 h-10 rounded-full">
            <span class="ml-2 text-white">{{ project.author.name }}</span>
          </router-link>
        </div>

        <p class="text-gray-300 mb-6">{{ project.description }}</p>

        <div class="flex flex-wrap gap-2 mb-6">
          <span v-for="tech in project.technologies" :key="tech"
            class="px-3 py-1 bg-gray-700 rounded-full text-sm text-white">
            {{ tech }}
          </span>
        </div>

        <div class="flex space-x-4 mb-8">
          <a :href="project.github" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"></path>
            </svg>
            View on GitHub
          </a>
          <a :href="project.demo" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center px-4 py-2 bg-primary-600 rounded-md hover:bg-primary-700">
            Live Demo
          </a>
          <button v-if="project.type === 'Premium'"
            class="inline-flex items-center px-4 py-2 bg-green-600 rounded-md hover:bg-green-700">
            Buy Now - {{ project.price }}
          </button>
        </div>

        <div class="border-t border-gray-700 pt-6">
          <h2 class="text-xl font-semibold text-white mb-4">Support this project</h2>
          <div class="flex space-x-4">
            <button class="px-4 py-2 bg-purple-600 rounded-md hover:bg-purple-700">
              Fund Project
            </button>
            <button class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600">
              Contribute
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>