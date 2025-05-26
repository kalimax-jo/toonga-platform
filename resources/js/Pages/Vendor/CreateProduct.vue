<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Add New Product</h1>
    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label>Title:</label>
        <input v-model="form.title" class="border p-2 w-full" required />
      </div>
      <div>
        <label>Description:</label>
        <textarea v-model="form.description" class="border p-2 w-full" />
      </div>
      <div>
        <label>Price (RWF):</label>
        <input type="number" v-model="form.price" class="border p-2 w-full" required />
      </div>
      <div>
        <label>Category:</label>
        <select v-model="form.category_id" class="border p-2 w-full" required>
          <option value="" disabled>Select category</option>
          <option v-for="cat in categories" :value="cat.id" :key="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>
      <div>
        <label>Commission for Toonga (%):</label>
        <input type="number" v-model="form.commission" class="border p-2 w-full" min="1" max="100" required />
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit Product</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

defineProps({ categories: Array })

const form = ref({
  title: '',
  description: '',
  price: '',
  category_id: '',
  commission: ''
})

const submitForm = async () => {
  await axios.post('/vendor/products', form.value)
  router.visit('/vendor/dashboard')
}
</script>
