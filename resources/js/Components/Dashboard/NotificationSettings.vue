<template>
  <div class="space-y-4">
    <h4 class="font-medium text-gray-900">Email Notifications</h4>
    
    <div class="space-y-3">
      <NotificationToggle 
        v-model="localPreferences.orderUpdates"
        title="Order Updates"
        description="Get notified about your bookings and orders"
      />
      
      <NotificationToggle 
        v-model="localPreferences.priceAlerts"
        title="Price Alerts"
        description="Receive alerts when prices drop on your wishlist items"
      />
      
      <NotificationToggle 
        v-model="localPreferences.milesUpdates"
        title="Miles Updates"
        description="Stay informed about your miles balance and rewards"
      />
      
      <NotificationToggle 
        v-model="localPreferences.promotionalOffers"
        title="Promotional Offers"
        description="Receive special deals and offers"
      />
    </div>

    <hr class="my-6">

    <h4 class="font-medium text-gray-900">Communication Preferences</h4>
    
    <div class="space-y-3">
      <NotificationToggle 
        v-model="localPreferences.emailNotifications"
        title="Email Notifications"
        description="Receive notifications via email"
      />
      
      <NotificationToggle 
        v-model="localPreferences.smsNotifications"
        title="SMS Notifications"
        description="Receive important updates via SMS"
      />
      
      <NotificationToggle 
        v-model="localPreferences.pushNotifications"
        title="Push Notifications"
        description="Receive browser push notifications"
      />
    </div>

    <div class="flex justify-end pt-4">
      <button 
        @click="savePreferences"
        :disabled="!hasChanges"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        Save Preferences
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import NotificationToggle from './NotificationToggle.vue'

const props = defineProps({
  preferences: Object
})

const emit = defineEmits(['update'])

const localPreferences = ref({ ...props.preferences })
const originalPreferences = ref({ ...props.preferences })

const hasChanges = computed(() => {
  return JSON.stringify(localPreferences.value) !== JSON.stringify(originalPreferences.value)
})

const savePreferences = () => {
  emit('update', localPreferences.value)
  originalPreferences.value = { ...localPreferences.value }
}

watch(() => props.preferences, (newPreferences) => {
  localPreferences.value = { ...newPreferences }
  originalPreferences.value = { ...newPreferences }
}, { deep: true })
</script>