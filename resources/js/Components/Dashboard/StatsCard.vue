<template>
  <div class="bg-white/20 p-3 rounded-lg">
    <div class="text-2xl font-bold">{{ value }}</div>
    <div class="text-sm text-blue-100">{{ label }}</div>
  </div>
</template>

<script setup>
defineProps({
  value: [String, Number],
  label: String
})
</script>