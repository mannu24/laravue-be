<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';

import { useAuthStore } from '../stores/auth.js';

const authStore = useAuthStore();

const navigation = [
  { name: 'Home', href: '/', current: true },
  { name: 'Projects', href: '/projects', current: false },
  { name: 'About', href: '/about', current: false },
  { name: 'Contact', href: '/contact', current: false },
]
</script>

<template>
  <Disclosure as="nav" class="bg-gray-900" v-slot="{ open }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <router-link to="/" class="text-primary-500 font-bold text-xl flex items-center">
              <span class="text-secondary-500">Lara</span>Vue
            </router-link>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <router-link v-for="item in navigation" :key="item.name" :to="item.href"
                :class="[item.current ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                  'rounded-md px-3 py-2 text-sm font-medium']">
                {{ item.name }}
              </router-link>
            </div>
          </div>
        </div>
        <div class="hidden md:block">
          <div v-if="authStore.isAuthenticated" class="ml-4 flex items-center md:ml-6">
            <router-link :to="'/profile/' + authStore?.user?.username"
              class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">
              {{ authStore?.user?.name }}
            </router-link>
          </div>
          <div v-else class="ml-4 flex items-center md:ml-6">
            <router-link to="/login"
              class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">
              Login
            </router-link>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <DisclosureButton
            class="inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <DisclosurePanel class="md:hidden">
      <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
        <router-link v-for="item in navigation" :key="item.name" :to="item.href"
          :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white',
            'block rounded-md px-3 py-2 text-base font-medium']">
          {{ item.name }}
        </router-link>
      </div>
      <div class="border-t border-gray-700 pb-3 pt-4">
        <div class="flex items-center px-5">
          <router-link to="/login"
            class="block w-full text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-base font-medium">
            Login
          </router-link>
          <router-link to="/signup"
            class="block w-full bg-secondary-500 text-white rounded-md px-3 py-2 text-base font-medium hover:bg-secondary-600">
            Sign up
          </router-link>
        </div>
      </div>
    </DisclosurePanel>
  </Disclosure>
</template>