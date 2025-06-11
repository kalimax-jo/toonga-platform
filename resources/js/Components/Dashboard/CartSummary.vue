<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
    
    <div class="space-y-3">
      <div class="flex justify-between">
        <span class="text-gray-600">Subtotal</span>
        <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
      </div>
      
      <div v-if="milesDiscount > 0" class="flex justify-between text-green-600">
        <span>Miles Discount</span>
        <span>-{{ formatCurrency(milesDiscount) }}</span>
      </div>
      
      <div v-if="taxes > 0" class="flex justify-between">
        <span class="text-gray-600">Taxes & Fees</span>
        <span class="font-medium">{{ formatCurrency(taxes) }}</span>
      </div>
      
      <hr class="my-4">
      
      <div class="flex justify-between text-lg font-semibold">
        <span>Total</span>
        <span>{{ formatCurrency(total) }}</span>
      </div>
      
      <div v-if="milesEarnable > 0" class="text-sm text-green-600 bg-green-50 p-3 rounded-lg">
        ⭐ You'll earn {{ milesEarnable.toLocaleString() }} miles from this purchase
      </div>
    </div>
    
    <button 
      @click="$emit('checkout')"
      :disabled="!props.itemCount || props.itemCount === 0"
      :class="[
        'w-full mt-6 py-3 rounded-lg font-medium transition-colors',
        props.itemCount > 0 
          ? 'bg-blue-600 text-white hover:bg-blue-700' 
          : 'bg-gray-300 text-gray-500 cursor-not-allowed'
      ]"
    >
      {{ props.itemCount > 0 ? 'Proceed to Checkout' : 'No Items to Checkout' }}
    </button>
    
    <div class="mt-4 text-center">
      <Link href="/flights" class="text-blue-600 hover:text-blue-800 text-sm">
        Continue Shopping
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  subtotal: Number,
  milesDiscount: Number,
  taxes: Number,
  total: Number,
  milesEarnable: Number,
  itemCount: {
    type: Number,
    default: 0
  }
})

defineEmits(['checkout'])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-RW', {
    style: 'currency',
    currency: 'RWF',
    minimumFractionDigits: 0
  }).format(amount)
}
</script>