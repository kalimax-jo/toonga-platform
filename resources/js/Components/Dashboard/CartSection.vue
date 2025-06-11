<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">{{ title }}</h3>
    <div class="space-y-4">
      <CartItem 
        v-for="item in items" 
        :key="item.id"
        :item="item"
        @remove="$emit('remove', item.id)"
        @update="$emit('update', item.id, $event)"
      />
    </div>
  </div>
</template>

<script setup>
import CartItem from './CartItem.vue'

defineProps({
  title: String,
  items: Array
})

defineEmits(['remove', 'update'])
</script>