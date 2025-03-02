<!-- resources/js/views/Profile.vue -->
<template>
  <div class="profile-page">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>
    <social-links />
  </div>
</template>

<script>
import SocialLinks from '../components/profile/SocialLinks.vue';

export default {
  components: { SocialLinks },
};
</script>

<style scoped>
.profile-page {
  padding: 20px;
}
</style>