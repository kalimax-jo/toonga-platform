<template>
  <Link 
    :href="route" 
    :class="[
      'p-4 rounded-lg cursor-pointer transition-colors text-center hover:shadow-md',
      colorClasses
    ]"
  >
    <div class="text-2xl mb-2">{{ icon }}</div>
    <div class="text-sm font-medium">{{ title }}</div>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  icon: String,
  title: String,
  route: String,
  color: String
})

const colorClasses = computed(() => {
  const colors = {
    blue: 'bg-blue-50 hover:bg-blue-100 text-blue-700',
    green: 'bg-green-50 hover:bg-green-100 text-green-700',
    orange: 'bg-orange-50 hover:bg-orange-100 text-orange-700',
    purple: 'bg-purple-50 hover:bg-purple-100 text-purple-700',
    red: 'bg-red-50 hover:bg-red-100 text-red-700',
    yellow: 'bg-yellow-50 hover:bg-yellow-100 text-yellow-700'
  }
  return colors[props.color] || colors.blue
})
</script>