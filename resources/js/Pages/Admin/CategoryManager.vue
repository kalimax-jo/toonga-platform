<template>
  <AdminLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Manage Categories</h1>

      <!-- Add New Category -->
      <form @submit.prevent="addCategory" class="space-x-2">
        <input v-model="newCategory.name" class="border p-2 rounded" placeholder="New category name" required />
        <button class="bg-indigo-600 text-white px-4 py-2 rounded">Add</button>
      </form>

      <!-- Category List -->
      <table class="w-full mt-4 bg-white shadow rounded">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-2">Name</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id" class="border-t">
            <td class="p-2">
              <span v-if="editId !== cat.id">{{ cat.name }}</span>
              <input v-else v-model="editCategory.name" class="border p-1" />
            </td>
            <td class="p-2 flex gap-2">
              <button v-if="editId !== cat.id" @click="startEdit(cat)" class="text-blue-600">Edit</button>
              <button v-else @click="updateCategory" class="text-green-600">Save</button>
              <button @click="deleteCategory(cat.id)" class="text-red-600">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const categories = ref([])
const newCategory = ref({ name: '' })
const editCategory = ref({ id: null, name: '' })
const editId = ref(null)

const loadCategories = async () => {
  const res = await axios.get('/api/categories')
  categories.value = res.data
}

const addCategory = async () => {
  await axios.post('/api/categories', newCategory.value)
  newCategory.value.name = ''
  loadCategories()
}

const startEdit = (cat) => {
  editId.value = cat.id
  editCategory.value = { ...cat }
}

const updateCategory = async () => {
  await axios.put(`/api/categories/${editId.value}`, editCategory.value)
  editId.value = null
  loadCategories()
}

const deleteCategory = async (id) => {
  if (confirm('Delete this category?')) {
    await axios.delete(`/api/categories/${id}`)
    loadCategories()
  }
}

onMounted(loadCategories)
</script>
