<template>
  <DashboardLayout :user="user" :stats="stats" :milesData="milesData">
    <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg">
        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ user.name }}! 👋</h1>
        <p class="text-blue-100 mb-4">Your journey to amazing experiences continues...</p>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatsCard 
            :value="stats.totalOrders" 
            label="Total Orders" 
          />
          <StatsCard 
            :value="stats.availableMiles?.toLocaleString()" 
            label="Available Miles" 
          />
          <StatsCard 
            :value="stats.cartItems" 
            label="Cart Items" 
          />
          <StatsCard 
            :value="stats.activeBookings" 
            label="Active Bookings" 
          />
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <QuickActionCard 
          v-for="action in quickActions" 
          :key="action.title"
          :icon="action.icon"
          :title="action.title"
          :route="action.route"
          :color="action.color"
        />
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
        <div class="space-y-4">
          <ActivityItem 
            v-for="order in recentOrders" 
            :key="order.id"
            :order="order"
          />
        </div>
      </div>

      <!-- Recommendations -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Recommended for You</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <RecommendationCard 
            v-for="item in recommendations" 
            :key="item.id"
            :item="item"
          />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import StatsCard from '@/Components/Dashboard/StatsCard.vue'
import QuickActionCard from '@/Components/Dashboard/QuickActionCard.vue'
import ActivityItem from '@/Components/Dashboard/ActivityItem.vue'
import RecommendationCard from '@/Components/Dashboard/RecommendationCard.vue'

defineProps({
  user: Object,
  stats: Object,
  recentOrders: Array,
  milesData: Object
})

const quickActions = [
  { icon: '✈️', title: 'Book Flight', route: '/flights', color: 'blue' },
  { icon: '🏨', title: 'Book Hotel', route: '/hotels', color: 'green' },
  { icon: '🚗', title: 'Rent Car', route: '/cars', color: 'orange' },
  { icon: '📱', title: 'Electronics', route: '/electronics', color: 'purple' },
  { icon: '🍕', title: 'Food & Drinks', route: '/food', color: 'red' },
  { icon: '🛋️', title: 'Furniture', route: '/furniture', color: 'yellow' }
]

const recommendations = [
  { id: 1, title: 'Dubai Flight Deal', price: 450000, type: 'flight', image: '✈️' },
  { id: 2, title: 'Kigali Marriott', price: 85000, type: 'hotel', image: '🏨' },
  { id: 3, title: 'AirPods Pro', price: 280000, type: 'electronics', image: '🎧' }
]
</script>