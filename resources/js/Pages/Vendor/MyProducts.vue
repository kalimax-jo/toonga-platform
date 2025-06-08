<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">My Products</h1>

    <div>
      <h2 class="text-lg font-semibold">Approved Products</h2>
      <ul v-if="approved.length" class="mb-6">
        <li v-for="product in approved" :key="product.id" class="p-2 border rounded mb-2">
          <strong>{{ product.title }}</strong> - {{ product.category?.name }} - {{ product.price }} RWF
        </li>
      </ul>
      <p v-else class="text-gray-500">No approved products yet.</p>
    </div>

    <div>
      <h2 class="text-lg font-semibold">Pending Approval</h2>
      <ul v-if="pending.length">
        <li v-for="product in pending" :key="product.id" class="p-2 border rounded mb-2">
          <strong>{{ product.title }}</strong> - {{ product.category?.name }} - {{ product.price }} RWF
        </li>
      </ul>
      <p v-else class="text-gray-500">No products pending approval.</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  products: Array
})

const approved = computed(() => props.products.filter(p => p.is_approved))
const pending = computed(() => props.products.filter(p => !p.is_approved))
</script>
