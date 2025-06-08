<template>
  <PublicLayout>
    <div class="max-w-6xl mx-auto p-6 space-y-6">
      <h1 class="text-3xl font-bold text-indigo-700">Electronics</h1>
      <div v-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>
      <p v-else class="text-gray-500">No electronics products available.</p>
    </div>
  </PublicLayout>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

const products = usePage().props.products || []
</script>
