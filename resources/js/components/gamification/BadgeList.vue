<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import BadgeItem from './BadgeItem.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const props = defineProps({
  badges: {
    type: Array,
    default: () => []
  },
  ui: {
    type: String,
    default: 'grid'
  }
})
const emit = defineEmits(['view-badge'])
const handleBadgeClick = (badge) => {
  emit('view-badge', badge.slug)
}

// Swiper configuration for list view
const swiperModules = []
</script>
<template>
  <div>
    <div v-if="ui === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-4">
      <BadgeItem
        v-for="badge in badges"
        :key="badge.id"
        :badge="badge"
        @click="handleBadgeClick"
        data-test="badge-item"
      />
    </div>
    <div v-else-if="ui === 'list'" class="badge-swiper-container">
      <Swiper
        :modules="swiperModules"
        :slides-per-view="'auto'"
        :space-between="10"
        :free-mode="true"
        :grab-cursor="true"
        :breakpoints="{
          320: {
            slidesPerView: 'auto',
            spaceBetween: 16,
          },
          640: {
            slidesPerView: 'auto',
            spaceBetween: 16,
          },
          1024: {
            slidesPerView: 'auto',
            spaceBetween: 16,
          }
        }"
        class="badge-swiper"
      >
        <SwiperSlide
          v-for="badge in badges"
          :key="badge.id"
          class="!w-[250px]"
        >
          <BadgeItem
            :badge="badge"
            @click="handleBadgeClick"
            data-test="badge-item"
          />
        </SwiperSlide>
      </Swiper>
    </div>
    <div v-if="badges.length === 0" class="col-span-full">
      <EmptyState
        icon="Activity"
        title="No badges collected yet"
        subtitle="Collect badges to earn XP and level up!"
        size="default"
      />
    </div>
  </div>
</template>

<style scoped>
.badge-swiper-container {
  padding: 0.5rem 0;
  margin: 0 -0.5rem;
}

.badge-swiper {
  padding: 0 0.5rem;
}

.badge-swiper :deep(.swiper-slide) {
  width: auto;
  height: auto;
  display: flex;
}

.badge-swiper :deep(.swiper-wrapper) {
  display: flex;
  align-items: stretch;
}

/* Ensure Swiper is scrollable */
.badge-swiper-container :deep(.swiper) {
  overflow: visible;
}

/* On mobile, make it horizontally scrollable */
@media (max-width: 640px) {
  .badge-swiper-container :deep(.swiper) {
    overflow-x: auto;
    overflow-y: visible;
  }
  
  .badge-swiper-container :deep(.swiper-wrapper) {
    flex-wrap: nowrap;
  }
}

/* On desktop, show grid-like layout but still scrollable */
@media (min-width: 640px) {
  .badge-swiper-container :deep(.swiper) {
    overflow: visible;
  }
}
</style>