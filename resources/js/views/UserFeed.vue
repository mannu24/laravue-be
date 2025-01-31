<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const username = route.params.username;

</script>

<template>
  <div>
    <h1>{{ username }}'s Feed</h1>
    <!-- Fetch and show posts based on username -->
  </div>
</template>
