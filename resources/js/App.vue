<script setup lang="ts">
import Navbar from './components/Navbar.vue'
</script>

<template>
    <div class="min-h-screen bg-gray-900">
        <Navbar />
        <router-view />
    </div>
</template>