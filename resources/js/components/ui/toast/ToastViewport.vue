<script setup>
import { cn } from '/resources/js/lib/utils';
import { ToastViewport } from 'radix-vue';
import { computed } from 'vue';

const props = defineProps({
  hotkey: { type: Array, required: false },
  label: { type: [String, Function], required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});
</script>

<template>
  <ToastViewport
    v-bind="delegatedProps"
    :class="
      cn(
        'fixed bottom-0 right-0 z-[100] flex max-h-screen w-full flex-col p-4 md:max-w-[420px]',
        props.class,
      )
    "
  />
</template>
