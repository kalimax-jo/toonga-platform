<template>
  <AdminLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Manage Vendors</h1>

      <!-- Add New Vendor Form -->
      <form @submit.prevent="createVendor" class="bg-white p-4 shadow rounded space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <input v-model="form.name" placeholder="Name" class="border p-2 rounded" required />
          <input v-model="form.email" type="email" placeholder="Email" class="border p-2 rounded" required />
          <input v-model="form.password" type="password" placeholder="Password" class="border p-2 rounded" required />
          <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="border p-2 rounded" required />
          <input v-model="form.subaccount_id" placeholder="Flutterwave Subaccount ID" class="border p-2 rounded col-span-2" required />

          <!-- Category checkboxes -->
          <div class="col-span-2">
            <label class="font-semibold">Assign Categories</label>
            <div class="flex flex-wrap gap-3 mt-2">
              <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2">
                <input type="checkbox" :value="cat.id" v-model="form.categories" />
                {{ cat.name }}
              </label>
            </div>
          </div>
        </div>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded">Add Vendor</button>
      </form>

      <!-- Vendor List Table -->
      <div class="bg-white shadow rounded">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-100 text-left">
              <th class="p-2">Name</th>
              <th class="p-2">Email</th>
              <th class="p-2">Status</th>
              <th class="p-2">Categories</th>
              <th class="p-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="vendor in vendors" :key="vendor.id" class="border-t">
              <td class="p-2">{{ vendor.name }}</td>
              <td class="p-2">{{ vendor.email }}</td>
              <td class="p-2">{{ vendor.is_approved ? 'Approved' : 'Pending' }}</td>
              <td class="p-2">
                <ul class="list-disc list-inside text-sm">
                  <li v-for="cat in vendor.categories" :key="cat.id">{{ cat.name }}</li>
                </ul>
              </td>
              <td class="p-2 flex gap-2">
                <button @click="editVendor(vendor)" class="text-blue-600">Edit</button>
                <button @click="deleteVendor(vendor.id)" class="text-red-600">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Edit Modal -->
      <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-full max-w-md">
          <h2 class="text-lg font-bold mb-4">Edit Vendor</h2>
          <input v-model="editForm.name" placeholder="Name" class="border p-2 w-full mb-2" />
          <input v-model="editForm.email" type="email" placeholder="Email" class="border p-2 w-full mb-2" />
          <input v-model="editForm.subaccount_id" placeholder="Flutterwave Subaccount ID" class="border p-2 w-full mb-2" />
          <label class="block font-semibold mb-1">Categories</label>
          <div class="flex flex-wrap gap-2 mb-4">
            <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2">
              <input type="checkbox" :value="cat.id" v-model="editForm.categories" />
              {{ cat.name }}
            </label>
          </div>
          <div class="flex justify-end gap-2">
            <button @click="updateVendor" class="bg-indigo-600 text-white px-4 py-2 rounded">Update</button>
            <button @click="cancelEdit" class="text-gray-500">Cancel</button>
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

const vendors = ref([])
const categories = ref([])

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  subaccount_id: '',
  categories: []
})

const editForm = ref({
  id: null,
  name: '',
  email: '',
  subaccount_id: '',
  categories: []
})

const editing = ref(false)

const loadVendors = async () => {
  const res = await axios.get('/api/vendors')
  vendors.value = res.data
}

const loadCategories = async () => {
  const res = await axios.get('/api/categories')
  categories.value = res.data
}

const createVendor = async () => {
  try {
    await axios.post('/api/vendors', form.value)
    form.value = {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      subaccount_id: '',
      categories: []
    }
    loadVendors()
  } catch (error) {
    console.error('Vendor creation failed:', error.response?.data || error)
    alert('Failed to create vendor. Check console for details.')
  }
}

const deleteVendor = async (id) => {
  if (confirm('Delete this vendor?')) {
    await axios.delete(`/api/vendors/${id}`)
    loadVendors()
  }
}

const editVendor = (vendor) => {
  editForm.value = {
    id: vendor.id,
    name: vendor.name,
    email: vendor.email,
    subaccount_id: vendor.subaccount_id,
    categories: vendor.categories.map(c => c.id)
  }
  editing.value = true
}

const updateVendor = async () => {
  await axios.put(`/api/vendors/${editForm.value.id}`, editForm.value)
  editing.value = false
  loadVendors()
}

const cancelEdit = () => {
  editing.value = false
}

onMounted(() => {
  loadVendors()
  loadCategories()
})
</script>

<style scoped>
table th, table td {
  border-bottom: 1px solid #e5e7eb;
}
</style>
