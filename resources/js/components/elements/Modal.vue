<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-2xl">
            <div class="flex justify-end p-2">
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineEmits } from 'vue';
const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
}
</script>