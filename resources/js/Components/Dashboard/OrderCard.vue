<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-start mb-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Order #{{ order.id }}</h3>
        <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="getStatusClass(order.status)">
          {{ getStatusLabel(order.status) }}
        </span>
        <div class="text-right">
          <div class="text-lg font-bold text-gray-900">
            {{ formatCurrency(order.total_amount, order.currency_used) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Flight Details -->
    <div v-if="order.segments && order.segments.length > 0" class="space-y-3 mb-4">
      <div v-for="(segment, index) in order.segments" :key="index" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
        <div class="text-2xl">✈️</div>
        <div class="flex-1">
          <div class="font-medium">
            {{ segment.departure_airport }} → {{ segment.arrival_airport }}
          </div>
          <div class="text-sm text-gray-600">
            {{ formatDateTime(segment.departure_time) }} - {{ formatDateTime(segment.arrival_time) }}
          </div>
          <div v-if="segment.airline_name" class="text-sm text-gray-500">
            {{ segment.airline_name }} • {{ segment.flight_number }}
          </div>
        </div>
      </div>
    </div>

    <!-- Passengers -->
    <div v-if="order.passengers && order.passengers.length > 0" class="mb-4">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Passengers</h4>
      <div class="space-y-1">
        <div v-for="passenger in order.passengers" :key="passenger.id" class="text-sm text-gray-600">
          {{ passenger.title }} {{ passenger.first_name }} {{ passenger.last_name }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center pt-4 border-t">
      <div class="text-sm text-gray-500">
        Booking Reference: {{ order.booking_reference || 'N/A' }}
      </div>
      <div class="flex gap-2">
        <button 
          @click="$emit('view-details', order.id)"
          class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
        >
          View Details
        </button>
        <button 
          v-if="canCancel(order.status)"
          @click="$emit('cancel-order', order.id)"
          class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  order: Object
})

defineEmits(['view-details', 'cancel-order'])

const getStatusClass = (status) => {
  const classes = {
    confirmed: 'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
    pending: 'px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    cancelled: 'px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800',
    completed: 'px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800'
  }
  return classes[status] || classes.pending
}

const getStatusLabel = (status) => {
  const labels = {
    confirmed: 'Confirmed',
    pending: 'Pending',
    cancelled: 'Cancelled',
    completed: 'Completed'
  }
  return labels[status] || status
}

const canCancel = (status) => {
  return ['pending', 'confirmed'].includes(status)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A'
  return new Date(dateTimeString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (amount, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  return `${symbols[currency] || currency} ${amount?.toLocaleString() || 0}`
}
</script>