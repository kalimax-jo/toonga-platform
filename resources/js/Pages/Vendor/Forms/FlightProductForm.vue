<template>
  <div class="space-y-4">
    <!-- Search Inputs -->
    <div class="grid grid-cols-2 gap-4">
      <input v-model="origin" placeholder="Origin (e.g., KGL)" class="border p-2 rounded" />
      <input v-model="destination" placeholder="Destination (e.g., NBO)" class="border p-2 rounded" />
    </div>
    <input v-model="departureDate" type="date" class="border p-2 rounded w-full" />

    <button @click="searchFlights" :disabled="loading" class="bg-blue-600 text-white px-4 py-2 rounded">
      {{ loading ? 'Searching...' : 'Search Flights' }}
    </button>

    <!-- Flight Results -->
    <div v-if="flights.length" class="space-y-2">
      <label class="font-semibold">Select a Flight</label>
      <select v-model="modelValue.additional_data.selected_flight" class="border p-2 rounded w-full">
        <option disabled value="">Choose a flight offer</option>
        <option v-for="(flight, index) in flights" :key="index" :value="flight">
          ✈️ {{ getAirlineName(flight.itineraries[0].segments[0].carrierCode) }} —
          {{ flight.itineraries[0].segments[0].departure.iataCode }} →
          {{ flight.itineraries[0].segments[0].arrival.iataCode }} —
          {{ flight.price.total }} {{ flight.price.currency }}
        </option>
      </select>
    </div>

    <div v-else-if="searched && !flights.length" class="text-red-600">
      No flights found. Try different inputs.
    </div>

    <!-- Flight Preview -->
    <div
      v-if="modelValue.additional_data.selected_flight"
      class="border p-4 rounded bg-gray-50 mt-4 shadow-sm"
    >
      <h2 class="font-bold text-lg mb-2 text-indigo-700">Selected Flight Summary</h2>

      <div class="flex items-center gap-3 mb-2">
        <img
          :src="getAirlineLogo(modelValue.additional_data.selected_flight.itineraries[0].segments[0].carrierCode)"
          class="h-6"
        />
        <span class="text-sm font-semibold text-gray-800">
          {{ getAirlineName(modelValue.additional_data.selected_flight.itineraries[0].segments[0].carrierCode) }}
        </span>
      </div>

      <div class="space-y-1 text-sm text-gray-700">
        <p>
          <strong>From:</strong>
          {{ modelValue.additional_data.selected_flight.itineraries[0].segments[0].departure.iataCode }}
          ({{ modelValue.additional_data.selected_flight.itineraries[0].segments[0].departure.at }})
        </p>
        <p>
          <strong>To:</strong>
          {{ modelValue.additional_data.selected_flight.itineraries[0].segments[0].arrival.iataCode }}
          ({{ modelValue.additional_data.selected_flight.itineraries[0].segments[0].arrival.at }})
        </p>
        <p>
          <strong>Class:</strong>
          {{ modelValue.additional_data.selected_flight.travelerPricings[0].fareDetailsBySegment[0].cabin }}
        </p>
        <p>
          <strong>Total Price:</strong>
          {{ modelValue.additional_data.selected_flight.price.total }}
          {{ modelValue.additional_data.selected_flight.price.currency }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
defineProps(['modelValue'])

const origin = ref('')
const destination = ref('')
const departureDate = ref('')
const flights = ref([])
const loading = ref(false)
const searched = ref(false)

const airlineNames = {
  WB: 'RwandAir',
  KQ: 'Kenya Airways',
  ET: 'Ethiopian Airlines',
  QR: 'Qatar Airways',
  TK: 'Turkish Airlines',
  AF: 'Air France',
  KL: 'KLM Royal Dutch Airlines',
  LH: 'Lufthansa',
  BA: 'British Airways',
}

const getAirlineName = (code) => {
  return airlineNames[code] || code
}

const getAirlineLogo = (code) => {
  return `https://content.airhex.com/content/logos/airlines_${code}_200_200_s.png`
}

const searchFlights = async () => {
  if (!origin.value || !destination.value || !departureDate.value) {
    alert("Please fill in origin, destination, and date")
    return
  }

  loading.value = true
  searched.value = true

  try {
    const res = await axios.get('/api/flight-search', {
      params: {
        origin: origin.value,
        destination: destination.value,
        date: departureDate.value,
      }
    })
    flights.value = res.data.data || []
  } catch (error) {
    console.error("Error loading flights:", error)
    alert("Something went wrong while fetching flights.")
  }

  loading.value = false
}
</script>
