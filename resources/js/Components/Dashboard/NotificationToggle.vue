<template>
  <div class="flex items-center justify-between py-2">
    <div class="flex-1">
      <div class="font-medium text-gray-900">{{ title }}</div>
      <div class="text-sm text-gray-500">{{ description }}</div>
    </div>
    <label class="relative inline-flex items-center cursor-pointer">
      <input 
        type="checkbox" 
        :checked="modelValue"
        @change="$emit('update:modelValue', $event.target.checked)"
        class="sr-only peer"
      >
      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
    </label>
  </div>
</template>

<script setup>
defineProps({
  modelValue: Boolean,
  title: String,
  description: String
})

defineEmits(['update:modelValue'])
</script>