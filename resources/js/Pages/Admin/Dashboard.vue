<template>
  <AdminLayout>
    <div>
      <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Vendors</h2>
          <p class="text-gray-600">Create, approve, and manage vendor accounts.</p>
          <a href="/admin/vendors" class="text-indigo-600 mt-2 inline-block">Go to Vendor Management</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Products</h2>
          <p class="text-gray-600">Review submitted products and manage listings.</p>
          <a href="/sales/dashboard" class="text-indigo-600 mt-2 inline-block">Review Products</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Finance</h2>
          <p class="text-gray-600">View commissions, transactions, and reports.</p>
          <a href="/admin/reports" class="text-indigo-600 mt-2 inline-block">View Reports</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Loyalty & Rewards</h2>
          <p class="text-gray-600">Configure miles, raffles, and redemption offers.</p>
          <a href="/admin/rewards" class="text-indigo-600 mt-2 inline-block">Manage Rewards</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Campaigns</h2>
          <p class="text-gray-600">Create timed promotions and announcements.</p>
          <a href="/admin/campaigns" class="text-indigo-600 mt-2 inline-block">Launch Campaign</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold mb-2">Partners</h2>
          <p class="text-gray-600">Manage reward partners and track redemptions.</p>
          <a href="/admin/partners" class="text-indigo-600 mt-2 inline-block">Partner Dashboard</a>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>
