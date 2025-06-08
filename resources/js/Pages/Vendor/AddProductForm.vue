<template>
  <VendorLayout>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-bold mb-4">Add New Product</h1>
      <form @submit.prevent="submitProduct" class="space-y-4">
        <input v-model="form.title" type="text" placeholder="Product Title" class="w-full border p-2 rounded" required />

        <textarea v-model="form.description" placeholder="Description" class="w-full border p-2 rounded"></textarea>

        <input v-model="form.price" type="number" placeholder="Price (RWF)" class="w-full border p-2 rounded" required />

        <select v-model="form.category_id" @change="loadCategoryType" class="w-full border p-2 rounded" required>
          <option disabled value="">Select Category</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <input v-model="form.commission" type="number" placeholder="Commission % (e.g. 20)" class="w-full border p-2 rounded" required />

        <!-- Dynamic Type-Specific Form -->
        <component :is="currentFormComponent" v-if="currentFormComponent" v-model="form" />

        <button class="bg-indigo-600 text-white px-4 py-2 rounded">Submit</button>
      </form>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import VendorLayout from '@/Layouts/VendorLayout.vue'

// Import dynamic form components (Create folder if not exists)
import FlightProductForm from './Forms/FlightProductForm.vue'
import HotelProductForm from './Forms/HotelProductForm.vue'
import ElectronicsProductForm from './Forms/ElectronicsProductForm.vue'
// You can add more forms as needed...

const categories = ref([])
const currentFormComponent = ref(null)

const form = ref({
  title: '',
  description: '',
  price: '',
  category_id: '',
  commission: '',
  additional_data: {}
})

const formMap = {
  Flights: FlightProductForm,
  Hotels: HotelProductForm,
  Electronics: ElectronicsProductForm,
  // Add other mappings as needed
}

const loadCategories = async () => {
  const res = await axios.get('/api/categories-for-vendor')
  categories.value = res.data
}

const loadCategoryType = async () => {
  if (!form.value.category_id) return
  const res = await axios.get(`/api/categories/${form.value.category_id}`)
  const typeName = res.data.type?.name
  currentFormComponent.value = formMap[typeName] || null
}

const submitProduct = async () => {
  try {
    await axios.post('/vendor/products', form.value)
    alert('Product submitted successfully!')
    form.value = {
      title: '', description: '', price: '', category_id: '', commission: '', additional_data: {}
    }
    currentFormComponent.value = null
  } catch (error) {
    console.error(error)
    alert('Failed to submit product')
  }
}

onMounted(loadCategories)
</script>
