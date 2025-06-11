<template>
  <DashboardLayout :user="user || $page.props.auth?.user">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">My Orders</h1>
        <div class="flex gap-2">
          <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Orders</option>
            <option value="confirmed">Confirmed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
          </select>
        </div>
      </div>

      <div v-if="filteredOrders.length === 0" class="text-center py-12">
        <ArchiveBoxIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
        <p class="text-gray-500 mb-4">
          {{ statusFilter ? `No ${statusFilter} orders found` : 'You haven\'t made any orders yet' }}
        </p>
        <Link href="/flights" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
          Book Your First Flight
        </Link>
      </div>

      <div v-else class="space-y-4">
        <OrderCard 
          v-for="order in filteredOrders" 
          :key="order.id"
          :order="order"
          @view-details="viewOrderDetails"
          @cancel-order="cancelOrder"
        />
      </div>

      <!-- Pagination -->
      <div v-if="orders.links && orders.links.length > 3" class="flex justify-center">
        <nav class="flex items-center gap-2">
          <Link 
            v-for="link in orders.links" 
            :key="link.label"
            :href="link.url || '#'"
            :class="[
              'px-3 py-2 text-sm rounded-lg transition-colors',
              link.active 
                ? 'bg-blue-600 text-white' 
                : link.url 
                  ? 'text-gray-700 hover:bg-gray-100' 
                  : 'text-gray-400 cursor-not-allowed'
            ]"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ArchiveBoxIcon } from '@heroicons/vue/24/outline'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import OrderCard from '@/Components/Dashboard/OrderCard.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  orders: {
    type: Object,
    default: () => ({ data: [], links: [] })
  }
})

const statusFilter = ref('')

const filteredOrders = computed(() => {
  const orderList = props.orders?.data || []
  if (!statusFilter.value) return orderList
  return orderList.filter(order => order.status === statusFilter.value)
})

const viewOrderDetails = (orderId) => {
  router.visit(`/dashboard/orders/${orderId}`)
}

const cancelOrder = (orderId) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    router.patch(`/orders/${orderId}/cancel`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Show success message
      }
    })
  }
}
</script>