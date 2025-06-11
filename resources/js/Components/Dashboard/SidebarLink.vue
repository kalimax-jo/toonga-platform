<template>
  <Link
    :href="props.href || '#'"
    :class="[
      'w-full flex items-center gap-3 px-3 py-2 text-left rounded-lg transition-colors',
      props.active
        ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600'
        : 'text-gray-700 hover:bg-gray-50'
    ]"
  >
    <component :is="iconComponent" class="w-5 h-5" />
    <span class="flex-1">{{ props.label }}</span>
    <span 
      v-if="props.count" 
      class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full"
    >
      {{ props.count }}
    </span>
  </Link>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { 
  HomeIcon, 
  ShoppingCartIcon, 
  ArchiveBoxIcon, 
  StarIcon, 
  HeartIcon, 
  UserIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  href: {
    type: String,
    default: '#'
  },
  active: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: 'home'
  },
  label: {
    type: String,
    default: ''
  },
  count: [Number, String]
})

const iconComponent = computed(() => {
  const icons = {
    'home': HomeIcon,
    'shopping-cart': ShoppingCartIcon,
    'package': ArchiveBoxIcon,
    'archive-box': ArchiveBoxIcon,
    'star': StarIcon,
    'heart': HeartIcon,
    'user': UserIcon,
    'bell': BellIcon
  }
  return icons[props.icon] || HomeIcon
})
</script>