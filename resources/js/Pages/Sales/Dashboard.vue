<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Pending Product Approvals</h1>
    <table class="w-full border">
      <thead>
        <tr>
          <th class="border p-2">Product</th>
          <th class="border p-2">Vendor</th>
          <th class="border p-2">Category</th>
          <th class="border p-2">Price</th>
          <th class="border p-2">Commission (%)</th>
          <th class="border p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in pendingProducts" :key="product.id">
          <td class="border p-2">{{ product.title }}</td>
          <td class="border p-2">{{ product.vendor.name }}</td>
          <td class="border p-2">{{ product.category.name }}</td>
          <td class="border p-2">{{ product.price }} RWF</td>
          <td class="border p-2">{{ product.commission }}%</td>
          <td class="border p-2 space-x-2">
            <button @click="approve(product.id)" class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
            <button @click="reject(product.id)" class="bg-red-600 text-white px-3 py-1 rounded">Reject</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

defineProps({ pendingProducts: Array })

const approve = async (id) => {
  await axios.post(`/sales/products/${id}/approve`)
  window.location.reload()
}

const reject = async (id) => {
  await axios.post(`/sales/products/${id}/reject`)
  window.location.reload()
}
</script>
