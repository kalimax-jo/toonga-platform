<template>
  <div>
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-600">Toonga</h1>
        <nav class="space-x-6">
          <a href="/" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Home</a>
          <a href="/flights" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Flights</a>
          <a href="/hotels" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Hotels</a>
          <a href="/cars" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Car Rentals</a>
          <a href="/electronics" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Electronics</a>
          <a href="/food" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Food & Beverage</a>
          <a href="/furniture" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Furniture</a>
          <a href="/login" class="text-sm font-medium text-indigo-600">Login</a>
        </nav>
      </div>
    </header>

    <main class="min-h-screen">
      <slot />
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-16">
      <div class="max-w-6xl mx-auto px-4 flex justify-between">
        <p>&copy; 2025 Toonga. All rights reserved.</p>
        <div class="space-x-4">
          <a href="#" class="hover:underline">Privacy</a>
          <a href="#" class="hover:underline">Terms</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// No props or logic needed for static layout
</script>
