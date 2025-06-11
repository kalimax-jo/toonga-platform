<template>
  <DashboardLayout :user="user || $page.props.auth?.user">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 rounded-lg">
        <h1 class="text-2xl font-bold mb-2">Miles & Rewards</h1>
        <p class="text-purple-100">Manage your miles and explore earning opportunities</p>
      </div>

      <!-- Miles Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Available Miles</p>
              <p class="text-2xl font-bold text-green-600">{{ milesData?.available?.toLocaleString() || '0' }}</p>
            </div>
            <StarIcon class="w-8 h-8 text-yellow-400" />
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Earned This Year</p>
              <p class="text-2xl font-bold text-blue-600">{{ milesData?.earned?.toLocaleString() || '0' }}</p>
            </div>
            <TrophyIcon class="w-8 h-8 text-blue-400" />
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Expiring Soon</p>
              <p class="text-2xl font-bold text-red-600">{{ milesData?.expiring?.toLocaleString() || '0' }}</p>
            </div>
            <ClockIcon class="w-8 h-8 text-red-400" />
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Current Tier</p>
              <p class="text-2xl font-bold text-purple-600">{{ milesData?.tier || 'Bronze' }}</p>
            </div>
            <ShieldCheckIcon class="w-8 h-8 text-purple-400" />
          </div>
        </div>
      </div>

      <!-- Tier Progress -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Tier Progress</h3>
        <div class="mb-4">
          <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>Progress to next tier</span>
            <span>{{ milesData?.tierProgress || 0 }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div 
              class="bg-gradient-to-r from-purple-500 to-blue-500 h-3 rounded-full transition-all duration-500" 
              :style="{ width: (milesData?.tierProgress || 0) + '%' }"
            ></div>
          </div>
        </div>
        <div class="grid grid-cols-4 gap-4 text-center text-sm">
          <div :class="getCurrentTierClass('Bronze')">
            <div class="font-medium">Bronze</div>
            <div class="text-xs text-gray-500">0+ miles</div>
          </div>
          <div :class="getCurrentTierClass('Silver')">
            <div class="font-medium">Silver</div>
            <div class="text-xs text-gray-500">2,000+ miles</div>
          </div>
          <div :class="getCurrentTierClass('Gold')">
            <div class="font-medium">Gold</div>
            <div class="text-xs text-gray-500">5,000+ miles</div>
          </div>
          <div :class="getCurrentTierClass('Platinum')">
            <div class="font-medium">Platinum</div>
            <div class="text-xs text-gray-500">10,000+ miles</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer">
          <div class="text-center">
            <GiftIcon class="w-12 h-12 text-blue-600 mx-auto mb-3" />
            <h3 class="font-semibold mb-2">Redeem Miles</h3>
            <p class="text-sm text-gray-600 mb-4">Use your miles for flights, upgrades, and more</p>
            <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
              Browse Rewards
            </button>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer">
          <div class="text-center">
            <PlusIcon class="w-12 h-12 text-green-600 mx-auto mb-3" />
            <h3 class="font-semibold mb-2">Earn More Miles</h3>
            <p class="text-sm text-gray-600 mb-4">Discover ways to earn miles faster</p>
            <button class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
              Earning Opportunities
            </button>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer">
          <div class="text-center">
            <DocumentTextIcon class="w-12 h-12 text-purple-600 mx-auto mb-3" />
            <h3 class="font-semibold mb-2">Miles Statement</h3>
            <p class="text-sm text-gray-600 mb-4">View your detailed miles activity</p>
            <button class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">
              View Statement
            </button>
          </div>
        </div>
      </div>

      <!-- Miles History -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
          <h3 class="text-lg font-semibold">Recent Miles Activity</h3>
        </div>
        <div class="p-6">
          <div v-if="milesData?.history?.data?.length > 0" class="space-y-4">
            <div 
              v-for="transaction in milesData.history.data" 
              :key="transaction.id"
              class="flex items-center justify-between py-3 border-b last:border-b-0"
            >
              <div class="flex items-center gap-3">
                <div :class="getTransactionIconClass(transaction.type)">
                  <component :is="getTransactionIcon(transaction.type)" class="w-4 h-4" />
                </div>
                <div>
                  <div class="font-medium">{{ transaction.description || 'Miles Transaction' }}</div>
                  <div class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</div>
                </div>
              </div>
              <div :class="getTransactionAmountClass(transaction.type)">
                {{ transaction.type === 'earned' ? '+' : '-' }}{{ transaction.amount?.toLocaleString() || 0 }} miles
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <StarIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No miles activity yet</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { 
  StarIcon, 
  TrophyIcon, 
  ClockIcon, 
  ShieldCheckIcon,
  GiftIcon,
  PlusIcon,
  DocumentTextIcon,
  ArrowUpIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  milesData: {
    type: Object,
    default: () => ({
      available: 0,
      earned: 0,
      expiring: 0,
      tier: 'Bronze',
      tierProgress: 0,
      history: { data: [] }
    })
  }
})

const getCurrentTierClass = (tier) => {
  const currentTier = props.milesData?.tier || 'Bronze'
  return currentTier === tier 
    ? 'text-purple-600 font-semibold' 
    : 'text-gray-400'
}

const getTransactionIcon = (type) => {
  return type === 'earned' ? ArrowUpIcon : ArrowDownIcon
}

const getTransactionIconClass = (type) => {
  return type === 'earned' 
    ? 'w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center'
    : 'w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center'
}

const getTransactionAmountClass = (type) => {
  return type === 'earned' 
    ? 'font-semibold text-green-600'
    : 'font-semibold text-red-600'
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>