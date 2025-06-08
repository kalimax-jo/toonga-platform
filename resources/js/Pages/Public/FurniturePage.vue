<template>
    <div class="furniture-page">
        <header class="header">
            <h1>Furniture Collection</h1>
            <input
                v-model="search"
                type="text"
                placeholder="Search furniture..."
                class="search-input"
            />
        </header>

        <section class="furniture-list">
            <div
                v-for="item in filteredFurniture"
                :key="item.id"
                class="furniture-card"
            >
                <img :src="item.image" :alt="item.name" class="furniture-image" />
                <div class="furniture-info">
                    <h2>{{ item.name }}</h2>
                    <p class="description">{{ item.description }}</p>
                    <span class="price">${{ item.price }}</span>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const search = ref('')

const furniture = ref([
    {
        id: 1,
        name: 'Modern Sofa',
        description: 'A comfortable modern sofa for your living room.',
        price: 499,
        image: 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?auto=format&fit=crop&w=400&q=80'
    },
    {
        id: 2,
        name: 'Wooden Dining Table',
        description: 'Elegant wooden dining table for family meals.',
        price: 799,
        image: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80'
    },
    {
        id: 3,
        name: 'Office Chair',
        description: 'Ergonomic office chair for comfortable work.',
        price: 199,
        image: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80'
    }
])

const filteredFurniture = computed(() =>
    furniture.value.filter(item =>
        item.name.toLowerCase().includes(search.value.toLowerCase())
    )
)
</script>

<style scoped>
.furniture-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f9f9f9;
}

.header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2rem;
}

.header h1 {
    margin-bottom: 1rem;
    font-size: 2.5rem;
    color: #333;
}

.search-input {
    padding: 0.5rem 1rem;
    width: 300px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
}

.furniture-list {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    justify-content: center;
}

.furniture-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    width: 300px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s;
}

.furniture-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.13);
}

.furniture-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.furniture-info {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.furniture-info h2 {
    font-size: 1.3rem;
    margin: 0;
    color: #222;
}

.description {
    color: #666;
    font-size: 1rem;
}

.price {
    color: #1976d2;
    font-weight: bold;
    font-size: 1.1rem;
    margin-top: 0.5rem;
}
</style>