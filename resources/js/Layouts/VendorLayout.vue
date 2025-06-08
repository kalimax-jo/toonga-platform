<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md flex-shrink-0 overflow-y-auto">
      <div class="p-4 font-bold text-xl text-indigo-600 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V7a4 4 0 118 0v4a4 4 0 11-8 0zm0 0H9a4 4 0 100 8h2a4 4 0 100-8z" />
        </svg>
        Vendor Panel
      </div>

      <nav class="space-y-4 px-4 text-sm">
        <div class="text-gray-500 uppercase tracking-wide text-xs">Dashboard</div>
        <a href="/vendor/dashboard" class="flex items-center gap-2 px-2 py-2 rounded hover:bg-indigo-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" />
          </svg>
          Dashboard
        </a>

        <div class="text-gray-500 uppercase tracking-wide text-xs mt-4">Manage</div>
        <a href="/vendor/products/create" class="block hover:text-indigo-600">Add Product</a>
        <a href="/vendor/products" class="block hover:text-indigo-600">My Products</a>
        <a href="/vendor/products/pending" class="block hover:text-indigo-600">Pending Approvals</a>
        <a href="/vendor/categories" class="block hover:text-indigo-600">Categories</a>
    


        <div class="text-gray-500 uppercase tracking-wide text-xs mt-4">Reports</div>
        <a href="/vendor/sales" class="block hover:text-indigo-600">Sales Report</a>
        <a href="/vendor/payments" class="block hover:text-indigo-600">Payout Summary</a>

        <div class="text-gray-500 uppercase tracking-wide text-xs mt-4">Account</div>
        <a href="/profile" class="block hover:text-indigo-600">My Profile</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
      <header class="bg-white shadow p-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold">Vendor Dashboard</h1>
        <div class="text-sm text-gray-600">Welcome, Vendor</div>
      </header>

      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
// No logic required for static layout
</script>
