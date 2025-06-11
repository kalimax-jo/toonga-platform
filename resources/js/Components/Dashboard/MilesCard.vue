<template>
  <div :class="['p-6 rounded-lg', cardClass]">
    <div class="text-3xl mb-2">{{ icon }}</div>
    <h3 class="text-lg font-semibold mb-1">{{ title }}</h3>
    <p class="text-2xl font-bold mb-1">{{ formattedValue }}</p>
    <p :class="subtitleClass">{{ subtitle }}</p>
    
    <!-- Progress Bar for Tier Status -->
    <div v-if="showProgress" class="w-full bg-gray-200 rounded-full h-2 mt-3">
      <div 
        class="bg-purple-600 h-2 rounded-full transition-all duration-300"
        :style="{ width: `${progress}%` }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  value: [String, Number],
  subtitle: String,
  icon: String,
  class: String,
  warning: Boolean,
  showProgress: Boolean,
  progress: Number
})

const cardClass = computed(() => props.class || 'bg-white shadow')

const subtitleClass = computed(() => {
  if (props.class?.includes('text-white')) return 'text-white/80'
  if (props.warning) return 'text-orange-600'
  return 'text-gray-500'
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString()
  }
  return props.value
})
</script>