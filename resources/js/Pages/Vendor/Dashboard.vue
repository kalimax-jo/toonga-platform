<script setup>
import { usePage } from '@inertiajs/vue3'
import VendorLayout from '@/Layouts/VendorLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

const products = usePage().props.products || []
</script>

<template>
  <VendorLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Vendor Dashboard</h1>

      <div v-if="products.length === 0" class="text-gray-500">
        You haven’t added any products yet.
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>
    </div>
  </VendorLayout>
</template>
