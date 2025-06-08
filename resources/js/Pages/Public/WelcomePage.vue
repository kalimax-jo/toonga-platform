<template>
  <PublicLayout>
    <div class="max-w-6xl mx-auto py-16 px-4">
      <h1 class="text-3xl font-bold text-center mb-10 text-indigo-700">Welcome to Toonga</h1>
      <p class="text-center text-gray-600 mb-12">Choose a service to get started</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div
          v-for="category in categories"
          :key="category.id"
          @click="navigate(category.slug)"
          class="bg-white p-6 rounded shadow cursor-pointer hover:shadow-lg transition"
        >
          <div class="text-center">
            <img :src="category.icon" alt="" class="h-16 mx-auto mb-4" />
            <h2 class="text-lg font-semibold text-gray-800">{{ category.name }}</h2>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const categories = [
  {
    id: 1,
    name: 'Flights',
    slug: 'flights',
    icon: 'https://img.icons8.com/ios-filled/100/airplane-take-off.png'
  },
  {
    id: 2,
    name: 'Hotels',
    slug: 'hotels',
    icon: 'https://img.icons8.com/ios-filled/100/hotel.png'
  },
  {
    id: 3,
    name: 'Car Rentals',
    slug: 'cars',
    icon: 'https://img.icons8.com/ios-filled/100/car-rental.png'
  },
  {
    id: 4,
    name: 'Food & Beverage',
    slug: 'food',
    icon: 'https://img.icons8.com/ios-filled/100/meal.png'
  },
  {
    id: 5,
    name: 'Electronics',
    slug: 'electronics',
    icon: 'https://img.icons8.com/ios-filled/100/electronics.png'
  },
  {
    id: 6,
    name: 'Furniture',
    slug: 'furniture',
    icon: 'https://img.icons8.com/ios-filled/100/sofa.png'
  }
]

const navigate = (slug) => {
  router.visit(`/${slug}`)
}
</script>

<style scoped>
img {
  filter: grayscale(40%);
}
</style>
