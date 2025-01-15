<script setup>
import ProjectCard from '../components/ProjectCard.vue';
import ProjectFilters from '../components/ProjectFilters.vue';

const projects = [
  {
    id: 1,
    title: 'Project Management Tool',
    description: 'A comprehensive project management solution built with Vue and Laravel',
    type: 'Premium',
    price: '$49',
    stars: 245,
    image: 'https://picsum.photos/400/300'
  },
  {
    id: 2,
    title: 'Open Source CMS',
    description: 'A modern content management system for developers',
    type: 'Open Source',
    stars: 1200,
    image: 'https://picsum.photos/400/301'
  },
  {
    id: 3,
    title: 'E-commerce Platform',
    description: 'A scalable e-commerce solution with Vue.js frontend',
    type: 'Premium',
    price: '$99',
    stars: 890,
    image: 'https://picsum.photos/400/302'
  }
]
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-white mb-8">Browse Projects</h1>
    <!-- <ProjectFilters @filter="(value) => console.log(value)" /> -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <ProjectCard v-for="project in projects" :key="project.id" :project="project" />
    </div>
  </div>
</template>