<template>
  <div class="flex items-center gap-4 p-4 border rounded-lg">
    <div class="text-3xl">{{ getItemIcon(item.type) }}</div>
    
    <div class="flex-1">
      <h4 class="font-medium">{{ item.title }}</h4>
      <p class="text-sm text-gray-600">{{ getItemSubtitle(item) }}</p>
      <p v-if="item.date" class="text-sm text-gray-600">{{ formatDate(item.date) }}</p>
    </div>

    <!-- Quantity Controls for Products -->
    <div v-if="item.type === 'product'" class="flex items-center gap-2">
      <button 
        @click="updateQuantity(-1)"
        class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-gray-100"
      >
        <MinusIcon class="w-4 h-4" />
      </button>
      <span class="w-8 text-center">{{ item.quantity || 1 }}</span>
      <button 
        @click="updateQuantity(1)"
        class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-gray-100"
      >
        <PlusIcon class="w-4 h-4" />
      </button>
    </div>

    <div class="text-right">
      <div class="text-lg font-bold">{{ formatCurrency(getTotalPrice()) }}</div>
      <div class="text-sm text-green-600">+{{ Math.floor(getTotalPrice()/1000) }} miles</div>
    </div>

    <button 
      @click="$emit('remove')"
      class="text-red-500 hover:text-red-700"
    >
      <XMarkIcon class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup>
import { MinusIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  item: Object
})

const emit = defineEmits(['remove', 'update'])

const getItemIcon = (type) => {
  const icons = {
    flight: '✈️',
    hotel: '🏨',
    product: '📱',
    car: '🚗',
    food: '🍕',
    furniture: '🛋️'
  }
  return icons[type] || '📦'
}

const getItemSubtitle = (item) => {
  if (item.type === 'flight') return item.airline || 'Flight'
  if (item.type === 'hotel') return item.location || 'Hotel'
  if (item.type === 'product') return item.brand || item.category || 'Product'
  return ''
}

const getTotalPrice = () => {
  return props.item.price * (props.item.quantity || 1)
}

const updateQuantity = (change) => {
  const newQuantity = Math.max(1, (props.item.quantity || 1) + change)
  emit('update', { quantity: newQuantity })
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const conversionRates = { USD: 1250, EUR: 1150 }
const formatCurrency = (amount, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  if (currency !== 'RWF') {
    const rate = conversionRates[currency] || 1
    amount = amount * rate
    currency = 'RWF'
  }
  return `${symbols[currency]}${Math.round(amount).toLocaleString()}`
}
</script>