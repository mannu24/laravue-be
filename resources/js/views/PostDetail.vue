<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const username = route.params.username;
const postcode = route.params.postcode;

</script>

<template>
  <div>
    <h1>Post by {{ username }}</h1>
    <p>Post ID: {{ postcode }}</p>
    <!-- Fetch and show post details -->
  </div>
</template>
