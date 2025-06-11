<template>
  <div class="border rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
    <div class="text-3xl mb-2">{{ item.image || getTypeIcon(item.type) }}</div>
    <div class="font-medium mb-1">{{ item.title }}</div>
    <div class="text-sm text-gray-600 mb-2">{{ item.subtitle || getTypeSubtitle(item.type) }}</div>
    <div class="text-lg font-bold text-blue-600 mb-3">{{ formatCurrency(item.price) }}</div>
    <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">
      Add to Cart
    </button>
  </div>
</template>

<script setup>
defineProps({
  item: Object
})

const getTypeIcon = (type) => {
  const icons = {
    flight: '✈️',
    hotel: '🏨',
    car: '🚗',
    electronics: '📱',
    food: '🍕',
    furniture: '🛋️'
  }
  return icons[type] || '📦'
}

const getTypeSubtitle = (type) => {
  const subtitles = {
    flight: 'Flight Deal',
    hotel: 'Hotel Deal',
    car: 'Car Rental',
    electronics: 'Electronics',
    food: 'Food & Drinks',
    furniture: 'Furniture'
  }
  return subtitles[type] || 'Special Offer'
}

const formatCurrency = (amount, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  return `${symbols[currency]}${amount.toLocaleString()}`
}
</script>