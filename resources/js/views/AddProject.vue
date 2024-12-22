<script setup>
import { ref } from 'vue';

const projectData = ref({
  title: '',
  description: '',
  type: 'opensource',
  githubUrl: '',
  demoUrl: '',
  price: '',
  technologies: '',
  preview: null
})

const handleSubmit = () => {
  console.log('Form submitted:', projectData.value)
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-white mb-8">Add Your Project</h1>
    <form @submit.prevent="handleSubmit" class="bg-gray-800 rounded-lg p-6">
      <div class="space-y-6">
        <div>
          <label for="title" class="block text-sm font-medium text-white">Project Title</label>
          <input type="text" id="title" v-model="projectData.title"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-white">Description</label>
          <textarea id="description" v-model="projectData.description" rows="4"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white"></textarea>
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-white">Project Type</label>
          <select id="type" v-model="projectData.type"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
            <option value="opensource">Open Source</option>
            <option value="premium">Premium</option>
          </select>
        </div>

        <div>
          <label for="githubUrl" class="block text-sm font-medium text-white">GitHub URL</label>
          <input type="url" id="githubUrl" v-model="projectData.githubUrl"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
        </div>

        <div>
          <label for="demoUrl" class="block text-sm font-medium text-white">Demo URL</label>
          <input type="url" id="demoUrl" v-model="projectData.demoUrl"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
        </div>

        <div v-if="projectData.type === 'premium'">
          <label for="price" class="block text-sm font-medium text-white">Price</label>
          <input type="text" id="price" v-model="projectData.price"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
        </div>

        <div>
          <label for="technologies" class="block text-sm font-medium text-white">Technologies (comma-separated)</label>
          <input type="text" id="technologies" v-model="projectData.technologies"
            class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-white">
        </div>

        <div>
          <label class="block text-sm font-medium text-white">Preview Image</label>
          <input type="file" accept="image/*" @change="e => projectData.preview = e.target.files?.[0] || null"
            class="mt-1 block w-full text-white">
        </div>

        <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
          Submit Project
        </button>
      </div>
    </form>
  </div>
</template>