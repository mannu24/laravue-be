<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import ProjectCard from '../components/ProjectCard.vue';

const route = useRoute()
const profile = ref({
  username: 'johndoe',
  name: 'John Doe',
  avatar: 'https://picsum.photos/200/200',
  bio: 'Full-stack developer passionate about Vue.js and Laravel',
  location: 'San Francisco, CA',
  links: [
    { type: 'github', url: 'https://github.com/johndoe' },
    { type: 'twitter', url: 'https://twitter.com/johndoe' },
    { type: 'website', url: 'https://johndoe.dev' }
  ],
  projects: [
    {
      id: 1,
      title: 'Project Management Tool',
      description: 'A comprehensive project management solution',
      type: 'Premium',
      price: '$49',
      stars: 245,
      image: 'https://picsum.photos/400/300'
    },
    {
      id: 2,
      title: 'Open Source CMS',
      description: 'A modern content management system',
      type: 'Open Source',
      stars: 1200,
      image: 'https://picsum.photos/400/301'
    }
  ]
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-gray-800 rounded-lg overflow-hidden">
      <div class="p-8">
        <div class="flex items-center space-x-6">
          <img :src="profile.avatar" :alt="profile.name" class="w-24 h-24 rounded-full">
          <div>
            <h1 class="text-2xl font-bold text-white">{{ profile.name }}</h1>
            <p class="text-gray-400">@{{ profile.username }}</p>
            <p class="text-gray-300 mt-2">{{ profile.bio }}</p>
            <p class="text-gray-400 mt-1">{{ profile.location }}</p>
          </div>
        </div>

        <div class="flex space-x-4 mt-6">
          <a v-for="link in profile.links" :key="link.url" :href="link.url"
            target="_blank" rel="noopener noreferrer"
            class="px-4 py-2 bg-gray-700 rounded-md hover:bg-gray-600 text-white">
            {{ link.type }}
          </a>
        </div>
      </div>
    </div>

    <div class="mt-12">
      <h2 class="text-2xl font-bold text-white mb-6">Projects</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <ProjectCard v-for="project in profile.projects" :key="project.id" :project="project" />
      </div>
    </div>
  </div>
</template>