<template>
  <PublicLayout>
    <div class="max-w-3xl mx-auto py-10 px-6 bg-white rounded shadow">
      <h2 class="text-2xl font-bold mb-4">✈️ Confirm Your Flight Booking</h2>

      <div v-if="flight">
        <div class="border rounded p-4 bg-gray-50 mb-6">
          <p class="text-lg font-semibold mb-2">
            {{ flight.carrier }} — {{ flight.tripType?.toUpperCase() ?? 'One Way' }}
          </p>

          <p class="text-sm text-gray-600">
            <strong>From:</strong> {{ flight?.itineraries?.[0]?.segments?.[0]?.departure?.iataCode ?? 'N/A' }}
            →
            <strong>To:</strong> {{ flight?.itineraries?.[0]?.segments?.[0]?.arrival?.iataCode ?? 'N/A' }}
          </p>

          <p class="text-sm text-gray-600">
            <strong>Departure:</strong>
            {{ formatDateTime(flight?.itineraries?.[0]?.segments?.[0]?.departure?.at) }}
          </p>

          <div v-if="flight.tripType === 'round' && flight.itineraries?.length > 1">
            <p class="text-sm text-gray-600">
              <strong>Return:</strong>
              {{ formatDateTime(flight?.itineraries?.[1]?.segments?.[0]?.departure?.at) }}
            </p>
          </div>

          <p class="mt-2 text-indigo-600 font-bold">
            Price: {{ formattedPrice }}
          </p>

          <p class="text-sm text-gray-500 italic">
            * This price will be charged in RWF (converted from EUR if applicable)
          </p>
        </div>

        <button
          @click="goToPayment"
          class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700"
        >
          💳 Proceed to Payment
        </button>
      </div>

      <div v-else class="text-center text-gray-600">
        No flight in cart. Please go back and search again.
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const { props } = usePage()
const flight = props.flight

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString()
}

const formattedPrice = computed(() => {
  if (!flight) return 'N/A'
  const currency = flight.convertedCurrency || flight.currency || 'EUR'
  const price = flight.convertedPrice || flight.price || '0'
  return new Intl.NumberFormat('rw-RW', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0
  }).format(parseFloat(price))
})

const goToPayment = () => {
  window.location.href = '/flights/pay'
}
</script>
