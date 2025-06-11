<template>
  <div class="bg-white border border-gray-200 rounded-lg p-4 opacity-75">
    <div class="flex items-start gap-4">
      <div class="text-3xl opacity-50">
        {{ getItemIcon(item.type) }}
      </div>
      
      <div class="flex-1">
        <div class="flex items-start justify-between">
          <div>
            <h4 class="font-medium text-gray-600 line-through">{{ item.title }}</h4>
            <p class="text-sm text-gray-500 mt-1">{{ item.description }}</p>
            
            <!-- Flight specific details -->
            <div v-if="item.type === 'flight' && item.details" class="mt-2 text-sm text-gray-500">
              <div class="flex flex-wrap gap-4">
                <span v-if="item.details.departure_time">
                  📅 {{ formatDate(item.details.departure_time) }}
                </span>
                <span v-if="item.details.passengers">
                  👥 {{ item.details.passengers }} passenger{{ item.details.passengers > 1 ? 's' : '' }}
                </span>
                <span v-if="item.details.airline">
                  ✈️ {{ item.details.airline }}
                </span>
              </div>
            </div>
            
            <div class="mt-2 text-xs text-red-600 font-medium">
              ⏰ Expired {{ getTimeAgo(item.expires_at) }}
            </div>
          </div>
          
          <div class="text-right">
            <div class="text-lg font-bold text-gray-500 line-through">
              {{ formatCurrency(item.price, item.currency) }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="flex justify-between items-center mt-4 pt-4 border-t">
      <div class="text-sm text-gray-500">
        This {{ item.type }} booking has expired and is no longer available for payment.
      </div>
      
      <div class="flex gap-2">
        <button 
          @click="$emit('rebook', item)"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Book Again
        </button>
        <button 
          @click="$emit('remove', item.id)"
          class="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded hover:bg-gray-50"
        >
          Remove
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: Object
})

defineEmits(['remove', 'rebook'])

const getItemIcon = (type) => {
  const icons = {
    flight: '✈️',
    hotel: '🏨',
    car: '🚗',
    product: '📱',
    food: '🍕'
  }
  return icons[type] || '📦'
}

const formatCurrency = (amount, currency = 'RWF') => {
  const symbols = { RWF: 'Rwf', USD: '$', EUR: '€' }
  return `${symbols[currency] || currency} ${amount?.toLocaleString() || 0}`
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getTimeAgo = (dateString) => {
  if (!dateString) return 'recently'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) return 'less than 1 hour ago'
  if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`
  
  const diffInDays = Math.floor(diffInHours / 24)
  return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`
}
</script>