<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Vendor Approval Requests</h1>

      <div v-if="vendors.length === 0" class="text-gray-600">
        No pending vendors to review.
      </div>

      <table v-else class="w-full table-auto bg-white shadow rounded">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Status</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="vendor in vendors" :key="vendor.id" class="border-t">
            <td class="p-2">{{ vendor.name }}</td>
            <td class="p-2">{{ vendor.email }}</td>
            <td class="p-2 text-red-500 font-semibold">Pending</td>
            <td class="p-2">
              <button @click="approve(vendor.id)" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                Approve
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

const vendors = ref([])

const loadPending = async () => {
  const res = await axios.get('/api/vendors')
  vendors.value = res.data.filter(v => v.is_approved === false)
  console.log('Filtered pending vendors:', vendors.value)
}

const approve = async (id) => {
  await axios.post(`/admin/approve-vendor/${id}`)
  await loadPending()
}

onMounted(loadPending)
</script>
