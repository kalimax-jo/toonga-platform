<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center gap-3 mb-4">
      <div class="text-2xl">{{ icon }}</div>
      <div>
        <h3 class="text-lg font-semibold">{{ title }}</h3>
        <p class="text-gray-600 text-sm">{{ description }}</p>
      </div>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: String,
  icon: String,
  description: String
})
</script>