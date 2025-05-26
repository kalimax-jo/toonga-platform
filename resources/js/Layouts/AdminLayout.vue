<template>
  <div class="flex h-screen bg-[#f5f7fa]">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md flex-shrink-0 overflow-y-auto">
      <div class="p-4 font-bold text-xl text-indigo-600 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V7a4 4 0 118 0v4a4 4 0 11-8 0zm0 0H9a4 4 0 100 8h2a4 4 0 100-8z" />
        </svg>
        Toonga Admin
      </div>

      <nav class="space-y-4 px-4 text-sm">
        <div class="text-gray-500 uppercase tracking-wide text-xs">Home</div>
        <a href="/admin/dashboard" class="flex items-center gap-2 px-2 py-2 rounded hover:bg-indigo-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" />
          </svg>
          Dashboard
        </a>

        <div class="text-gray-500 uppercase tracking-wide text-xs mt-4">Manage</div>
        <details open class="text-sm">
          <summary class="cursor-pointer font-semibold text-indigo-600 py-1">Vendors</summary>
          <div class="pl-4 space-y-1">
            <a href="/admin/vendors" class="block hover:text-indigo-600">Manage Vendors</a>
            <a href="/admin/vendors/pending" class="block hover:text-indigo-600">
  Review Vendor Approvals
</a>

    <a href="/admin/categories" class="block hover:text-indigo-600">Categories</a> 
    
          </div>
        </details>

        <details>
          <summary class="cursor-pointer font-semibold text-indigo-600 py-1">Products</summary>
          <div class="pl-4 space-y-1">
            <a href="/sales/dashboard" class="block hover:text-indigo-600">Product Approvals</a>
            <a href="/admin/products" class="block hover:text-indigo-600">All Products</a>
          </div>
        </details>

        <details>
          <summary class="cursor-pointer font-semibold text-indigo-600 py-1">Finance</summary>
          <div class="pl-4 space-y-1">
            <a href="/admin/transactions" class="block hover:text-indigo-600">Transactions</a>
            <a href="/admin/commissions" class="block hover:text-indigo-600">Commissions</a>
            <a href="/admin/reports" class="block hover:text-indigo-600">Reports</a>
          </div>
        </details>

        <details>
          <summary class="cursor-pointer font-semibold text-indigo-600 py-1">Loyalty & Rewards</summary>
          <div class="pl-4 space-y-1">
            <a href="/admin/rewards" class="block hover:text-indigo-600">Miles Settings</a>
            <a href="/admin/raffles" class="block hover:text-indigo-600">Raffles</a>
            <a href="/admin/offers" class="block hover:text-indigo-600">Partner Offers</a>
          </div>
        </details>

        <div class="text-gray-500 uppercase tracking-wide text-xs mt-4">Apps</div>
        <a href="/admin/campaigns" class="block hover:text-indigo-600">Campaign Manager</a>
        <a href="/admin/partners" class="block hover:text-indigo-600">Partners</a>
        <a href="/profile" class="block hover:text-indigo-600">My Profile</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
      <header class="bg-white shadow p-4 flex justify-between items-center">
        <h1 class="text-lg font-semibold">Admin Panel</h1>
        <div class="text-sm text-gray-600">Welcome, Admin</div>
      </header>

      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
