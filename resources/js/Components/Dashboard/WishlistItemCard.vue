<template>
  <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
    <div class="relative">
      <img 
        :src="item.image || getDefaultImage(item.type)" 
        :alt="item.title"
        class="w-full h-48 object-cover rounded-t-lg"
      >
      <button 
        @click="$emit('remove', item.id)"
        class="absolute top-2 right-2 p-2 bg-white rounded-full shadow hover:bg-red-50 hover:text-red-600"
      >
        <HeartIcon class="w-5 h-5 text-red-500 fill-current" />
      </button>
    </div>
    
    <div class="p-4">
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-semibold text-gray-900 line-clamp-2">{{ item.title }}</h3>
        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full ml-2">
          {{ getTypeLabel(item.type) }}
        </span>
      </div>
      
      <p v-if="item.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
        {{ item.description }}
      </p>
      
      <div class="flex items-center justify-between mb-4">
        <div class="text-lg font-bold text-blue-600">
          {{ formatPrice(item.price, item.currency) }}
        </div>
        <div v-if="item.originalPrice && item.originalPrice > item.price" class="text-sm text-gray-500 line-through">
          {{ formatPrice(item.originalPrice, item.currency) }}
        </div>
      </div>
      
      <div class="flex gap-2">
        <button 
          @click="$emit('add-to-cart', item)"
          class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
        >
          Add to Cart
        </button>
        <Link 
          :href="item.url || '#'"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium"
        >
          View
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { HeartIcon } from '@heroicons/vue/24/outline'

defineProps({
  item: Object
})

defineEmits(['remove', 'add-to-cart'])

const getTypeLabel = (type) => {
  const labels = {
    flight: '✈️ Flight',
    hotel: '🏨 Hotel',
    car: '🚗 Car',
    product: '📱 Product',
    food: '🍕 Food'
  }
  return labels[type] || type
}

const getDefaultImage = (type) => {
  const images = {
    flight: 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=400',
    hotel: 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=400',
    car: 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=400',
    product: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
    food: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400'
  }
  return images[type] || images.product
}

const conversionRates = { USD: 1250, EUR: 1150 }
const formatPrice = (price, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  if (currency !== 'RWF') {
    const rate = conversionRates[currency] || 1
    price = price * rate
    currency = 'RWF'
  }
  return `${symbols[currency] || currency} ${Math.round(price).toLocaleString()}`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>