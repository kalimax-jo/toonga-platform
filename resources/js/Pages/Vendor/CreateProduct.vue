<template>
  <VendorLayout>
    <div class="p-6 max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold">Add Product</h1>

      <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-4 bg-white p-4 shadow rounded">
        <input v-model="form.title" placeholder="Title" class="w-full border p-2 rounded" required />
        <textarea v-model="form.description" placeholder="Description" class="w-full border p-2 rounded"></textarea>
        <input v-model.number="form.price" type="number" placeholder="Price (RWF)" class="w-full border p-2 rounded" required />

        <!-- Category Select -->
        <select v-model="form.category_id" class="w-full border p-2 rounded" required @change="loadCategoryType">
          <option value="">Select Category</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>

        <input v-model.number="form.commission" type="number" placeholder="Commission %" class="w-full border p-2 rounded" required />

        <!-- 🔁 Dynamic type-specific form -->
        <component
          v-if="selectedType"
          :is="getTypeFormComponent(selectedType)"
          v-model="form"
        />

        <!-- Preview Images -->
        <div>
          <label class="block font-semibold mb-1">Upload up to 3 images</label>
          <input type="file" multiple accept="image/*" @change="handleImages" />
          <div class="flex gap-2 mt-2">
            <img v-for="(img, idx) in previewImages" :key="idx" :src="img" class="w-24 h-24 object-cover rounded border" />
          </div>
        </div>

        <!-- Product Video -->
        <div>
          <label class="block font-semibold mb-1">Upload product video (optional)</label>
          <input type="file" accept="video/*" @change="handleVideo" />
          <video v-if="previewVideo" controls class="w-full max-w-sm mt-2 rounded shadow">
            <source :src="previewVideo" type="video/mp4" />
          </video>
        </div>

        <button class="bg-indigo-600 text-white px-6 py-2 rounded">Submit Product</button>
      </form>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import VendorLayout from '@/Layouts/VendorLayout.vue'
import { usePage } from '@inertiajs/vue3'

// Type-specific dynamic components
import FlightProductForm from './Forms/FlightProductForm.vue'

const categories = usePage().props.categories || []
const categoryMap = ref({}) // will map category_id to type

const form = ref({
  title: '',
  description: '',
  price: '',
  category_id: '',
  commission: '',
  additional_data: {},
  preview_images: [],
  product_video: null,
})

const selectedType = ref('')
const previewImages = ref([])
const previewVideo = ref(null)

// 🧠 Load category type when selected
const loadCategoryType = async () => {
  if (!form.value.category_id) return
  const res = await axios.get(`/api/categories/${form.value.category_id}`)
  selectedType.value = res.data.type?.name || ''
}

// 🔁 Dynamic form component based on type name
const getTypeFormComponent = (type) => {
  const typeMap = {
    Flights: FlightProductForm,
    Hotels: null, // to be added
    'Car Rentals': null,
    Electronics: null,
    'Food & Beverage': null,
    Furniture: null,
  }
  return typeMap[type] || null
}

const handleImages = (e) => {
  const files = Array.from(e.target.files).slice(0, 3)
  form.value.preview_images = files
  previewImages.value = files.map(file => URL.createObjectURL(file))
}

const handleVideo = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.value.product_video = file
    previewVideo.value = URL.createObjectURL(file)
  }
}

const submitForm = async () => {
  const data = new FormData()
  data.append('title', form.value.title)
  data.append('description', form.value.description)
  data.append('price', form.value.price)
  data.append('commission', form.value.commission)
  data.append('category_id', form.value.category_id)

  data.append('additional_data', JSON.stringify(form.value.additional_data || {}))

  form.value.preview_images.forEach((img, i) => {
    data.append(`preview_images[${i}]`, img)
  })

  if (form.value.product_video) {
    data.append('product_video', form.value.product_video)
  }

  try {
    await axios.post('/vendor/products', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert('Product submitted successfully')
    window.location.href = '/vendor/dashboard'
  } catch (err) {
    console.error(err)
    alert('Submission failed — see console for details')
  }
}
</script>
