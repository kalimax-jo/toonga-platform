<template>
  <form @submit.prevent="submitForm" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
        <input 
          v-model="form.firstName" 
          type="text" 
          class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          required
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
        <input 
          v-model="form.lastName" 
          type="text" 
          class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          required
        />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input 
        v-model="form.email" 
        type="email" 
        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        required
      />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input 
          v-model="form.phone" 
          type="tel" 
          class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
        <input 
          v-model="form.dateOfBirth" 
          type="date" 
          class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
      <textarea 
        v-model="form.address" 
        rows="3"
        class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        placeholder="Enter your full address"
      ></textarea>
    </div>

    <div class="flex justify-end gap-3">
      <button 
        type="button" 
        @click="resetForm"
        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
      >
        Reset
      </button>
      <button 
        type="submit" 
        :disabled="processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ processing ? 'Saving...' : 'Save Changes' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  user: Object
})

const emit = defineEmits(['updated'])

const processing = ref(false)

const form = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  dateOfBirth: '',
  address: ''
})

// Initialize form with user data
watch(() => props.user, (newUser) => {
  if (newUser) {
    const nameParts = newUser.name.split(' ')
    form.value = {
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: newUser.email || '',
      phone: newUser.phone || '',
      dateOfBirth: newUser.date_of_birth || '',
      address: newUser.address || ''
    }
  }
}, { immediate: true })

const submitForm = async () => {
  processing.value = true
  try {
    emit('updated', {
      name: `${form.value.firstName} ${form.value.lastName}`.trim(),
      email: form.value.email,
      phone: form.value.phone,
      date_of_birth: form.value.dateOfBirth,
      address: form.value.address
    })
  } finally {
    processing.value = false
  }
}

const resetForm = () => {
  // Reset to original user data
  if (props.user) {
    const nameParts = props.user.name.split(' ')
    form.value = {
      firstName: nameParts[0] || '',
      lastName: nameParts.slice(1).join(' ') || '',
      email: props.user.email || '',
      phone: props.user.phone || '',
      dateOfBirth: props.user.date_of_birth || '',
      address: props.user.address || ''
    }
  }
}
</script>