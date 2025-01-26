<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Navbar from './components/Navbar.vue'

const isScrollTopVisible = ref(false)
onMounted(() => {
  window.addEventListener('scroll', handleScrollTop);
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScrollTop);
});
const handleScrollTop = () => {
	isScrollTopVisible.value = window.scrollY > 200;
}
const scollTop = () => {
    window.scrollTo({top: 0, behaviour: 'smooth'})
}
</script>
<template>
    <div class="min-h-screen bg-gray-900">
        <Navbar />
        <router-view />
		<transition name="fade">
			<button v-if="isScrollTopVisible" class="fixed right-10 bottom-10 flex justify-center items-center text-white bg-primary-500 w-10 h-10 transition-all duration-300 ease-in-out rounded-full hover:bg-secondary-300 hover:scale-110" type="button" @click="scollTop()">
				<i class="fas fa-arrow-up me-0"></i>
			</button>
		</transition>
    </div>
</template>
<style>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
  transform-origin: center;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>