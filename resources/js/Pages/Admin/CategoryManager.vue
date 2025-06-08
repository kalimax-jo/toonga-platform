<template>
  <AdminLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Manage Categories</h1>

      <!-- Add New Category -->
      <form @submit.prevent="addCategory" class="space-y-4">
        <input
          v-model="newCategory.name"
          class="border p-2 rounded w-full"
          placeholder="New category name"
          required
        />

        <select
          v-model="newCategory.category_type_id"
          class="border p-2 rounded w-full"
          required
        >
          <option disabled value="">Select category type</option>
          <option
            v-for="type in categoryTypes"
            :key="type.id"
            :value="type.id"
          >
            {{ type.name }}
          </option>
        </select>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded">
          Add Category
        </button>
      </form>

      <!-- Category List -->
      <table class="w-full mt-4 bg-white shadow rounded">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-2">Name</th>
            <th class="p-2">Type</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id" class="border-t">
            <td class="p-2">
              <span v-if="editId !== cat.id">{{ cat.name }}</span>
              <input v-else v-model="editCategory.name" class="border p-1" />
            </td>
            <td class="p-2">
              <span v-if="editId !== cat.id">
                {{ cat.type?.name || 'Unknown' }}
              </span>
              <select
                v-else
                v-model="editCategory.category_type_id"
                class="border p-1"
              >
                <option disabled value="">Select type</option>
                <option
                  v-for="type in categoryTypes"
                  :key="type.id"
                  :value="type.id"
                >
                  {{ type.name }}
                </option>
              </select>
            </td>
            <td class="p-2 flex gap-2">
              <button
                v-if="editId !== cat.id"
                @click="startEdit(cat)"
                class="text-blue-600"
              >
                Edit
              </button>
              <button
                v-else
                @click="updateCategory"
                class="text-green-600"
              >
                Save
              </button>
              <button
                @click="deleteCategory(cat.id)"
                class="text-red-600"
              >
                Delete
              </button>
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
const categoryTypes = ref([])
const newCategory = ref({ name: '', category_type_id: '' })
const editCategory = ref({ id: null, name: '', category_type_id: '' })
const editId = ref(null)

const loadCategories = async () => {
  const res = await axios.get('/api/categories') // must return with('type')
  categories.value = res.data
}

const loadCategoryTypes = async () => {
  const res = await axios.get('/api/category-types')
  categoryTypes.value = res.data
}

const addCategory = async () => {
  await axios.post('/api/categories', newCategory.value)
  newCategory.value.name = ''
  newCategory.value.category_type_id = ''
  loadCategories()
}

const startEdit = (cat) => {
  editId.value = cat.id
  editCategory.value = {
    id: cat.id,
    name: cat.name,
    category_type_id: cat.category_type_id
  }
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

onMounted(() => {
  loadCategories()
  loadCategoryTypes()
})
</script>
