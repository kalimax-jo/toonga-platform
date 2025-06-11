<template>
  <DashboardLayout :user="user || $page.props.auth?.user" :stats="stats">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Shopping Cart ({{ cartItems.length }} items)</h1>
        <div class="flex gap-2">
          <button 
            v-if="expiredItems.length > 0"
            @click="removeExpiredItems"
            class="text-orange-600 hover:text-orange-800 text-sm"
          >
            Remove Expired ({{ expiredItems.length }})
          </button>
          <button 
            @click="clearCart"
            class="text-red-600 hover:text-red-800 text-sm"
          >
            Clear All
          </button>
        </div>
      </div>

      <!-- Expired Items Warning -->
      <div v-if="expiredItems.length > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex items-center gap-2 text-orange-800">
          <ExclamationTriangleIcon class="w-5 h-5" />
          <span class="font-medium">{{ expiredItems.length }} item(s) have expired</span>
        </div>
        <p class="text-sm text-orange-700 mt-1">
          These bookings are no longer available for payment. Please book again if needed.
        </p>
      </div>

      <div v-if="activeItems.length === 0 && expiredItems.length === 0" class="text-center py-12">
        <ShoppingCartIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
        <p class="text-gray-500 mb-4">Start shopping to add items to your cart</p>
        <Link href="/flights" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Browse Flights
        </Link>
      </div>

      <div v-else class="space-y-6">
        <!-- Active Items by Category -->
        <div class="space-y-6">
          <!-- Flights Section -->
          <CartSection 
            v-if="activeFlightItems.length"
            title="✈️ Flight Bookings"
            :items="activeFlightItems"
            @remove="removeFromCart"
            @update="updateCart"
          />

          <!-- Hotels Section -->
          <CartSection 
            v-if="activeHotelItems.length"
            title="🏨 Hotel Bookings"
            :items="activeHotelItems"
            @remove="removeFromCart"
            @update="updateCart"
          />

          <!-- Products Section -->
          <CartSection 
            v-if="activeProductItems.length"
            title="📱 Products"
            :items="activeProductItems"
            @remove="removeFromCart"
            @update="updateCart"
          />

          <!-- Expired Items Section -->
          <div v-if="expiredItems.length > 0" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-600">⏰ Expired Items</h3>
            <div class="space-y-4">
              <ExpiredCartItem 
                v-for="item in expiredItems" 
                :key="item.id"
                :item="item"
                @remove="removeFromCart"
                @rebook="rebookItem"
              />
            </div>
          </div>
        </div>

        <!-- Cart Summary -->
        <CartSummary 
          v-if="activeItems.length > 0"
          :subtotal="subtotal"
          :milesDiscount="milesDiscount"
          :taxes="taxes"
          :total="total"
          :milesEarnable="milesEarnable"
          :itemCount="activeItems.length"
          @checkout="proceedToCheckout"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ShoppingCartIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import CartSection from '@/Components/Dashboard/CartSection.vue'
import CartSummary from '@/Components/Dashboard/CartSummary.vue'
import ExpiredCartItem from '@/Components/Dashboard/ExpiredCartItem.vue'

const props = defineProps({
  user: Object,
  cartItems: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const cartItems = ref(props.cartItems)

// Separate active and expired items
const activeItems = computed(() => cartItems.value.filter(item => !item.is_expired))
const expiredItems = computed(() => cartItems.value.filter(item => item.is_expired))

// Filter by type
const activeFlightItems = computed(() => activeItems.value.filter(item => item.type === 'flight'))
const activeHotelItems = computed(() => activeItems.value.filter(item => item.type === 'hotel'))
const activeProductItems = computed(() => activeItems.value.filter(item => item.type === 'product'))

// Calculate totals (only for active items)
const subtotal = computed(() => {
  return activeItems.value.reduce((total, item) => {
    return total + (item.price * (item.quantity || 1))
  }, 0)
})

const milesDiscount = computed(() => 0) // Implement miles discount logic
const taxes = computed(() => {
  // Calculate taxes as 18% of subtotal for flights
  const flightTotal = activeFlightItems.value.reduce((total, item) => {
    return total + (item.price * (item.quantity || 1))
  }, 0)
  return Math.round(flightTotal * 0.18) // 18% tax on flights
})

const total = computed(() => subtotal.value - milesDiscount.value + taxes.value)
const milesEarnable = computed(() => Math.floor(total.value / 1000)) // 1 mile per 1000 currency units

const removeFromCart = (itemId) => {
  const item = cartItems.value.find(item => item.id === itemId)
  
  if (item?.type === 'flight' && item.booking_id) {
    // Cancel flight booking
    router.delete(`/bookings/${item.booking_id}`, {
      preserveScroll: true,
      onSuccess: () => {
        cartItems.value = cartItems.value.filter(item => item.id !== itemId)
        // Show success message
      },
      onError: (errors) => {
        console.error('Failed to cancel booking:', errors)
      }
    })
  } else {
    // Remove other item types
    cartItems.value = cartItems.value.filter(item => item.id !== itemId)
    // API call to update cart for other items
  }
}

const updateCart = (itemId, updates) => {
  const itemIndex = cartItems.value.findIndex(item => item.id === itemId)
  if (itemIndex !== -1) {
    cartItems.value[itemIndex] = { ...cartItems.value[itemIndex], ...updates }
  }
  // API call to update cart
}

const removeExpiredItems = () => {
  const expiredIds = expiredItems.value.map(item => item.id)
  expiredIds.forEach(id => removeFromCart(id))
}

const clearCart = () => {
  if (confirm('Are you sure you want to clear your entire cart? This will cancel all unpaid bookings.')) {
    router.delete('/cart/clear', {
      preserveScroll: true,
      onSuccess: () => {
        cartItems.value = []
      }
    })
  }
}

const rebookItem = (item) => {
  if (item.type === 'flight') {
    // Redirect to flight search with the same route
    const departure = item.details?.departure
    const arrival = item.details?.arrival
    router.visit(`/flights?from=${departure}&to=${arrival}`)
  }
}

const proceedToCheckout = () => {
  if (activeItems.value.length === 0) {
    alert('No active items to checkout')
    return
  }

  router.post('/checkout', {
    items: activeItems.value.map(item => ({
      id: item.id,
      type: item.type,
      booking_id: item.booking_id,
      amount: item.price * (item.quantity || 1)
    })),
    total: total.value,
    subtotal: subtotal.value,
    taxes: taxes.value,
    miles_discount: milesDiscount.value
  })
}
</script>