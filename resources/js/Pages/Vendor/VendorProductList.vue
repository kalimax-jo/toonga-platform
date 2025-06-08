<template>
  <VendorLayout>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold">My Products</h1>

      <!-- Approved Products -->
      <div>
        <h2 class="text-lg font-semibold mb-2">Approved Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <ProductCard v-for="product in approved" :key="product.id" :product="product" />
        </div>
        <p v-if="approved.length === 0" class="text-gray-500">No approved products yet.</p>
      </div>

      <!-- Pending Products -->
      <div>
        <h2 class="text-lg font-semibold mb-2">Pending Approval</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <ProductCard v-for="product in pending" :key="product.id" :product="product" />
        </div>
        <p v-if="pending.length === 0" class="text-gray-500">No products pending approval.</p>
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import VendorLayout from '@/Layouts/VendorLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'

const approved = ref([])
const pending = ref([])

onMounted(async () => {
  const res = await axios.get('/api/vendor/products')
  approved.value = res.data.approved
  pending.value = res.data.pending
})
</script>

<style scoped>
/* Optional styling */
</style>
