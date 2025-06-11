<template>
  <DashboardLayout :user="$page.props.auth?.user || user">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">My Wishlist ({{ wishlistItems.length }} items)</h1>
        <button 
          v-if="wishlistItems.length > 0"
          @click="clearWishlist"
          class="text-red-600 hover:text-red-800 text-sm"
        >
          Clear All
        </button>
      </div>

      <div v-if="wishlistItems.length === 0" class="text-center py-12">
        <HeartIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Your wishlist is empty</h3>
        <p class="text-gray-500 mb-4">Save items you're interested in to your wishlist</p>
        <Link href="/flights" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Browse Flights
        </Link>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <WishlistItemCard 
          v-for="item in wishlistItems" 
          :key="item.id"
          :item="item"
          @remove="removeFromWishlist"
          @add-to-cart="addToCart"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { HeartIcon } from '@heroicons/vue/24/outline'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import WishlistItemCard from '@/Components/Dashboard/WishlistItemCard.vue'

const props = defineProps({
  user: Object,
  wishlistItems: {
    type: Array,
    default: () => []
  }
})

const wishlistItems = ref(props.wishlistItems)

const removeFromWishlist = (itemId) => {
  wishlistItems.value = wishlistItems.value.filter(item => item.id !== itemId)
  // API call to remove from wishlist
  router.delete(`/wishlist/${itemId}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handling
    }
  })
}

const addToCart = (item) => {
  // API call to add to cart
  router.post('/cart', {
    item_id: item.id,
    type: item.type
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Show success message
    }
  })
}

const clearWishlist = () => {
  if (confirm('Are you sure you want to clear your entire wishlist?')) {
    wishlistItems.value = []
    router.delete('/wishlist/clear', {
      preserveScroll: true
    })
  }
}
</script>