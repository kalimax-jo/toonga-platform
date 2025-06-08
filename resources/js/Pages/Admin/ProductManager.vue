<template>
  <AdminLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Manage Products</h1>

      <div class="bg-white shadow rounded">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-100 text-left">
              <th class="p-2">Title</th>
              <th class="p-2">Vendor</th>
              <th class="p-2">Category</th>
              <th class="p-2">Price</th>
              <th class="p-2">Commission (%)</th>
              <th class="p-2">Status</th>
              <th class="p-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products" :key="product.id" class="border-t">
              <td class="p-2">{{ product.title }}</td>
              <td class="p-2">{{ product.vendor.name }}</td>
              <td class="p-2">{{ product.category.name }}</td>
              <td class="p-2">{{ product.price }}</td>
              <td class="p-2">{{ product.commission }}</td>
              <td class="p-2">
                <span :class="product.is_approved ? 'text-green-600' : 'text-yellow-600'">
                  {{ product.is_approved ? 'Approved' : 'Pending' }}
                </span>
              </td>
              <td class="p-2 flex gap-2">
                <button @click="editProduct(product)" class="text-blue-600">Edit</button>
                <button @click="deleteProduct(product.id)" class="text-red-600">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Edit Modal -->
      <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-full max-w-md">
          <h2 class="text-lg font-bold mb-4">Edit Product</h2>
          <input v-model="editForm.title" placeholder="Title" class="border p-2 w-full mb-2" />
          <input v-model="editForm.price" type="number" placeholder="Price" class="border p-2 w-full mb-2" />
          <input v-model="editForm.commission" type="number" placeholder="Commission (%)" class="border p-2 w-full mb-2" />
          <div class="flex items-center gap-2 mb-4">
            <label>
              <input type="checkbox" v-model="editForm.is_approved" /> Approved
            </label>
          </div>
          <div class="flex justify-end gap-2">
            <button @click="updateProduct" class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
            <button @click="editing = false" class="text-gray-500">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const products = ref([])
const editing = ref(false)
const editForm = ref({})

const loadProducts = async () => {
  const res = await axios.get('/api/products')
  products.value = res.data
}

const deleteProduct = async (id) => {
  if (confirm('Are you sure you want to delete this product?')) {
    await axios.delete(`/api/products/${id}`)
    loadProducts()
  }
}

const editProduct = (product) => {
  editForm.value = { ...product }
  editing.value = true
}

const updateProduct = async () => {
  await axios.put(`/api/products/${editForm.value.id}`, editForm.value)
  editing.value = false
  loadProducts()
}

onMounted(loadProducts)
</script>

<style scoped>
table th, table td {
  border-bottom: 1px solid #e5e7eb;
}
</style>
