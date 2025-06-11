<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Top Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-3">
            <Link href="/" class="text-2xl font-bold text-blue-600">Toonga</Link>
            <div class="hidden md:block text-gray-400">|</div>
            <div class="hidden md:block text-gray-600">Dashboard</div>
          </div>
          
          <div class="flex items-center gap-4">
            <button class="relative p-2 text-gray-400 hover:text-gray-600">
              <MagnifyingGlassIcon class="w-5 h-5" />
            </button>
            <button class="relative p-2 text-gray-400 hover:text-gray-600">
              <BellIcon class="w-5 h-5" />
              <span v-if="notifications" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ notifications }}
              </span>
            </button>
            
            <!-- User Menu -->
            <div class="relative">
              <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm">
                  {{ (props.user?.name || 'U').charAt(0) }}
                </div>
                <span class="hidden md:block text-sm font-medium">{{ props.user?.name || 'User' }}</span>
              </button>
              
              <!-- Dropdown Menu -->
              <div v-if="showUserMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                <Link href="/dashboard/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</Link>
                <Link href="/dashboard/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</Link>
                <Link href="/logout" method="post" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex gap-6">
        <!-- Sidebar -->
        <div class="w-64 flex-shrink-0">
          <div class="bg-white rounded-lg shadow">
            <nav class="p-4 space-y-1">
              <SidebarLink 
                v-for="item in sidebarItems" 
                :key="item.route"
                :href="item.route"
                :active="$page.url.startsWith(item.route)"
                :icon="item.icon"
                :label="item.label"
                :count="item.count"
              />
            </nav>
          </div>

          <!-- Quick Stats Widget -->
          <div class="mt-6 bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold mb-3">Quick Stats</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Total Orders</span>
                <span class="text-sm font-medium">{{ props.stats?.totalOrders || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Miles Balance</span>
                <span class="text-sm font-medium text-green-600">{{ props.stats?.availableMiles?.toLocaleString() || '0' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Tier Status</span>
                <span class="text-sm font-medium text-purple-600">{{ props.milesData?.tier || 'Bronze' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { BellIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import SidebarLink from '@/Components/Dashboard/SidebarLink.vue'

const props = defineProps({
  user: {
    type: Object,
    default: () => ({
      name: 'User',
      email: ''
    })
  },
  stats: {
    type: Object,
    default: () => ({
      totalOrders: 0,
      availableMiles: 0,
      cartItems: 0,
      activeBookings: 0
    })
  },
  milesData: {
    type: Object,
    default: () => ({
      tier: 'Bronze',
      available: 0,
      earned: 0,
      expiring: 0
    })
  },
  notifications: {
    type: Number,
    default: 0
  }
})

const showUserMenu = ref(false)

const sidebarItems = computed(() => [
  { route: '/dashboard', icon: 'home', label: 'Dashboard', count: null },
  { route: '/dashboard/cart', icon: 'shopping-cart', label: 'Shopping Cart', count: props.stats?.cartItems || 0 },
  { route: '/dashboard/orders', icon: 'archive-box', label: 'My Orders', count: null },
  { route: '/dashboard/miles', icon: 'star', label: 'Miles & Rewards', count: null },
  { route: '/dashboard/wishlist', icon: 'heart', label: 'My Wishlist', count: props.stats?.wishlistItems || 0 },
  { route: '/dashboard/profile', icon: 'user', label: 'Profile & Settings', count: null }
])
</script>