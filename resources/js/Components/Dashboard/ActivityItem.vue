<template>
  <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
    <div class="text-2xl">
      {{ getOrderIcon(order.airline_code) }}
    </div>
    <div class="flex-1">
      <div class="font-medium">{{ order.origin_city }} to {{ order.destination_city }}</div>
      <div class="text-sm text-gray-600">
        {{ formatDate(order.created_at) }} • {{ formatCurrency(order.total_price_local, order.currency_used) }}
      </div>
    </div>
    <div :class="getStatusClass(order.status)">
      {{ order.status }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: Object
})

const getOrderIcon = (airlineCode) => {
  const icons = {
    'KQ': '✈️',
    'WB': '✈️',
    'ET': '✈️',
    'QR': '✈️'
  }
  return icons[airlineCode] || '✈️'
}

const getStatusClass = (status) => {
  const classes = {
    confirmed: 'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
    pending: 'px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    cancelled: 'px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800',
    completed: 'px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800'
  }
  return classes[status] || classes.pending
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  return `${symbols[currency]}${amount.toLocaleString()}`
}
</script>