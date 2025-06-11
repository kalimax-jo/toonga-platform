<template>
  <DashboardLayout :user="user || $page.props.auth?.user">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-900">Profile & Settings</h1>
        <p class="text-gray-600 mt-1">Manage your account information and preferences</p>
      </div>

      <!-- Profile Information -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
          <h2 class="text-lg font-semibold">Personal Information</h2>
        </div>
        <form @submit.prevent="updateProfile" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
              <input 
                v-model="form.firstName"
                type="text" 
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :placeholder="currentUser?.name?.split(' ')[0] || 'First Name'"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input 
                v-model="form.lastName"
                type="text" 
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :placeholder="currentUser?.name?.split(' ')[1] || 'Last Name'"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input 
              v-model="form.email"
              type="email" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :placeholder="currentUser?.email || 'Email Address'"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <input 
              v-model="form.phone"
              type="tel" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Phone Number"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
            <input 
              v-model="form.dateOfBirth"
              type="date" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <div class="flex justify-end">
            <button 
              type="submit"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :disabled="isUpdating"
            >
              {{ isUpdating ? 'Updating...' : 'Update Profile' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Password Change -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
          <h2 class="text-lg font-semibold">Change Password</h2>
        </div>
        <form @submit.prevent="updatePassword" class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
            <input 
              v-model="passwordForm.currentPassword"
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter current password"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <input 
              v-model="passwordForm.newPassword"
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter new password"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
            <input 
              v-model="passwordForm.confirmPassword"
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Confirm new password"
            >
          </div>

          <div class="flex justify-end">
            <button 
              type="submit"
              class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
              :disabled="isUpdatingPassword"
            >
              {{ isUpdatingPassword ? 'Updating...' : 'Change Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Preferences -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
          <h2 class="text-lg font-semibold">Preferences</h2>
        </div>
        <div class="p-6 space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-medium">Email Notifications</h3>
              <p class="text-sm text-gray-600">Receive updates about your bookings and offers</p>
            </div>
            <input 
              v-model="preferences.emailNotifications"
              type="checkbox" 
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-medium">SMS Notifications</h3>
              <p class="text-sm text-gray-600">Get text messages for booking confirmations</p>
            </div>
            <input 
              v-model="preferences.smsNotifications"
              type="checkbox" 
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            >
          </div>

          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-medium">Marketing Communications</h3>
              <p class="text-sm text-gray-600">Receive promotional offers and travel deals</p>
            </div>
            <input 
              v-model="preferences.marketingEmails"
              type="checkbox" 
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Currency</label>
            <select 
              v-model="preferences.currency"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="RWF">Rwandan Franc (RWF)</option>
              <option value="USD">US Dollar (USD)</option>
              <option value="EUR">Euro (EUR)</option>
            </select>
          </div>

          <div class="flex justify-end">
            <button 
              @click="updatePreferences"
              class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              :disabled="isUpdatingPreferences"
            >
              {{ isUpdatingPreferences ? 'Saving...' : 'Save Preferences' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Account Actions -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
          <h2 class="text-lg font-semibold">Account Actions</h2>
        </div>
        <div class="p-6 space-y-4">
          <button 
            @click="downloadData"
            class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Download My Data
          </button>
          
          <button 
            @click="deleteAccount"
            class="w-full md:w-auto bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 md:ml-4"
          >
            Delete Account
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const currentUser = computed(() => props.user || window?.$page?.props?.auth?.user)

const isUpdating = ref(false)
const isUpdatingPassword = ref(false)
const isUpdatingPreferences = ref(false)

const form = ref({
  firstName: currentUser.value?.name?.split(' ')[0] || '',
  lastName: currentUser.value?.name?.split(' ')[1] || '',
  email: currentUser.value?.email || '',
  phone: '',
  dateOfBirth: ''
})

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const preferences = ref({
  emailNotifications: true,
  smsNotifications: false,
  marketingEmails: true,
  currency: 'RWF'
})

const updateProfile = async () => {
  isUpdating.value = true
  try {
    await router.patch('/profile', form.value, {
      preserveScroll: true,
      onSuccess: () => {
        // Show success message
        alert('Profile updated successfully!')
      },
      onError: (errors) => {
        console.error('Profile update failed:', errors)
      }
    })
  } finally {
    isUpdating.value = false
  }
}

const updatePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert('New passwords do not match!')
    return
  }

  isUpdatingPassword.value = true
  try {
    await router.patch('/profile/password', passwordForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        passwordForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
        alert('Password updated successfully!')
      },
      onError: (errors) => {
        console.error('Password update failed:', errors)
      }
    })
  } finally {
    isUpdatingPassword.value = false
  }
}

const updatePreferences = async () => {
  isUpdatingPreferences.value = true
  try {
    await router.patch('/profile/preferences', preferences.value, {
      preserveScroll: true,
      onSuccess: () => {
        alert('Preferences saved successfully!')
      }
    })
  } finally {
    isUpdatingPreferences.value = false
  }
}

const downloadData = () => {
  window.open('/profile/download-data', '_blank')
}

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    router.delete('/profile', {
      onSuccess: () => {
        window.location.href = '/'
      }
    })
  }
}
</script>