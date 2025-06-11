<template>
  <div class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
    <div class="text-2xl">
      {{ getActivityIcon(activity.type) }}
    </div>
    <div class="flex-1">
      <p class="font-medium">{{ activity.description }}</p>
      <p class="text-sm text-gray-500">{{ formatDate(activity.created_at) }}</p>
      <p v-if="activity.reference" class="text-xs text-gray-400">Ref: {{ activity.reference }}</p>
    </div>
    <div :class="getAmountClass(activity.type)">
      {{ activity.type === 'earned' ? '+' : '-' }}{{ activity.amount.toLocaleString() }} miles
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  activity: Object
})

const getActivityIcon = (type) => {
  const icons = {
    earned: '📈',
    redeemed: '💰',
    expired: '⏰',
    transferred: '🎁'
  }
  return icons[type] || '📊'
}

const getAmountClass = (type) => {
  const classes = {
    earned: 'font-bold text-green-600',
    redeemed: 'font-bold text-red-600',
    expired: 'font-bold text-gray-500',
    transferred: 'font-bold text-blue-600'
  }
  return classes[type] || 'font-bold text-gray-600'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>