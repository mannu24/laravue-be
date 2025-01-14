<script setup>
import ProjectCard from '../components/ProjectCard.vue';
import { useAuthStore } from '../stores/auth.js';

const authStore = useAuthStore();
console.log(authStore.isAuthenticated, authStore.user);

const featuredProjects = [
  {
    id: 1,
    title: 'Vue Laravel Admin Panel',
    description: 'A modern admin panel built with Vue 3 and Laravel 10',
    type: 'Premium',
    price: '$49',
    stars: 245,
    image: 'https://picsum.photos/400/300'
  },
  {
    id: 2,
    title: 'Vue Laravel Starter Kit',
    description: 'Get started quickly with this Vue + Laravel template',
    type: 'Open Source',
    stars: 1200,
    image: 'https://picsum.photos/400/301'
  },
  {
    id: 3,
    title: 'Real-time Chat App',
    description: 'Full-stack chat application with Laravel WebSockets',
    type: 'Premium',
    price: '$39',
    stars: 890,
    image: 'https://picsum.photos/400/302'
  }
]

const communityStats = [
  { label: 'Active Members', value: '10,000+' },
  { label: 'Projects Shared', value: '5,000+' },
  { label: 'Monthly Downloads', value: '1M+' },
  { label: 'Countries', value: '120+' }
]
</script>

<template>
  <div class="bg-gray-900">
    <!-- Hero Section -->
    <div class="relative isolate overflow-hidden">
      <div class="mx-auto max-w-7xl px-6 pb-24 pt-10 sm:pb-32 lg:flex lg:px-8 lg:py-40">
        <div class="mx-auto max-w-2xl flex-shrink-0 lg:mx-0 lg:max-w-xl lg:pt-8">
          <div class="mt-24 sm:mt-32 lg:mt-16">
            <a href="/projects" class="inline-flex space-x-6">
              <span
                class="rounded-full bg-secondary-500/10 px-3 py-1 text-sm font-semibold leading-6 text-secondary-500 ring-1 ring-inset ring-secondary-500/20">
                What's new
              </span>
              <span class="inline-flex items-center space-x-2 text-sm font-medium leading-6 text-gray-300">
                <span>Just shipped v1.0</span>
                <span aria-hidden="true">→</span>
              </span>
            </a>
          </div>
          <h1 class="mt-10 text-4xl font-bold tracking-tight text-white sm:text-6xl">
            Vue & Laravel Developer Community
          </h1>
          <p class="mt-6 text-lg leading-8 text-gray-300">
            Join thousands of developers sharing and discovering amazing projects built with Vue.js and Laravel.
            Contribute, collaborate, and grow together.
          </p>
          <div class="mt-10 flex items-center gap-x-6">
            <router-link to="/projects"
              class="rounded-md bg-primary-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-400">
              Explore Projects
            </router-link>
            <router-link to="/add-project"
              class="rounded-md bg-secondary-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-400">
              Share Your Project
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Community Stats -->
    <div class="border-t border-gray-800 bg-gray-900">
      <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
          <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
              Trusted by developers worldwide
            </h2>
            <p class="mt-4 text-lg leading-8 text-gray-300">
              Join our thriving community of Vue.js and Laravel developers
            </p>
          </div>
          <dl
            class="mt-16 grid grid-cols-1 gap-0.5 overflow-hidden rounded-2xl text-center sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="stat in communityStats" :key="stat.label" class="flex flex-col bg-gray-800/80 p-8">
              <dt class="text-sm font-semibold leading-6 text-gray-300">{{ stat.label }}</dt>
              <dd class="order-first text-3xl font-semibold tracking-tight text-white">{{ stat.value }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Featured Projects -->
    <div class="py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
          <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Featured Projects</h2>
          <p class="mt-2 text-lg leading-8 text-gray-300">
            Discover popular projects from our community
          </p>
        </div>
        <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
          <ProjectCard v-for="project in featuredProjects" :key="project.id" :project="project" />
        </div>
        <div class="mt-10 text-center">
          <router-link to="/projects" class="text-sm font-semibold leading-6 text-primary-400 hover:text-primary-300">
            View all projects <span aria-hidden="true">→</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Community Features -->
    <div class="bg-gray-800 py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center">
          <h2 class="text-base font-semibold leading-7 text-secondary-500">Build Together</h2>
          <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Everything you need to grow as a developer
          </p>
          <p class="mt-6 text-lg leading-8 text-gray-300">
            Join a community of passionate developers sharing knowledge, code, and opportunities.
          </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
          <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-white">
                <svg class="h-5 w-5 flex-none text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12zm-1-5a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"
                    clip-rule="evenodd" />
                </svg>
                Share & Discover
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-300">
                <p class="flex-auto">Share your projects and discover amazing work from other developers in the
                  community.</p>
              </dd>
            </div>
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-white">
                <svg class="h-5 w-5 flex-none text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12zm-1-5a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"
                    clip-rule="evenodd" />
                </svg>
                Collaborate & Learn
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-300">
                <p class="flex-auto">Work together on open-source projects and learn from experienced developers.</p>
              </dd>
            </div>
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-white">
                <svg class="h-5 w-5 flex-none text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12zm-1-5a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"
                    clip-rule="evenodd" />
                </svg>
                Grow & Earn
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-300">
                <p class="flex-auto">Monetize your work through premium projects and receive community support.</p>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>