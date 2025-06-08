<template>
  <div class="border rounded shadow p-4 bg-white flex flex-col gap-2">
    <div v-if="product.images?.length">
      <img :src="`/storage/${product.images[0]}`" alt="Image" class="h-40 w-full object-cover rounded" />
    </div>
    <h3 class="text-lg font-semibold">{{ product.title }}</h3>
    <p class="text-gray-600 text-sm truncate">{{ product.description }}</p>
    <p class="text-indigo-600 font-bold">{{ formatPrice(product.price) }} RWF</p>
    <span
      class="inline-block px-2 py-1 text-xs font-semibold rounded"
      :class="product.is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
    >
      {{ product.is_approved ? 'Approved' : 'Pending' }}
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  product: Object
})

const formatPrice = (val) => {
  return new Intl.NumberFormat().format(val)
}
</script>
