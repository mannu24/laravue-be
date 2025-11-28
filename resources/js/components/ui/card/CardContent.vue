<script setup>
import { cn } from '/resources/js/lib/utils';

const props = defineProps({
  class: { type: null, required: false },
});
</script>

<template>
  <div :class="cn('p-5 pt-0', props.class)">
    <slot />
  </div>
</template>
