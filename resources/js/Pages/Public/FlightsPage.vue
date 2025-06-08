<template>
  <PublicLayout>
    <div class="max-w-6xl mx-auto py-10 px-6 bg-white rounded shadow">
      <!-- Currency Selection -->
      <div class="mb-6 flex items-center justify-between bg-blue-50 p-4 rounded-lg">
        <div class="flex items-center gap-4">
          <label class="text-sm font-medium text-blue-900">Currency:</label>
          <select 
            v-model="selectedCurrency" 
            @change="updateCurrencyRates"
            class="border border-blue-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="EUR">EUR (€)</option>
            <option value="USD">USD ($)</option>
            <option value="RWF">RWF (Rwf)</option>
          </select>
        </div>
        <div v-if="exchangeRates && selectedCurrency !== 'EUR'" class="text-sm text-blue-700">
          1 EUR = {{ formatCurrency(exchangeRates[selectedCurrency], selectedCurrency) }}
        </div>
      </div>
      <!-- Search Form -->
      <form @submit.prevent="searchFlights">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
          <!-- Trip Type -->
          <div class="col-span-full">
            <label class="text-sm font-semibold text-gray-700">Trip Type</label>
            <div class="flex gap-4 mt-2">
              <label class="flex items-center gap-2 cursor-pointer">
                <input 
                  type="radio" 
                  value="round" 
                  v-model="tripType" 
                  class="text-indigo-600 focus:ring-indigo-500"
                /> 
                Round Trip
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input 
                  type="radio" 
                  value="oneway" 
                  v-model="tripType"
                  class="text-indigo-600 focus:ring-indigo-500"
                /> 
                One Way
              </label>
            </div>
          </div>

          <!-- Origin -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <div class="relative">
              <input 
                v-model="originSearch" 
                @input="handleOriginInput"
                @focus="showOriginDropdown = true"
                @blur="handleOriginBlur"
                class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                placeholder="e.g., Kigali or KGL" 
                required
                :class="{ 'border-red-500': errors.origin }"
                autocomplete="off"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            
            <!-- Origin Dropdown -->
            <div v-if="showOriginDropdown && filteredOriginAirports.length > 0" 
                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              <div v-for="airport in filteredOriginAirports" 
     :key="`orig-${airport.code}`"
                   @mousedown="selectOriginAirport(airport)"
                   class="px-4 py-3 cursor-pointer hover:bg-indigo-50 border-b border-gray-100 last:border-b-0">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-medium text-gray-900">{{ airport.city }}</p>
                    <p class="text-sm text-gray-500">{{ airport.name }}</p>
                  </div>
                  <span class="text-sm font-bold text-indigo-600">{{ airport.code }}</span>
                </div>
                <p v-if="airport.country" class="text-xs text-gray-400 mt-1">{{ airport.country }}</p>
              </div>
            </div>
            <p v-if="errors.origin" class="text-red-500 text-xs mt-1">{{ errors.origin }}</p>
          </div>

          <!-- Destination -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <div class="relative">
              <input 
                v-model="destinationSearch" 
                @input="handleDestinationInput"
                @focus="showDestinationDropdown = true"
                @blur="handleDestinationBlur"
                class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                placeholder="e.g., Nairobi or NBO" 
                required
                :class="{ 'border-red-500': errors.destination }"
                autocomplete="off"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            
            <!-- Destination Dropdown -->
            <div v-if="showDestinationDropdown && filteredDestinationAirports.length > 0" 
                 class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              <div v-for="airport in filteredDestinationAirports" 
     :key="`dest-${airport.code}`"
                   @mousedown="selectDestinationAirport(airport)"
                   class="px-4 py-3 cursor-pointer hover:bg-indigo-50 border-b border-gray-100 last:border-b-0">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-medium text-gray-900">{{ airport.city }}</p>
                    <p class="text-sm text-gray-500">{{ airport.name }}</p>
                  </div>
                  <span class="text-sm font-bold text-indigo-600">{{ airport.code }}</span>
                </div>
                <p v-if="airport.country" class="text-xs text-gray-400 mt-1">{{ airport.country }}</p>
              </div>
            </div>
            <p v-if="errors.destination" class="text-red-500 text-xs mt-1">{{ errors.destination }}</p>
          </div>

          <!-- Departure Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departure Date</label>
            <input 
              type="date" 
              v-model="departureDate" 
              class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
              required
              :min="minDate"
              :class="{ 'border-red-500': errors.departureDate }"
            />
            <p v-if="errors.departureDate" class="text-red-500 text-xs mt-1">{{ errors.departureDate }}</p>
          </div>

          <!-- Return Date -->
          <div v-if="tripType === 'round'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Return Date</label>
            <input 
              type="date" 
              v-model="returnDate" 
              class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
              :required="tripType === 'round'"
              :min="departureDate || minDate"
              :class="{ 'border-red-500': errors.returnDate }"
            />
            <p v-if="errors.returnDate" class="text-red-500 text-xs mt-1">{{ errors.returnDate }}</p>
          </div>

          <!-- Travelers -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Travelers</label>
            <input 
              type="number" 
              min="1" 
              max="9"
              v-model.number="travelers" 
              class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
              required
            />
          </div>

          <!-- Flight Class -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
            <select 
              v-model="flightClass" 
              class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">All Classes</option>
              <option value="ECONOMY">Economy</option>
              <option value="BUSINESS">Business</option>
              <option value="FIRST">First</option>
            </select>
          </div>

          <!-- Sort Options -->
          <div class="col-span-full flex items-center justify-between">
            <label class="flex items-center gap-2">
              <span class="text-sm font-medium text-gray-700">Sort by:</span>
              <select 
                v-model="sortOrder" 
                class="border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="asc">Price: Low to High</option>
                <option value="desc">Price: High to Low</option>
                <option value="duration">Duration: Shortest First</option>
              </select>
            </label>
          </div>

          <!-- Search Button -->
          <div class="col-span-full">
            <button 
              type="submit" 
              :disabled="loading"
              class="w-full bg-indigo-600 text-white py-3 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center gap-2"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              <span v-else>🔍</span>
              {{ loading ? 'Searching...' : 'Search Flights' }}
            </button>
          </div>
        </div>
      </form>

      <!-- Error Message -->
      <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center mt-10 py-12">
        <div class="text-center">
          <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <p class="text-indigo-600 text-lg font-medium">Searching for the best flights...</p>
          <p class="text-gray-500 text-sm mt-1">This may take a few moments</p>
        </div>
      </div>

      <!-- Results Header -->
      <div v-if="flights.length && !loading" class="flex justify-between items-center mb-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-800">
          {{ flights.length }} flight{{ flights.length !== 1 ? 's' : '' }} found
        </h2>
        <div class="text-sm text-gray-500">
          Page {{ currentPage }} of {{ totalPages }}
        </div>
      </div>

      <!-- Flight Results -->
      <div v-if="flights.length && !loading" class="space-y-4 mt-8">
        <div 
          v-for="(flight, index) in paginatedFlights" 
          :key="index" 
          class="border border-gray-200 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-start gap-4">
            <!-- Airline Logo -->
            <div class="flex-shrink-0">
              <img 
                :src="getAirlineLogo(flight.carrier)" 
                :alt="getAirlineName(flight.carrier)"
                class="w-12 h-12 object-contain rounded-full border border-gray-200"
                @error="handleImageError"
              />
            </div>

            <!-- Flight Details -->
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-3">
                <h3 class="font-semibold text-gray-800 text-lg">
                  {{ getAirlineName(flight.carrier) }}
                </h3>
                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                  {{ flight.tripType }}
                </span>
                <!-- Flight Class Badge -->
                <span v-if="flight.class" :class="getClassBadgeColor(flight.class)" class="text-xs px-2 py-1 rounded-full font-medium">
                  {{ formatCabinClass(flight.class) }}
                </span>
              </div>

              <!-- Flight Summary Info -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Booking Class</p>
                  <p class="font-semibold text-sm">{{ flight.bookingClass || 'N/A' }}</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Fare Type</p>
                  <p class="font-semibold text-sm">{{ flight.fareType || 'Standard' }}</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Baggage</p>
                  <p class="font-semibold text-sm">{{ flight.includedCheckedBags || '1' }} x 23kg</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-gray-500 uppercase tracking-wide">Aircraft</p>
                  <p class="font-semibold text-sm">{{ flight.aircraft || 'Various' }}</p>
                </div>
              </div>

              <!-- Itineraries -->
              <div v-for="(itinerary, i) in flight.itineraries" :key="i" class="mb-4 last:mb-0">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <p class="text-sm text-gray-600 font-medium">
                      {{ i === 0 ? 'Outbound' : 'Return' }} Flight
                    </p>
                    <span v-if="itinerary.segments?.length > 1" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">
                      {{ itinerary.segments.length - 1 }} stop{{ itinerary.segments.length > 2 ? 's' : '' }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500">
                    Duration: {{ formatDuration(itinerary.duration) }}
                  </p>
                </div>
                
                <div class="flex items-center gap-4 text-sm mb-3">
                  <div class="text-center">
                    <p class="font-bold text-gray-800 text-base">
                      {{ itinerary.segments[0].departure.iataCode }}
                    </p>
                    <p class="text-gray-600 font-medium">
                      {{ formatTime(itinerary.segments[0].departure.at) }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatDate(itinerary.segments[0].departure.at) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      {{ getAirportName(itinerary.segments[0].departure.iataCode) }}
                    </p>
                  </div>
                  
                  <div class="flex-1 flex items-center justify-center">
                    <div class="w-full border-t border-gray-300 relative">
                      <div class="absolute inset-0 flex items-center justify-center">
                        <span class="bg-white px-3 text-xs text-gray-500 border rounded-full">
                          ✈️ {{ getMinutes(itinerary.duration) }}m
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="text-center">
                    <p class="font-bold text-gray-800 text-base">
                      {{ itinerary.segments[itinerary.segments.length - 1].arrival.iataCode }}
                    </p>
                    <p class="text-gray-600 font-medium">
                      {{ formatTime(itinerary.segments[itinerary.segments.length - 1].arrival.at) }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatDate(itinerary.segments[itinerary.segments.length - 1].arrival.at) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      {{ getAirportName(itinerary.segments[itinerary.segments.length - 1].arrival.iataCode) }}
                    </p>
                  </div>
                </div>

                <!-- Detailed Segments for Multi-stop flights -->
                <div v-if="itinerary.segments?.length > 1" class="mt-3 space-y-2">
                  <div v-for="(segment, segIndex) in itinerary.segments" :key="segIndex" class="bg-gray-50 p-3 rounded text-xs">
                    <div class="flex justify-between items-center">
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Segment {{ segIndex + 1 }}:</span>
                        <span>{{ segment.departure.iataCode }} → {{ segment.arrival.iataCode }}</span>
                        <span class="text-gray-500">{{ getAirlineName(segment.carrierCode) }} {{ segment.number }}</span>
                      </div>
                      <div class="text-right">
                        <p>{{ formatTime(segment.departure.at) }} - {{ formatTime(segment.arrival.at) }}</p>
                        <p class="text-gray-500">{{ formatDuration(segment.duration) }}</p>
                      </div>
                    </div>
                    <div class="flex justify-between mt-1 text-gray-500">
                      <span>Aircraft: {{ segment.aircraft?.code || 'N/A' }}</span>
                      <span>Class: {{ formatCabinClass(segment.cabin) }}</span>
                    </div>
                    
                    <!-- Layover info -->
                    <div v-if="segIndex < itinerary.segments.length - 1" class="mt-2 pt-2 border-t border-gray-200">
                      <p class="text-orange-600 font-medium">
                        ⏱️ Layover in {{ segment.arrival.iataCode }}: {{ calculateLayover(segment.arrival.at, itinerary.segments[segIndex + 1].departure.at) }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Flight Rules & Restrictions -->
                <div v-if="flight.fareRules" class="mt-3 p-2 bg-blue-50 rounded text-xs">
                  <p class="font-medium text-blue-800 mb-1">Fare Rules:</p>
                  <div class="grid grid-cols-2 gap-2 text-blue-700">
                    <span v-if="flight.fareRules.refundable !== undefined">
                      {{ flight.fareRules.refundable ? '✅' : '❌' }} {{ flight.fareRules.refundable ? 'Refundable' : 'Non-refundable' }}
                    </span>
                    <span v-if="flight.fareRules.changeable !== undefined">
                      {{ flight.fareRules.changeable ? '✅' : '❌' }} {{ flight.fareRules.changeable ? 'Changeable' : 'Non-changeable' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Price and Book Button -->
            <div class="text-right flex-shrink-0 min-w-[140px]">
              <div class="mb-4">
                <p class="text-2xl font-bold text-indigo-600">{{ getConvertedPrice(flight.price) }}</p>
                <p class="text-xs text-gray-500">per person</p>
                
                <!-- Miles Display -->
                <div class="mt-2 p-2 bg-green-50 rounded-lg border border-green-200">
                  <div class="flex items-center gap-1 mb-1">
                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-xs font-medium text-green-800">Earn Miles</span>
                  </div>
                  <p class="text-sm font-bold text-green-800">{{ calculateMiles(flight.price) }} miles</p>
                  <p class="text-xs text-green-600">per person</p>
                </div>
               <div v-if="flight.priceBreakdown" class="text-xs text-gray-400 mt-1 space-y-0.5">
                  <p v-if="flight.priceBreakdown.baseFare">Base: {{ getConvertedPrice(flight.priceBreakdown.baseFare) }}</p>
                  <p v-if="flight.priceBreakdown.taxes">Taxes: {{ getConvertedPrice(flight.priceBreakdown.taxes) }}</p>
                  <p v-if="flight.priceBreakdown.fees">Fees: {{ getConvertedPrice(flight.priceBreakdown.fees) }}</p>
                </div>
              </div>
              
              <!-- Fare Details Toggle -->
              <button 
                @click="toggleFareDetails(index)" 
                class="text-xs text-indigo-600 hover:text-indigo-800 mb-2 flex items-center gap-1"
              >
                <span>{{ showFareDetails[index] ? 'Hide' : 'Show' }} Details</span>
                <svg 
                  :class="{ 'rotate-180': showFareDetails[index] }" 
                  class="w-3 h-3 transition-transform"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Expandable Fare Details -->
              <div v-if="showFareDetails[index]" class="mb-3 p-2 bg-gray-50 rounded text-xs space-y-1">
                <div v-if="flight.validatingAirline" class="flex justify-between">
                  <span>Validating Airline:</span>
                  <span class="font-medium">{{ getAirlineName(flight.validatingAirline) }}</span>
                </div>
                <div v-if="flight.fareFamily" class="flex justify-between">
                  <span>Fare Family:</span>
                  <span class="font-medium">{{ flight.fareFamily }}</span>
                </div>
                <div v-if="flight.seatsRemaining" class="flex justify-between">
                  <span>Seats Left:</span>
                  <span :class="flight.seatsRemaining < 5 ? 'text-red-600 font-medium' : 'text-green-600'">
                    {{ flight.seatsRemaining }}
                  </span>
                </div>
                <div v-if="flight.bookingDeadline" class="flex justify-between">
                  <span>Book by:</span>
                  <span class="font-medium">{{ formatDate(flight.bookingDeadline) }}</span>
                </div>
                <div class="flex justify-between border-t pt-1">
                  <span>Total Miles:</span>
                  <span class="font-medium text-green-600">{{ calculateMiles(flight.price) * travelers }} miles</span>
                </div>
                
              </div>

              <button 
  @click="openBookingModal(flight, index)" 
  :disabled="bookingLoading[index]"
  class="w-full bg-green-600 text-white text-sm px-4 py-3 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center gap-2"
>
  <svg v-if="bookingLoading[index]" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
  </svg>
  <span>{{ bookingLoading[index] ? 'Processing...' : 'Book Now' }}</span>
</button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center mt-8">
          <nav class="flex gap-1">
            <button
              @click="currentPage = 1"
              :disabled="currentPage === 1"
              class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              First
            </button>
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="currentPage = page"
              :class="[
                'px-3 py-2 rounded-md border text-sm font-medium',
                currentPage === page 
                  ? 'bg-indigo-600 text-white border-indigo-600' 
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
            <button
              @click="currentPage = totalPages"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Last
            </button>
          </nav>
        </div>
      </div>

      <!-- No Results -->
      <div v-else-if="searched && !loading" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0012 15c-2.34 0-4.467-.881-6.08-2.33m12.16 0A7.962 7.962 0 0012 15c2.34 0 4.467-.881 6.08-2.33M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No flights found</h3>
          <p class="text-gray-500 mb-4">
            We couldn't find any flights matching your search criteria. Try adjusting your search parameters.
          </p>
          <button 
            @click="resetSearch"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors duration-200"
          >
            Reset Search
          </button>
        </div>
      </div>
    </div>


    <!-- 2. ADD BOOKING MODAL (paste before closing </PublicLayout> tag) -->
<!-- Booking Modal -->
<div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">
          {{ bookingStep === 'confirm' ? 'Confirm Your Flight' : 
             bookingStep === 'passenger' ? 'Passenger Details' : 'Payment & Checkout' }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">
          {{ bookingStep === 'confirm' ? 'Review flight details before booking' : 
             bookingStep === 'passenger' ? 'Enter passenger information' : 'Complete your booking' }}
        </p>
      </div>
      <button @click="closeBookingModal" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Modal Content -->
    <div class="p-6">
      <!-- Step 1: Flight Confirmation -->
      <div v-if="bookingStep === 'confirm' && selectedFlight">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <h3 class="font-semibold text-blue-900 mb-2">Flight Summary</h3>
          <div class="flex items-center gap-4 mb-3">
            <img :src="getAirlineLogo(selectedFlight.carrier)" 
                 :alt="getAirlineName(selectedFlight.carrier)"
                 class="w-10 h-10 object-contain rounded border">
            <div>
              <p class="font-medium text-blue-900">{{ getAirlineName(selectedFlight.carrier) }}</p>
              <p class="text-sm text-blue-700">{{ selectedFlight.tripType }} Trip</p>
            </div>
            <div class="ml-auto text-right">
              <p class="text-2xl font-bold text-blue-900">{{ selectedFlight.price }}</p>
              <p class="text-sm text-blue-700">per person × {{ travelers }} travelers</p>
            </div>
          </div>
          
          <!-- Flight Details -->
          <div v-for="(itinerary, i) in selectedFlight.itineraries" :key="i" class="mb-4 last:mb-0">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-medium text-blue-900">{{ i === 0 ? 'Outbound' : 'Return' }} Flight</h4>
              <span class="text-sm text-blue-700">{{ formatDuration(itinerary.duration) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <div class="text-center">
                <p class="font-medium">{{ itinerary.segments[0].departure.iataCode }}</p>
                <p class="text-blue-700">{{ formatTime(itinerary.segments[0].departure.at) }}</p>
                <p class="text-blue-600">{{ formatDate(itinerary.segments[0].departure.at) }}</p>
              </div>
              <div class="flex-1 text-center">
                <p class="text-blue-700">✈️ {{ getMinutes(itinerary.duration) }}m</p>
                <div v-if="itinerary.segments.length > 1" class="text-xs text-blue-600">
                  {{ itinerary.segments.length - 1 }} stop{{ itinerary.segments.length > 2 ? 's' : '' }}
                </div>
              </div>
              <div class="text-center">
                <p class="font-medium">{{ itinerary.segments[itinerary.segments.length - 1].arrival.iataCode }}</p>
                <p class="text-blue-700">{{ formatTime(itinerary.segments[itinerary.segments.length - 1].arrival.at) }}</p>
                <p class="text-blue-600">{{ formatDate(itinerary.segments[itinerary.segments.length - 1].arrival.at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Booking Terms -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
          <h4 class="font-medium text-yellow-900 mb-2">Important Information</h4>
          <ul class="text-sm text-yellow-800 space-y-1">
            <li>• Please ensure all passenger names match passport/ID exactly</li>
            <li>• Check visa requirements for your destination</li>
            <li>• Arrive at airport 2-3 hours before international flights</li>
            <li>• Baggage allowance: {{ selectedFlight.includedCheckedBags || '1' }} × 23kg checked bag</li>
            <li v-if="selectedFlight.fareRules?.refundable === false">• This is a non-refundable ticket</li>
          </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
          <button @click="closeBookingModal" 
                  class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button @click="proceedToPassengerDetails" 
                  class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            Continue to Passenger Details
          </button>
        </div>
      </div>

      <!-- Step 2: Passenger Details -->
      <div v-if="bookingStep === 'passenger'">
        <form @submit.prevent="proceedToPayment" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
              <input v-model="passengerDetails.firstName" 
                     type="text" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                     placeholder="Enter first name">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
              <input v-model="passengerDetails.lastName" 
                     type="text" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                     placeholder="Enter last name">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
              <input v-model="passengerDetails.email" 
                     type="email" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                     placeholder="Enter email address">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
              <input v-model="passengerDetails.phone" 
                     type="tel" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                     placeholder="Enter phone number">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
              <input v-model="passengerDetails.dateOfBirth" 
                     type="date" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nationality *</label>
              <input v-model="passengerDetails.nationality" 
                     type="text" 
                     required
                     class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                     placeholder="e.g., Rwandan">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Passport Number</label>
            <input v-model="passengerDetails.passportNumber" 
                   type="text"
                   class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                   placeholder="Enter passport number (required for international flights)">
          </div>

          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600 mb-2">
              <strong>Note:</strong> For multiple travelers, you can add additional passengers after completing this booking.
            </p>
            <p class="text-xs text-gray-500">
              All names must match official travel documents exactly.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-4">
            <button type="button" @click="bookingStep = 'confirm'" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
              Back
            </button>
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
              Continue to Payment
            </button>
          </div>
        </form>
      </div>

      <!-- Step 3: Payment & Checkout -->
      <div v-if="bookingStep === 'payment'">
        <div class="space-y-6">
          <!-- Booking Summary -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-3">Booking Summary</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Flight</span>
                <span>{{ selectedFlight?.price || '$0' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Travelers</span>
                <span>× {{ travelers }}</span>
              </div>
              <div class="flex justify-between">
                <span>Taxes & Fees</span>
                <span>{{ selectedFlight?.priceBreakdown?.taxes || 'Included' }}</span>
              </div>
              <div class="border-t pt-2 flex justify-between font-semibold">
                <span>Total</span>
                <span>{{ calculateTotal() }}</span>
              </div>
            </div>
          </div>

          <!-- Passenger Summary -->
          <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-medium text-blue-900 mb-2">Primary Passenger</h4>
            <p class="text-sm text-blue-800">
              {{ passengerDetails.firstName }} {{ passengerDetails.lastName }}
            </p>
            <p class="text-sm text-blue-700">{{ passengerDetails.email }}</p>
          </div>

          <!-- Payment Notice -->
          <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
            <h4 class="font-medium text-green-900 mb-2">🔒 Secure Payment</h4>
            <p class="text-sm text-green-800 mb-3">
              You will be redirected to our secure payment partner to complete your booking.
            </p>
            <ul class="text-xs text-green-700 space-y-1">
              <li>• Credit/Debit Cards accepted</li>
              <li>• Mobile Money (MTN, Airtel)</li>
              <li>• Bank transfers available</li>
              <li>• SSL encrypted & secure</li>
            </ul>
          </div>

          <!-- Final Terms -->
          <div class="text-xs text-gray-500 space-y-2">
            <label class="flex items-start gap-2">
              <input type="checkbox" required class="mt-1">
              <span>I agree to the <a href="#" class="text-green-600 hover:underline">Terms and Conditions</a> and <a href="#" class="text-green-600 hover:underline">Privacy Policy</a></span>
            </label>
            <label class="flex items-start gap-2">
              <input type="checkbox" class="mt-1">
              <span>Send me booking updates and travel deals via email</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-4">
            <button @click="bookingStep = 'passenger'" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
              Back
            </button>
            <button @click="completeBooking" 
                    :disabled="bookingLoading.final"
                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 flex items-center justify-center gap-2">
              <svg v-if="bookingLoading.final" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              {{ bookingLoading.final ? 'Processing...' : 'Complete Booking' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'


const user = usePage().props.auth.user

const showBookingModal = ref(false)
const selectedFlight = ref(null)
const bookingStep = ref('confirm') // 'confirm', 'passenger', 'payment'
const passengerDetails = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  dateOfBirth: '',
  passportNumber: '',
  nationality: ''
})


// Booking Modal Functions
const openBookingModal = (flight, index) => {
  // Check if user is authenticated
  if (!user || !user.id) {
    alert('Please login to continue booking your flight.')
    window.location.href = '/login'
    return
  }
  
  selectedFlight.value = flight
  bookingStep.value = 'confirm'
  showBookingModal.value = true
  
  // Reset passenger details with user data if available
  passengerDetails.value = {
    firstName: user?.first_name || '',
    lastName: user?.last_name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    dateOfBirth: '',
    passportNumber: '',
    nationality: ''
  }
}

const closeBookingModal = () => {
  showBookingModal.value = false
  selectedFlight.value = null
  bookingStep.value = 'confirm'
}

const proceedToPassengerDetails = () => {
  bookingStep.value = 'passenger'
}

const proceedToPayment = () => {
  // Validate passenger details
  if (!passengerDetails.value.firstName.trim() || 
      !passengerDetails.value.lastName.trim() || 
      !passengerDetails.value.email.trim() || 
      !passengerDetails.value.phone.trim() || 
      !passengerDetails.value.dateOfBirth || 
      !passengerDetails.value.nationality.trim()) {
    alert('Please fill in all required fields.')
    return
  }
  
  bookingStep.value = 'payment'
}

const calculateTotal = () => {
  if (!selectedFlight.value?.price || !exchangeRates.value) return '$0'
  
  const match = selectedFlight.value.price.match(/[\d,]+/)
  if (!match) return selectedFlight.value.price
  
  const eurPrice = parseFloat(match[0].replace(/,/g, ''))
  const convertedPrice = eurPrice * exchangeRates.value[selectedCurrency.value]
  const total = convertedPrice * travelers.value
  
  return formatCurrency(total, selectedCurrency.value)
}

const completeBooking = async () => {
  bookingLoading.value = { ...bookingLoading.value, final: true }

  try {
    // Step 1: Create pending booking in database
    const bookingData = {
      flight: selectedFlight.value,
      passenger: passengerDetails.value,
      travelers: travelers.value,
      total: calculateTotal(),
      currency: selectedCurrency.value,
      exchange_rate: exchangeRates.value[selectedCurrency.value],
      miles_earned: calculateMiles(selectedFlight.value.price) * travelers.value,
      searchCriteria: {
        origin: origin.value,
        destination: destination.value,
        departureDate: departureDate.value,
        returnDate: returnDate.value,
        tripType: tripType.value,
        flightClass: flightClass.value
      }
    }

    const response = await axios.post('/api/bookings/create-pending', bookingData)
    
    if (response.data.success) {
      // Step 2: Redirect to payment with booking reference
      const bookingReference = response.data.booking_reference
      window.location.href = `/bookings/${bookingReference}/payment`
    } else {
      throw new Error(response.data.message || 'Booking failed')
    }
  } catch (error) {
    console.error('Booking error:', error)
    alert(error.response?.data?.message || 'Unable to create booking. Please try again.')
  } finally {
    bookingLoading.value = { ...bookingLoading.value, final: false }
  }
}

// Form data
const tripType = ref('round')
const origin = ref('')
const destination = ref('')
const originSearch = ref('')
const destinationSearch = ref('')
const departureDate = ref('')
const returnDate = ref('')
const travelers = ref(1)
const flightClass = ref('')
const sortOrder = ref('asc')

// UI state
const currentPage = ref(1)
const itemsPerPage = 5
const loading = ref(false)
const flights = ref([])
const searched = ref(false)
const errorMessage = ref('')
const errors = ref({})
const bookingLoading = ref({})
const showFareDetails = ref({})
const selectedCurrency = ref('EUR')
const exchangeRates = ref(null)
const currencyUpdateLoading = ref(false)
const showOriginDropdown = ref(false)
const showDestinationDropdown = ref(false)


// Constants
const minDate = new Date().toISOString().split('T')[0]

const airlineNames = {
  WB: 'RwandAir', 
  QR: 'Qatar Airways', 
  KQ: 'Kenya Airways', 
  ET: 'Ethiopian Airlines',
  TK: 'Turkish Airlines', 
  KL: 'KLM', 
  LH: 'Lufthansa', 
  BA: 'British Airways', 
  AF: 'Air France',
  EK: 'Emirates',
  SQ: 'Singapore Airlines',
  CX: 'Cathay Pacific',
  DL: 'Delta Air Lines',
  UA: 'United Airlines',
  AA: 'American Airlines'
}

const airportNames = {
  KGL: 'Kigali International',
  NBO: 'Jomo Kenyatta International',
  ADD: 'Addis Ababa Bole',
  DOH: 'Hamad International',
  DXB: 'Dubai International',
  IST: 'Istanbul Airport',
  AMS: 'Amsterdam Schiphol',
  FRA: 'Frankfurt Airport',
  LHR: 'London Heathrow',
  CDG: 'Charles de Gaulle',
  JFK: 'John F. Kennedy',
  LAX: 'Los Angeles International',
  SIN: 'Singapore Changi',
  HKG: 'Hong Kong International'
}

// Comprehensive airport database
const airports = [
 // AFRICA - East Africa
  { code: 'KGL', city: 'Kigali', name: 'Kigali International Airport', country: 'Rwanda' },
  { code: 'NBO', city: 'Nairobi', name: 'Jomo Kenyatta International Airport', country: 'Kenya' },
  { code: 'ADD', city: 'Addis Ababa', name: 'Addis Ababa Bole International Airport', country: 'Ethiopia' },
  { code: 'EBB', city: 'Kampala', name: 'Entebbe International Airport', country: 'Uganda' },
  { code: 'DAR', city: 'Dar es Salaam', name: 'Julius Nyerere International Airport', country: 'Tanzania' },
  { code: 'JRO', city: 'Kilimanjaro', name: 'Kilimanjaro International Airport', country: 'Tanzania' },
  { code: 'ZNZ', city: 'Zanzibar', name: 'Zanzibar International Airport', country: 'Tanzania' },
  { code: 'ASM', city: 'Asmara', name: 'Asmara International Airport', country: 'Eritrea' },
  { code: 'JIB', city: 'Djibouti', name: 'Djibouti-Ambouli International Airport', country: 'Djibouti' },

  // AFRICA - West Africa
  { code: 'LOS', city: 'Lagos', name: 'Murtala Muhammed International Airport', country: 'Nigeria' },
  { code: 'ABV', city: 'Abuja', name: 'Nnamdi Azikiwe International Airport', country: 'Nigeria' },
  { code: 'ACC', city: 'Accra', name: 'Kotoka International Airport', country: 'Ghana' },
  { code: 'ABJ', city: 'Abidjan', name: 'Félix-Houphouët-Boigny International Airport', country: 'Ivory Coast' },
  { code: 'DKR', city: 'Dakar', name: 'Blaise Diagne International Airport', country: 'Senegal' },
  { code: 'COO', city: 'Cotonou', name: 'Cadjehoun Airport', country: 'Benin' },
  { code: 'LFW', city: 'Lome', name: 'Gnassingbé Eyadéma International Airport', country: 'Togo' },
  { code: 'OUA', city: 'Ouagadougou', name: 'Ouagadougou Airport', country: 'Burkina Faso' },
  { code: 'BKO', city: 'Bamako', name: 'Modibo Keita International Airport', country: 'Mali' },
  { code: 'NIM', city: 'Niamey', name: 'Diori Hamani International Airport', country: 'Niger' },
  { code: 'FNA', city: 'Freetown', name: 'Lungi International Airport', country: 'Sierra Leone' },
  { code: 'ROB', city: 'Monrovia', name: 'Roberts International Airport', country: 'Liberia' },
  { code: 'MLW', city: 'Monrovia', name: 'Spriggs Payne Airport', country: 'Liberia' }, 

  // AFRICA - Southern Africa
  { code: 'JNB', city: 'Johannesburg', name: 'O.R. Tambo International Airport', country: 'South Africa' },
  { code: 'CPT', city: 'Cape Town', name: 'Cape Town International Airport', country: 'South Africa' },
  { code: 'DUR', city: 'Durban', name: 'King Shaka International Airport', country: 'South Africa' },
  { code: 'HRE', city: 'Harare', name: 'Robert Gabriel Mugabe International Airport', country: 'Zimbabwe' },

  { code: 'MPM', city: 'Maputo', name: 'Maputo International Airport', country: 'Mozambique' },
  { code: 'WDH', city: 'Windhoek', name: 'Hosea Kutako International Airport', country: 'Namibia' },
  { code: 'GBE', city: 'Gaborone', name: 'Sir Seretse Khama International Airport', country: 'Botswana' },
  { code: 'BLZ', city: 'Blantyre', name: 'Chileka International Airport', country: 'Malawi' },
  // AFRICA - North Africa
  { code: 'CAI', city: 'Cairo', name: 'Cairo International Airport', country: 'Egypt' },
  { code: 'CMN', city: 'Casablanca', name: 'Mohammed V International Airport', country: 'Morocco' },
  { code: 'RAK', city: 'Marrakech', name: 'Marrakech Menara Airport', country: 'Morocco' },
  { code: 'TUN', city: 'Tunis', name: 'Tunis-Carthage International Airport', country: 'Tunisia' },
  { code: 'ALG', city: 'Algiers', name: 'Houari Boumediene Airport', country: 'Algeria' },
  { code: 'TIP', city: 'Tripoli', name: 'Mitiga International Airport', country: 'Libya' },
  { code: 'BJM', city: 'Bujumbura', name: 'Bujumbura International Airport', country: 'Burundi' },
  { code: 'HRG', city: 'Hurghada', name: 'Hurghada International Airport', country: 'Egypt' },
  { code: 'LXR', city: 'Luxor', name: 'Luxor International Airport', country: 'Egypt' },

   // MIDDLE EAST
  { code: 'DOH', city: 'Doha', name: 'Hamad International Airport', country: 'Qatar' },
  { code: 'DXB', city: 'Dubai', name: 'Dubai International Airport', country: 'UAE' },
  { code: 'AUH', city: 'Abu Dhabi', name: 'Abu Dhabi International Airport', country: 'UAE' },
  { code: 'KWI', city: 'Kuwait City', name: 'Kuwait International Airport', country: 'Kuwait' },
  { code: 'BAH', city: 'Manama', name: 'Bahrain International Airport', country: 'Bahrain' },
  { code: 'RUH', city: 'Riyadh', name: 'King Khalid International Airport', country: 'Saudi Arabia' },
  { code: 'JED', city: 'Jeddah', name: 'King Abdulaziz International Airport', country: 'Saudi Arabia' },
  { code: 'IST', city: 'Istanbul', name: 'Istanbul Airport', country: 'Turkey' },
  { code: 'TLV', city: 'Tel Aviv', name: 'Ben Gurion Airport', country: 'Israel' },
  { code: 'AMM', city: 'Amman', name: 'Queen Alia International Airport', country: 'Jordan' },
  { code: 'BEY', city: 'Beirut', name: 'Rafic Hariri International Airport', country: 'Lebanon' },
  { code: 'MCT', city: 'Muscat', name: 'Muscat International Airport', country: 'Oman' },
  
   // EUROPE - Western Europe
  { code: 'LHR', city: 'London', name: 'London Heathrow Airport', country: 'United Kingdom' },
  { code: 'LGW', city: 'London', name: 'London Gatwick Airport', country: 'United Kingdom' },
  { code: 'MAN', city: 'Manchester', name: 'Manchester Airport', country: 'United Kingdom' },
  { code: 'EDI', city: 'Edinburgh', name: 'Edinburgh Airport', country: 'United Kingdom' },
  { code: 'DUB', city: 'Dublin', name: 'Dublin Airport', country: 'Ireland' },
  { code: 'CDG', city: 'Paris', name: 'Charles de Gaulle Airport', country: 'France' },
  { code: 'ORY', city: 'Paris', name: 'Orly Airport', country: 'France' },
  { code: 'NCE', city: 'Nice', name: 'Nice Côte d\'Azur Airport', country: 'France' },
  { code: 'LYS', city: 'Lyon', name: 'Lyon-Saint Exupéry Airport', country: 'France' },
  { code: 'AMS', city: 'Amsterdam', name: 'Amsterdam Airport Schiphol', country: 'Netherlands' },
  { code: 'BRU', city: 'Brussels', name: 'Brussels Airport', country: 'Belgium' },
  { code: 'LUX', city: 'Luxembourg', name: 'Luxembourg Airport', country: 'Luxembourg' },
  { code: 'ZUR', city: 'Zurich', name: 'Zurich Airport', country: 'Switzerland' },
  { code: 'MUC', city: 'Munich', name: 'Munich Airport', country: 'Germany' },
  { code: 'FRA', city: 'Frankfurt', name: 'Frankfurt Airport', country: 'Germany' },
  { code: 'DUS', city: 'Düsseldorf', name: 'Düsseldorf Airport', country: 'Germany' },
  { code: 'HAM', city: 'Hamburg', name: 'Hamburg Airport', country: 'Germany' },
  { code: 'VIE', city: 'Vienna', name: 'Vienna International Airport', country: 'Austria' },
  { code: 'ZAG', city: 'Zagreb', name: 'Zagreb Airport', country: 'Croatia' },
  { code: 'PRG', city: 'Prague', name: 'Václav Havel Airport Prague', country: 'Czech Republic' },
  { code: 'BUD', city: 'Budapest', name: 'Budapest Ferenc Liszt International Airport', country: 'Hungary' },
  { code: 'BCN', city: 'Barcelona', name: 'Barcelona-El Prat Airport', country: 'Spain' },
  { code: 'MAD', city: 'Madrid', name: 'Adolfo Suárez Madrid-Barajas Airport', country: 'Spain' },
  { code: 'FCO', city: 'Rome', name: "Leonardo da Vinci International Airport", country:'Italy'},
  
  { code: 'FRA', city: 'Frankfurt', name: 'Frankfurt Airport', country: 'Germany' },
  { code: 'MUC', city: 'Munich', name: 'Munich Airport', country: 'Germany' },
  { code: 'BER', city: 'Berlin', name: 'Berlin Brandenburg Airport', country: 'Germany' },
  { code: 'DUS', city: 'Düsseldorf', name: 'Düsseldorf Airport', country: 'Germany' },
  { code: 'HAM', city: 'Hamburg', name: 'Hamburg Airport', country: 'Germany' },
  { code: 'VIE', city: 'Vienna', name: 'Vienna International Airport', country: 'Austria' },
  { code: 'ZUR', city: 'Zurich', name: 'Zurich Airport', country: 'Switzerland' },
  { code: 'GVA', city: 'Geneva', name: 'Geneva Airport', country: 'Switzerland' },

  // EUROPE - Nordic Countries
  { code: 'CPH', city: 'Copenhagen', name: 'Copenhagen Airport', country: 'Denmark' },
  { code: 'ARN', city: 'Stockholm', name: 'Stockholm Arlanda Airport', country: 'Sweden' },
  { code: 'OSL', city: 'Oslo', name: 'Oslo Airport', country: 'Norway' },
  { code: 'HEL', city: 'Helsinki', name: 'Helsinki-Vantaa Airport', country: 'Finland' },
  { code: 'KEF', city: 'Reykjavik', name: 'Keflavík International Airport', country: 'Iceland' },

  // EUROPE - Southern Europe
  { code: 'MAD', city: 'Madrid', name: 'Madrid-Barajas Airport', country: 'Spain' },
  { code: 'BCN', city: 'Barcelona', name: 'Barcelona-El Prat Airport', country: 'Spain' },
  { code: 'PMI', city: 'Palma', name: 'Palma de Mallorca Airport', country: 'Spain' },
  { code: 'LIS', city: 'Lisbon', name: 'Lisbon Airport', country: 'Portugal' },
  { code: 'OPO', city: 'Porto', name: 'Porto Airport', country: 'Portugal' },
  { code: 'FCO', city: 'Rome', name: 'Leonardo da Vinci International Airport', country: 'Italy' },
  { code: 'MXP', city: 'Milan', name: 'Milan Malpensa Airport', country: 'Italy' },
  { code: 'VCE', city: 'Venice', name: 'Venice Marco Polo Airport', country: 'Italy' },
  { code: 'NAP', city: 'Naples', name: 'Naples International Airport', country: 'Italy' },
  { code: 'ATH', city: 'Athens', name: 'Athens International Airport', country: 'Greece' },

  // EUROPE - Eastern Europe
  { code: 'WAW', city: 'Warsaw', name: 'Warsaw Chopin Airport', country: 'Poland' },
  { code: 'KRK', city: 'Krakow', name: 'John Paul II International Airport Kraków-Balice', country: 'Poland' },
  { code: 'PRG', city: 'Prague', name: 'Václav Havel Airport Prague', country: 'Czech Republic' },
  { code: 'BUD', city: 'Budapest', name: 'Budapest Ferenc Liszt International Airport', country: 'Hungary' },
  { code: 'OTP', city: 'Bucharest', name: 'Henri Coandă International Airport', country: 'Romania' },
  { code: 'SOF', city: 'Sofia', name: 'Sofia Airport', country: 'Bulgaria' },
  { code: 'SVO', city: 'Moscow', name: 'Sheremetyevo International Airport', country: 'Russia' },
  { code: 'LED', city: 'St. Petersburg', name: 'Pulkovo Airport', country: 'Russia' },

  // ASIA - East Asia
  { code: 'PEK', city: 'Beijing', name: 'Beijing Capital International Airport', country: 'China' },
  { code: 'PVG', city: 'Shanghai', name: 'Shanghai Pudong International Airport', country: 'China' },
  { code: 'CAN', city: 'Guangzhou', name: 'Guangzhou Tianhe International Airport', country: 'China' },
  { code: 'SZX', city: 'Shenzhen', name: 'Shenzhen Bao\'an International Airport', country: 'China' },
  { code: 'NRT', city: 'Tokyo', name: 'Narita International Airport', country: 'Japan' },
  { code: 'HND', city: 'Tokyo', name: 'Haneda Airport', country: 'Japan' },
  { code: 'KIX', city: 'Osaka', name: 'Kansai International Airport', country: 'Japan' },
  { code: 'ICN', city: 'Seoul', name: 'Incheon International Airport', country: 'South Korea' },
  { code: 'TPE', city: 'Taipei', name: 'Taiwan Taoyuan International Airport', country: 'Taiwan' },
  { code: 'HKG', city: 'Hong Kong', name: 'Hong Kong International Airport', country: 'Hong Kong' },

  // ASIA - Southeast Asia
  { code: 'SIN', city: 'Singapore', name: 'Singapore Changi Airport', country: 'Singapore' },
  { code: 'KUL', city: 'Kuala Lumpur', name: 'Kuala Lumpur International Airport', country: 'Malaysia' },
  { code: 'BKK', city: 'Bangkok', name: 'Suvarnabhumi Airport', country: 'Thailand' },
  { code: 'CGK', city: 'Jakarta', name: 'Soekarno-Hatta International Airport', country: 'Indonesia' },
  { code: 'DPS', city: 'Denpasar', name: 'Ngurah Rai International Airport', country: 'Indonesia' },
  { code: 'MNL', city: 'Manila', name: 'Ninoy Aquino International Airport', country: 'Philippines' },
  { code: 'SGN', city: 'Ho Chi Minh City', name: 'Tan Son Nhat International Airport', country: 'Vietnam' },
  { code: 'HAN', city: 'Hanoi', name: 'Noi Bai International Airport', country: 'Vietnam' },

  // ASIA - South Asia
  { code: 'DEL', city: 'New Delhi', name: 'Indira Gandhi International Airport', country: 'India' },
  { code: 'BOM', city: 'Mumbai', name: 'Chhatrapati Shivaji Maharaj International Airport', country: 'India' },
  { code: 'BLR', city: 'Bangalore', name: 'Kempegowda International Airport', country: 'India' },
  { code: 'MAA', city: 'Chennai', name: 'Chennai International Airport', country: 'India' },
  { code: 'CCU', city: 'Kolkata', name: 'Netaji Subhas Chandra Bose International Airport', country: 'India' },
  { code: 'HYD', city: 'Hyderabad', name: 'Rajiv Gandhi International Airport', country: 'India' },
  { code: 'KTM', city: 'Kathmandu', name: 'Tribhuvan International Airport', country: 'Nepal' },
  { code: 'DAC', city: 'Dhaka', name: 'Hazrat Shahjalal International Airport', country: 'Bangladesh' },
  { code: 'CMB', city: 'Colombo', name: 'Bandaranaike International Airport', country: 'Sri Lanka' },
  { code: 'MLE', city: 'Male', name: 'Velana International Airport', country: 'Maldives' },
  { code: 'ISB', city: 'Islamabad', name: 'Islamabad International Airport', country: 'Pakistan' },
  { code: 'KHI', city: 'Karachi', name: 'Jinnah International Airport', country: 'Pakistan' },

  // NORTH AMERICA
  { code: 'JFK', city: 'New York', name: 'John F. Kennedy International Airport', country: 'United States' },
  { code: 'LAX', city: 'Los Angeles', name: 'Los Angeles International Airport', country: 'United States' },
  { code: 'ORD', city: 'Chicago', name: 'O\'Hare International Airport', country: 'United States' },
  { code: 'MIA', city: 'Miami', name: 'Miami International Airport', country: 'United States' },
  { code: 'SFO', city: 'San Francisco', name: 'San Francisco International Airport', country: 'United States' },
  { code: 'SEA', city: 'Seattle', name: 'Seattle-Tacoma International Airport', country: 'United States' },
  { code: 'DEN', city: 'Denver', name: 'Denver International Airport', country: 'United States' },
  { code: 'DFW', city: 'Dallas', name: 'Dallas/Fort Worth International Airport', country: 'United States' },
  { code: 'ATL', city: 'Atlanta', name: 'Hartsfield-Jackson Atlanta International Airport', country: 'United States' },
  { code: 'BOS', city: 'Boston', name: 'Logan International Airport', country: 'United States' },
  { code: 'LAS', city: 'Las Vegas', name: 'Harry Reid International Airport', country: 'United States' },
  { code: 'PHX', city: 'Phoenix', name: 'Phoenix Sky Harbor International Airport', country: 'United States' },
  { code: 'YYZ', city: 'Toronto', name: 'Toronto Pearson International Airport', country: 'Canada' },
  { code: 'YVR', city: 'Vancouver', name: 'Vancouver International Airport', country: 'Canada' },
  { code: 'YUL', city: 'Montreal', name: 'Montréal-Pierre Elliott Trudeau International Airport', country: 'Canada' },
  { code: 'MEX', city: 'Mexico City', name: 'Mexico City International Airport', country: 'Mexico' },
  { code: 'CUN', city: 'Cancun', name: 'Cancún International Airport', country: 'Mexico' },

  // SOUTH AMERICA
  { code: 'GRU', city: 'São Paulo', name: 'São Paulo/Guarulhos International Airport', country: 'Brazil' },
  { code: 'GIG', city: 'Rio de Janeiro', name: 'Rio de Janeiro–Galeão International Airport', country: 'Brazil' },
  { code: 'BSB', city: 'Brasília', name: 'Brasília International Airport', country: 'Brazil' },
  { code: 'EZE', city: 'Buenos Aires', name: 'Ezeiza International Airport', country: 'Argentina' },
  { code: 'SCL', city: 'Santiago', name: 'Santiago International Airport', country: 'Chile' },
  { code: 'LIM', city: 'Lima', name: 'Jorge Chávez International Airport', country: 'Peru' },
  { code: 'BOG', city: 'Bogotá', name: 'El Dorado International Airport', country: 'Colombia' },
  { code: 'UIO', city: 'Quito', name: 'Mariscal Sucre International Airport', country: 'Ecuador' },
  { code: 'CCS', city: 'Caracas', name: 'Simón Bolívar International Airport', country: 'Venezuela' },

  // OCEANIA
  { code: 'SYD', city: 'Sydney', name: 'Sydney Kingsford Smith Airport', country: 'Australia' },
  { code: 'MEL', city: 'Melbourne', name: 'Melbourne Airport', country: 'Australia' },
  { code: 'BNE', city: 'Brisbane', name: 'Brisbane Airport', country: 'Australia' },
  { code: 'PER', city: 'Perth', name: 'Perth Airport', country: 'Australia' },
  { code: 'AKL', city: 'Auckland', name: 'Auckland Airport', country: 'New Zealand' },
  { code: 'CHC', city: 'Christchurch', name: 'Christchurch Airport', country: 'New Zealand' },
  { code: 'NAN', city: 'Nadi', name: 'Nadi International Airport', country: 'Fiji' },
  { code: 'PPT', city: 'Tahiti', name: 'Tahiti Faa\'a International Airport', country: 'French Polynesia' },

  { code: 'BEN', city: 'Benghazi', name: 'Benina International Airport', country: 'Libya' },
  { code: 'KRT', city: 'Khartoum', name: 'Khartoum International Airport', country: 'Sudan' },

  // MIDDLE EAST
  { code: 'DOH', city: 'Doha', name: 'Hamad International Airport', country: 'Qatar' },
  { code: 'DXB', city: 'Dubai', name: 'Dubai International Airport', country: 'UAE' },
  { code: 'AUH', city: 'Abu Dhabi', name: 'Abu Dhabi International Airport', country: 'UAE' },
  { code: 'SHJ', city: 'Sharjah', name: 'Sharjah International Airport', country: 'UAE' },
  { code: 'RAS', city: 'Ras Al Khaimah', name: 'Ras Al Khaimah International Airport', country: 'UAE' },
  { code: 'KWI', city: 'Kuwait City', name: 'Kuwait International Airport', country: 'Kuwait' },
  { code: 'BAH', city: 'Manama', name: 'Bahrain International Airport', country: 'Bahrain' },
  { code: 'RUH', city: 'Riyadh', name: 'King Khalid International Airport', country: 'Saudi Arabia' },
  { code: 'JED', city: 'Jeddah', name: 'King Abdulaziz International Airport', country: 'Saudi Arabia' },
  { code: 'DMM', city: 'Dammam', name: 'King Fahd International Airport', country: 'Saudi Arabia' },
  { code: 'MED', city: 'Medina', name: 'Prince Mohammad Bin Abdulaziz Airport', country: 'Saudi Arabia' },
  { code: 'IST', city: 'Istanbul', name: 'Istanbul Airport', country: 'Turkey' },
  { code: 'SAW', city: 'Istanbul', name: 'Sabiha Gökçen International Airport', country: 'Turkey' },
  { code: 'ESB', city: 'Ankara', name: 'Esenboğa Airport', country: 'Turkey' },
  { code: 'ADB', city: 'Izmir', name: 'Adnan Menderes Airport', country: 'Turkey' },
  { code: 'AYT', city: 'Antalya', name: 'Antalya Airport', country: 'Turkey' },
  { code: 'TLV', city: 'Tel Aviv', name: 'Ben Gurion Airport', country: 'Israel' },
  { code: 'AMM', city: 'Amman', name: 'Queen Alia International Airport', country: 'Jordan' },
  { code: 'BEY', city: 'Beirut', name: 'Rafic Hariri International Airport', country: 'Lebanon' },
  { code: 'DAM', city: 'Damascus', name: 'Damascus International Airport', country: 'Syria' },
  { code: 'BGW', city: 'Baghdad', name: 'Baghdad International Airport', country: 'Iraq' },
  { code: 'EBL', city: 'Erbil', name: 'Erbil International Airport', country: 'Iraq' },
  { code: 'IKA', city: 'Tehran', name: 'Imam Khomeini International Airport', country: 'Iran' },
  { code: 'SYZ', city: 'Shiraz', name: 'Shiraz International Airport', country: 'Iran' },
  { code: 'MCT', city: 'Muscat', name: 'Muscat International Airport', country: 'Oman' },
  { code: 'SLL', city: 'Salalah', name: 'Salalah Airport', country: 'Oman' },
  { code: 'SAH', city: 'Sanaa', name: 'Sanaa International Airport', country: 'Yemen' },
  { code: 'ADE', city: 'Aden', name: 'Aden International Airport', country: 'Yemen' },

  // EUROPE - Western Europe
  { code: 'LHR', city: 'London', name: 'London Heathrow Airport', country: 'United Kingdom' },
  { code: 'LGW', city: 'London', name: 'London Gatwick Airport', country: 'United Kingdom' },
  { code: 'STN', city: 'London', name: 'London Stansted Airport', country: 'United Kingdom' },
  { code: 'LTN', city: 'London', name: 'London Luton Airport', country: 'United Kingdom' },
  { code: 'LCY', city: 'London', name: 'London City Airport', country: 'United Kingdom' },
  { code: 'MAN', city: 'Manchester', name: 'Manchester Airport', country: 'United Kingdom' },
  { code: 'EDI', city: 'Edinburgh', name: 'Edinburgh Airport', country: 'United Kingdom' },
  { code: 'GLA', city: 'Glasgow', name: 'Glasgow Airport', country: 'United Kingdom' },
  { code: 'BHX', city: 'Birmingham', name: 'Birmingham Airport', country: 'United Kingdom' },
  { code: 'LPL', city: 'Liverpool', name: 'Liverpool John Lennon Airport', country: 'United Kingdom' },
  { code: 'DUB', city: 'Dublin', name: 'Dublin Airport', country: 'Ireland' },
  { code: 'ORK', city: 'Cork', name: 'Cork Airport', country: 'Ireland' },
  { code: 'CDG', city: 'Paris', name: 'Charles de Gaulle Airport', country: 'France' },
  { code: 'ORY', city: 'Paris', name: 'Orly Airport', country: 'France' },
  { code: 'LYS', city: 'Lyon', name: 'Lyon-Saint Exupéry Airport', country: 'France' },
  { code: 'MRS', city: 'Marseille', name: 'Marseille Provence Airport', country: 'France' },
  { code: 'NCE', city: 'Nice', name: 'Nice Côte d\'Azur Airport', country: 'France' },
  { code: 'TLS', city: 'Toulouse', name: 'Toulouse-Blagnac Airport', country: 'France' },
  { code: 'BOD', city: 'Bordeaux', name: 'Bordeaux-Mérignac Airport', country: 'France' },
  { code: 'NTE', city: 'Nantes', name: 'Nantes Atlantique Airport', country: 'France' },
  { code: 'AMS', city: 'Amsterdam', name: 'Amsterdam Airport Schiphol', country: 'Netherlands' },
  { code: 'EIN', city: 'Eindhoven', name: 'Eindhoven Airport', country: 'Netherlands' },
  { code: 'RTM', city: 'Rotterdam', name: 'Rotterdam The Hague Airport', country: 'Netherlands' },
  { code: 'BRU', city: 'Brussels', name: 'Brussels Airport', country: 'Belgium' },
  { code: 'CRL', city: 'Brussels', name: 'Brussels South Charleroi Airport', country: 'Belgium' },
  { code: 'ANR', city: 'Antwerp', name: 'Antwerp International Airport', country: 'Belgium' },
  { code: 'LUX', city: 'Luxembourg', name: 'Luxembourg Airport', country: 'Luxembourg' },

  // EUROPE - Germanic Europe
  { code: 'FRA', city: 'Frankfurt', name: 'Frankfurt Airport', country: 'Germany' },
  { code: 'MUC', city: 'Munich', name: 'Munich Airport', country: 'Germany' },
  { code: 'DUS', city: 'Düsseldorf', name: 'Düsseldorf Airport', country: 'Germany' },
  { code: 'TXL', city: 'Berlin', name: 'Berlin Tegel Airport', country: 'Germany' },
  { code: 'BER', city: 'Berlin', name: 'Berlin Brandenburg Airport', country: 'Germany' },
  { code: 'HAM', city: 'Hamburg', name: 'Hamburg Airport', country: 'Germany' },
  { code: 'CGN', city: 'Cologne', name: 'Cologne Bonn Airport', country: 'Germany' },
  { code: 'STR', city: 'Stuttgart', name: 'Stuttgart Airport', country: 'Germany' },
  { code: 'HAJ', city: 'Hannover', name: 'Hannover Airport', country: 'Germany' },
  { code: 'NUE', city: 'Nuremberg', name: 'Nuremberg Airport', country: 'Germany' },
  { code: 'VIE', city: 'Vienna', name: 'Vienna International Airport', country: 'Austria' },
  { code: 'SZG', city: 'Salzburg', name: 'Salzburg Airport', country: 'Austria' },
  { code: 'INN', city: 'Innsbruck', name: 'Innsbruck Airport', country: 'Austria' },
  { code: 'GRZ', city: 'Graz', name: 'Graz Airport', country: 'Austria' },
  { code: 'ZUR', city: 'Zurich', name: 'Zurich Airport', country: 'Switzerland' },
  { code: 'GVA', city: 'Geneva', name: 'Geneva Airport', country: 'Switzerland' },
  { code: 'BSL', city: 'Basel', name: 'EuroAirport Basel Mulhouse Freiburg', country: 'Switzerland' },
  { code: 'BRN', city: 'Bern', name: 'Bern Airport', country: 'Switzerland' },

  // EUROPE - Nordic Countries
  { code: 'CPH', city: 'Copenhagen', name: 'Copenhagen Airport', country: 'Denmark' },
  { code: 'AAL', city: 'Aalborg', name: 'Aalborg Airport', country: 'Denmark' },
  { code: 'BLL', city: 'Billund', name: 'Billund Airport', country: 'Denmark' },
  { code: 'ARN', city: 'Stockholm', name: 'Stockholm Arlanda Airport', country: 'Sweden' },
  { code: 'BMA', city: 'Stockholm', name: 'Stockholm Bromma Airport', country: 'Sweden' },
  { code: 'GOT', city: 'Gothenburg', name: 'Gothenburg Landvetter Airport', country: 'Sweden' },
  { code: 'MMX', city: 'Malmö', name: 'Malmö Airport', country: 'Sweden' },
  { code: 'OSL', city: 'Oslo', name: 'Oslo Airport', country: 'Norway' },
  { code: 'BGO', city: 'Bergen', name: 'Bergen Airport', country: 'Norway' },
  { code: 'TRD', city: 'Trondheim', name: 'Trondheim Airport', country: 'Norway' },
  { code: 'SVG', city: 'Stavanger', name: 'Stavanger Airport', country: 'Norway' },
  { code: 'HEL', city: 'Helsinki', name: 'Helsinki-Vantaa Airport', country: 'Finland' },
  { code: 'TMP', city: 'Tampere', name: 'Tampere-Pirkkala Airport', country: 'Finland' },
  { code: 'OUL', city: 'Oulu', name: 'Oulu Airport', country: 'Finland' },
  { code: 'KEF', city: 'Reykjavik', name: 'Keflavík International Airport', country: 'Iceland' },
  { code: 'RKV', city: 'Reykjavik', name: 'Reykjavík Airport', country: 'Iceland' },

  // EUROPE - Southern Europe
  { code: 'MAD', city: 'Madrid', name: 'Madrid-Barajas Airport', country: 'Spain' },
  { code: 'BCN', city: 'Barcelona', name: 'Barcelona-El Prat Airport', country: 'Spain' },
  { code: 'PMI', city: 'Palma', name: 'Palma de Mallorca Airport', country: 'Spain' },
  { code: 'VLC', city: 'Valencia', name: 'Valencia Airport', country: 'Spain' },
  { code: 'SVQ', city: 'Seville', name: 'Seville Airport', country: 'Spain' },
  { code: 'BIO', city: 'Bilbao', name: 'Bilbao Airport', country: 'Spain' },
  { code: 'LPA', city: 'Las Palmas', name: 'Las Palmas Airport', country: 'Spain' },
  { code: 'TFS', city: 'Tenerife', name: 'Tenerife South Airport', country: 'Spain' },
  { code: 'LIS', city: 'Lisbon', name: 'Lisbon Airport', country: 'Portugal' },
  { code: 'OPO', city: 'Porto', name: 'Porto Airport', country: 'Portugal' },
  { code: 'FAO', city: 'Faro', name: 'Faro Airport', country: 'Portugal' },
  { code: 'FNC', city: 'Funchal', name: 'Madeira Airport', country: 'Portugal' },
  { code: 'FCO', city: 'Rome', name: 'Leonardo da Vinci International Airport', country: 'Italy' },
  { code: 'CIA', city: 'Rome', name: 'Ciampino Airport', country: 'Italy' },
  { code: 'MXP', city: 'Milan', name: 'Milan Malpensa Airport', country: 'Italy' },
  { code: 'LIN', city: 'Milan', name: 'Milan Linate Airport', country: 'Italy' },
  { code: 'BGY', city: 'Milan', name: 'Milan Bergamo Airport', country: 'Italy' },
  { code: 'VCE', city: 'Venice', name: 'Venice Marco Polo Airport', country: 'Italy' },
  { code: 'NAP', city: 'Naples', name: 'Naples International Airport', country: 'Italy' },
  { code: 'CTA', city: 'Catania', name: 'Catania-Fontanarossa Airport', country: 'Italy' },
  { code: 'PMO', city: 'Palermo', name: 'Palermo Airport', country: 'Italy' },
  { code: 'BLQ', city: 'Bologna', name: 'Bologna Guglielmo Marconi Airport', country: 'Italy' },
  { code: 'FLR', city: 'Florence', name: 'Florence Airport', country: 'Italy' },
  { code: 'ATH', city: 'Athens', name: 'Athens International Airport', country: 'Greece' },
  { code: 'SKG', city: 'Thessaloniki', name: 'Thessaloniki Airport', country: 'Greece' },
  { code: 'HER', city: 'Heraklion', name: 'Heraklion International Airport', country: 'Greece' },
  { code: 'RHO', city: 'Rhodes', name: 'Rhodes International Airport', country: 'Greece' },
  { code: 'JMK', city: 'Mykonos', name: 'Mykonos Airport', country: 'Greece' },
  { code: 'JTR', city: 'Santorini', name: 'Santorini Airport', country: 'Greece' },

  // EUROPE - Eastern Europe
  { code: 'WAW', city: 'Warsaw', name: 'Warsaw Chopin Airport', country: 'Poland' },
  { code: 'KRK', city: 'Krakow', name: 'John Paul II International Airport Kraków-Balice', country: 'Poland' },
  { code: 'GDN', city: 'Gdansk', name: 'Gdańsk Lech Wałęsa Airport', country: 'Poland' },
  { code: 'WRO', city: 'Wroclaw', name: 'Wrocław Airport', country: 'Poland' },
  { code: 'POZ', city: 'Poznan', name: 'Poznań-Ławica Airport', country: 'Poland' },
  { code: 'PRG', city: 'Prague', name: 'Václav Havel Airport Prague', country: 'Czech Republic' },
  { code: 'BRQ', city: 'Brno', name: 'Brno-Tuřany Airport', country: 'Czech Republic' },
  { code: 'BUD', city: 'Budapest', name: 'Budapest Ferenc Liszt International Airport', country: 'Hungary' },
  { code: 'DEB', city: 'Debrecen', name: 'Debrecen International Airport', country: 'Hungary' },
  { code: 'BTS', city: 'Bratislava', name: 'M. R. Štefánik Airport', country: 'Slovakia' },
  { code: 'LJU', city: 'Ljubljana', name: 'Ljubljana Jože Pučnik Airport', country: 'Slovenia' },
  { code: 'ZAG', city: 'Zagreb', name: 'Zagreb Airport', country: 'Croatia' },
  { code: 'SPU', city: 'Split', name: 'Split Airport', country: 'Croatia' },
  { code: 'DBV', city: 'Dubrovnik', name: 'Dubrovnik Airport', country: 'Croatia' },
  { code: 'PUY', city: 'Pula', name: 'Pula Airport', country: 'Croatia' },
  { code: 'BEG', city: 'Belgrade', name: 'Belgrade Nikola Tesla Airport', country: 'Serbia' },
  { code: 'SJJ', city: 'Sarajevo', name: 'Sarajevo International Airport', country: 'Bosnia and Herzegovina' },
  { code: 'SKP', city: 'Skopje', name: 'Skopje Alexander the Great Airport', country: 'North Macedonia' },
  { code: 'TGD', city: 'Podgorica', name: 'Podgorica Airport', country: 'Montenegro' },
  { code: 'PRN', city: 'Pristina', name: 'Pristina International Airport', country: 'Kosovo' },
  { code: 'TIA', city: 'Tirana', name: 'Tirana International Airport', country: 'Albania' },
  { code: 'OTP', city: 'Bucharest', name: 'Henri Coandă International Airport', country: 'Romania' },
  { code: 'CLJ', city: 'Cluj-Napoca', name: 'Cluj-Napoca International Airport', country: 'Romania' },
  { code: 'TSR', city: 'Timișoara', name: 'Timișoara Traian Vuia International Airport', country: 'Romania' },
  { code: 'SOF', city: 'Sofia', name: 'Sofia Airport', country: 'Bulgaria' },
  { code: 'VAR', city: 'Varna', name: 'Varna Airport', country: 'Bulgaria' },
  { code: 'BOJ', city: 'Burgas', name: 'Burgas Airport', country: 'Bulgaria' },
  { code: 'SVO', city: 'Moscow', name: 'Sheremetyevo International Airport', country: 'Russia' },
  { code: 'DME', city: 'Moscow', name: 'Domodedovo International Airport', country: 'Russia' },
  { code: 'VKO', city: 'Moscow', name: 'Vnukovo International Airport', country: 'Russia' },
  { code: 'LED', city: 'St. Petersburg', name: 'Pulkovo Airport', country: 'Russia' },
  { code: 'KZN', city: 'Kazan', name: 'Kazan International Airport', country: 'Russia' },
  { code: 'ROV', city: 'Rostov-on-Don', name: 'Platov International Airport', country: 'Russia' },
  { code: 'KBP', city: 'Kyiv', name: 'Boryspil International Airport', country: 'Ukraine' },
   { code: 'KZN', city: 'Kazan', name: 'Kazan International Airport', country: 'Russia' },
  { code: 'ROV', city: 'Rostov-on-Don', name: 'Platov International Airport', country: 'Russia' },
  { code: 'KBP', city: 'Kyiv', name: 'Boryspil International Airport', country: 'Ukraine' },
  { code: 'IEV', city: 'Kyiv', name: 'Igor Sikorsky Kyiv International Airport', country: 'Ukraine' },
  { code: 'LWO', city: 'Lviv', name: 'Lviv Danylo Halytskyi International Airport', country: 'Ukraine' },
  { code: 'ODS', city: 'Odessa', name: 'Odessa International Airport', country: 'Ukraine' },
  { code: 'MSQ', city: 'Minsk', name: 'Minsk National Airport', country: 'Belarus' },
  { code: 'RIX', city: 'Riga', name: 'Riga International Airport', country: 'Latvia' },
  { code: 'TLL', city: 'Tallinn', name: 'Lennart Meri Tallinn Airport', country: 'Estonia' },
  { code: 'VNO', city: 'Vilnius', name: 'Vilnius Airport', country: 'Lithuania' },
  { code: 'KUN', city: 'Kaunas', name: 'Kaunas Airport', country: 'Lithuania' },
  { code: 'ZAG', city: 'Zagreb', name: 'Zagreb Airport', country: 'Croatia' },
  { code: 'SPU', city: 'Split', name: 'Split Airport', country: 'Croatia' },
  { code: 'DBV', city: 'Dubrovnik', name: 'Dubrovnik Airport', country: 'Croatia' },
  { code: 'BEG', city: 'Belgrade', name: 'Belgrade Nikola Tesla Airport', country: 'Serbia' },
  { code: 'SJJ', city: 'Sarajevo', name: 'Sarajevo International Airport', country: 'Bosnia and Herzegovina' },
  { code: 'SKP', city: 'Skopje', name: 'Skopje Alexander the Great Airport', country: 'North Macedonia' },
  { code: 'TGD', city: 'Podgorica', name: 'Podgorica Airport', country: 'Montenegro' },
  { code: 'TIA', city: 'Tirana', name: 'Tirana International Airport', country: 'Albania' },
  { code: 'LJU', city: 'Ljubljana', name: 'Ljubljana Jože Pučnik Airport', country: 'Slovenia' },
  { code: 'BTS', city: 'Bratislava', name: 'M. R. Štefánik Airport', country: 'Slovakia' },
  { code: 'CTU', city: 'Chengdu', name: 'Chengdu Shuangliu International Airport', country: 'China' },
  { code: 'XIY', city: 'Xi\'an', name: 'Xi\'an Xianyang International Airport', country: 'China' },
  { code: 'KMG', city: 'Kunming', name: 'Kunming Changshui International Airport', country: 'China' },
  { code: 'WUH', city: 'Wuhan', name: 'Wuhan Tianhe International Airport', country: 'China' },
  { code: 'CSX', city: 'Changsha', name: 'Changsha Huanghua International Airport', country: 'China' },
  { code: 'NKG', city: 'Nanjing', name: 'Nanjing Lukou International Airport', country: 'China' },
  { code: 'HGH', city: 'Hangzhou', name: 'Hangzhou Xiaoshan International Airport', country: 'China' },
  { code: 'SHA', city: 'Shanghai', name: 'Shanghai Hongqiao International Airport', country: 'China' },
  { code: 'PKX', city: 'Beijing', name: 'Beijing Daxing International Airport', country: 'China' },
  { code: 'ITM', city: 'Osaka', name: 'Osaka International Airport', country: 'Japan' },
  { code: 'NGO', city: 'Nagoya', name: 'Chubu Centrair International Airport', country: 'Japan' },
  { code: 'FUK', city: 'Fukuoka', name: 'Fukuoka Airport', country: 'Japan' },
  { code: 'CTS', city: 'Sapporo', name: 'New Chitose Airport', country: 'Japan' },
  { code: 'OKA', city: 'Okinawa', name: 'Naha Airport', country: 'Japan' },
  { code: 'GMP', city: 'Seoul', name: 'Gimpo International Airport', country: 'South Korea' },
  { code: 'PUS', city: 'Busan', name: 'Gimhae International Airport', country: 'South Korea' },
  { code: 'CJU', city: 'Jeju', name: 'Jeju International Airport', country: 'South Korea' },
  { code: 'TSA', city: 'Taipei', name: 'Taipei Songshan Airport', country: 'Taiwan' },
  { code: 'KHH', city: 'Kaohsiung', name: 'Kaohsiung International Airport', country: 'Taiwan' },
  { code: 'MFM', city: 'Macau', name: 'Macau International Airport', country: 'Macau' },
  { code: 'ULN', city: 'Ulaanbaatar', name: 'Chinggis Khaan International Airport', country: 'Mongolia' },
   // ASIA - Southeast Asia (expanded)
  { code: 'SZB', city: 'Kuala Lumpur', name: 'Sultan Abdul Aziz Shah Airport', country: 'Malaysia' },
  { code: 'PEN', city: 'Penang', name: 'Penang International Airport', country: 'Malaysia' },
  { code: 'JHB', city: 'Johor Bahru', name: 'Senai International Airport', country: 'Malaysia' },
  { code: 'KCH', city: 'Kuching', name: 'Kuching International Airport', country: 'Malaysia' },
  { code: 'BKI', city: 'Kota Kinabalu', name: 'Kota Kinabalu International Airport', country: 'Malaysia' },
  { code: 'DMK', city: 'Bangkok', name: 'Don Mueang International Airport', country: 'Thailand' },
  { code: 'CNX', city: 'Chiang Mai', name: 'Chiang Mai International Airport', country: 'Thailand' },
  { code: 'HKT', city: 'Phuket', name: 'Phuket International Airport', country: 'Thailand' },
  { code: 'USM', city: 'Koh Samui', name: 'Samui Airport', country: 'Thailand' },
  { code: 'UTP', city: 'U-Tapao', name: 'U-Tapao International Airport', country: 'Thailand' },
  { code: 'HLP', city: 'Jakarta', name: 'Halim Perdanakusuma International Airport', country: 'Indonesia' },
  { code: 'SUB', city: 'Surabaya', name: 'Juanda International Airport', country: 'Indonesia' },
  { code: 'MDC', city: 'Manado', name: 'Sam Ratulangi International Airport', country: 'Indonesia' },
  { code: 'PLM', city: 'Palembang', name: 'Sultan Mahmud Badaruddin II International Airport', country: 'Indonesia' },
  { code: 'PKU', city: 'Pekanbaru', name: 'Sultan Syarif Kasim II International Airport', country: 'Indonesia' },
  { code: 'CEB', city: 'Cebu', name: 'Mactan-Cebu International Airport', country: 'Philippines' },
  { code: 'DVO', city: 'Davao', name: 'Francisco Bangoy International Airport', country: 'Philippines' },
  { code: 'ILO', city: 'Iloilo', name: 'Iloilo International Airport', country: 'Philippines' },
  { code: 'CRK', city: 'Clark', name: 'Clark International Airport', country: 'Philippines' },
  { code: 'DAD', city: 'Da Nang', name: 'Da Nang International Airport', country: 'Vietnam' },
  { code: 'CXR', city: 'Nha Trang', name: 'Cam Ranh International Airport', country: 'Vietnam' },
  { code: 'PQC', city: 'Phu Quoc', name: 'Phu Quoc International Airport', country: 'Vietnam' },
  { code: 'PNH', city: 'Phnom Penh', name: 'Phnom Penh International Airport', country: 'Cambodia' },
  { code: 'REP', city: 'Siem Reap', name: 'Siem Reap International Airport', country: 'Cambodia' },
  { code: 'VTE', city: 'Vientiane', name: 'Wattay International Airport', country: 'Laos' },
  { code: 'LPQ', city: 'Luang Prabang', name: 'Luang Prabang International Airport', country: 'Laos' },
  { code: 'RGN', city: 'Yangon', name: 'Yangon International Airport', country: 'Myanmar' },
  { code: 'MDL', city: 'Mandalay', name: 'Mandalay International Airport', country: 'Myanmar' },
  { code: 'BWN', city: 'Bandar Seri Begawan', name: 'Brunei International Airport', country: 'Brunei' },

  // ASIA - South Asia (expanded)
  { code: 'AMD', city: 'Ahmedabad', name: 'Sardar Vallabhbhai Patel International Airport', country: 'India' },
  { code: 'PNQ', city: 'Pune', name: 'Pune Airport', country: 'India' },
  { code: 'GOI', city: 'Goa', name: 'Goa International Airport', country: 'India' },
  { code: 'COK', city: 'Kochi', name: 'Cochin International Airport', country: 'India' },
  { code: 'TRV', city: 'Thiruvananthapuram', name: 'Trivandrum International Airport', country: 'India' },
  { code: 'JAI', city: 'Jaipur', name: 'Jaipur International Airport', country: 'India' },
  { code: 'LKO', city: 'Lucknow', name: 'Chaudhary Charan Singh International Airport', country: 'India' },
  { code: 'IXC', city: 'Chandigarh', name: 'Chandigarh Airport', country: 'India' },
  { code: 'SXR', city: 'Srinagar', name: 'Sheikh ul-Alam International Airport', country: 'India' },
  { code: 'IXL', city: 'Leh', name: 'Kushok Bakula Rimpochee Airport', country: 'India' },
  { code: 'PKR', city: 'Pokhara', name: 'Pokhara International Airport', country: 'Nepal' },
  { code: 'CGP', city: 'Chittagong', name: 'Shah Amanat International Airport', country: 'Bangladesh' },
  { code: 'CXB', city: 'Cox\'s Bazar', name: 'Cox\'s Bazar Airport', country: 'Bangladesh' },
  { code: 'RML', city: 'Colombo', name: 'Ratmalana Airport', country: 'Sri Lanka' },
  { code: 'HRI', city: 'Mattala', name: 'Mattala Rajapaksa International Airport', country: 'Sri Lanka' },
  { code: 'GAN', city: 'Gan', name: 'Gan International Airport', country: 'Maldives' },
  { code: 'LHE', city: 'Lahore', name: 'Allama Iqbal International Airport', country: 'Pakistan' },
  { code: 'PEW', city: 'Peshawar', name: 'Bacha Khan International Airport', country: 'Pakistan' },

  // ASIA - Central Asia
  { code: 'FRU', city: 'Bishkek', name: 'Manas International Airport', country: 'Kyrgyzstan' },
  { code: 'OSS', city: 'Osh', name: 'Osh Airport', country: 'Kyrgyzstan' },
  { code: 'DYU', city: 'Dushanbe', name: 'Dushanbe Airport', country: 'Tajikistan' },
  { code: 'ASB', city: 'Ashgabat', name: 'Oguzhan Airport', country: 'Turkmenistan' },
  { code: 'TAS', city: 'Tashkent', name: 'Islam Karimov Tashkent International Airport', country: 'Uzbekistan' },
  { code: 'SKD', city: 'Samarkand', name: 'Samarkand Airport', country: 'Uzbekistan' },
  { code: 'ALA', city: 'Almaty', name: 'Almaty Airport', country: 'Kazakhstan' },
  { code: 'NQZ', city: 'Nur-Sultan', name: 'Nur-Sultan Nazarbayev International Airport', country: 'Kazakhstan' },
  { code: 'KBL', city: 'Kabul', name: 'Hamid Karzai International Airport', country: 'Afghanistan' },

  // NORTH AMERICA - United States (expanded)
  { code: 'LGA', city: 'New York', name: 'LaGuardia Airport', country: 'United States' },
  { code: 'EWR', city: 'Newark', name: 'Newark Liberty International Airport', country: 'United States' },
  { code: 'BUR', city: 'Burbank', name: 'Hollywood Burbank Airport', country: 'United States' },
  { code: 'LGB', city: 'Long Beach', name: 'Long Beach Airport', country: 'United States' },
  { code: 'SNA', city: 'Orange County', name: 'John Wayne Airport', country: 'United States' },
  { code: 'MDW', city: 'Chicago', name: 'Midway International Airport', country: 'United States' },
  { code: 'FLL', city: 'Fort Lauderdale', name: 'Fort Lauderdale-Hollywood International Airport', country: 'United States' },
  { code: 'PBI', city: 'West Palm Beach', name: 'Palm Beach International Airport', country: 'United States' },
  { code: 'MCO', city: 'Orlando', name: 'Orlando International Airport', country: 'United States' },
  { code: 'OAK', city: 'Oakland', name: 'Oakland International Airport', country: 'United States' },
  { code: 'SJC', city: 'San Jose', name: 'Norman Y. Mineta San José International Airport', country: 'United States' },
  { code: 'DAL', city: 'Dallas', name: 'Dallas Love Field', country: 'United States' },
  { code: 'IAH', city: 'Houston', name: 'George Bush Intercontinental Airport', country: 'United States' },
  { code: 'HOU', city: 'Houston', name: 'William P. Hobby Airport', country: 'United States' },
  { code: 'SAN', city: 'San Diego', name: 'San Diego International Airport', country: 'United States' },
  { code: 'BWI', city: 'Baltimore', name: 'Baltimore/Washington International Airport', country: 'United States' },
  { code: 'DCA', city: 'Washington', name: 'Ronald Reagan Washington National Airport', country: 'United States' },
  { code: 'IAD', city: 'Washington', name: 'Washington Dulles International Airport', country: 'United States' },
  { code: 'CLT', city: 'Charlotte', name: 'Charlotte Douglas International Airport', country: 'United States' },
  { code: 'MSP', city: 'Minneapolis', name: 'Minneapolis-Saint Paul International Airport', country: 'United States' },
  { code: 'DTW', city: 'Detroit', name: 'Detroit Metropolitan Wayne County Airport', country: 'United States' },
  { code: 'PHL', city: 'Philadelphia', name: 'Philadelphia International Airport', country: 'United States' },
  { code: 'SLC', city: 'Salt Lake City', name: 'Salt Lake City International Airport', country: 'United States' },
  { code: 'PDX', city: 'Portland', name: 'Portland International Airport', country: 'United States' },
  { code: 'MSY', city: 'New Orleans', name: 'Louis Armstrong New Orleans International Airport', country: 'United States' },
  { code: 'BNA', city: 'Nashville', name: 'Nashville International Airport', country: 'United States' },
  { code: 'RDU', city: 'Raleigh', name: 'Raleigh-Durham International Airport', country: 'United States' },
  { code: 'TPA', city: 'Tampa', name: 'Tampa International Airport', country: 'United States' },
  { code: 'ANC', city: 'Anchorage', name: 'Ted Stevens Anchorage International Airport', country: 'United States' },
  { code: 'HNL', city: 'Honolulu', name: 'Daniel K. Inouye International Airport', country: 'United States' },
  { code: 'OGG', city: 'Maui', name: 'Kahului Airport', country: 'United States' },
  { code: 'KOA', city: 'Kona', name: 'Ellison Onizuka Kona International Airport', country: 'United States' },

  // NORTH AMERICA - Canada (expanded)
  { code: 'YTZ', city: 'Toronto', name: 'Billy Bishop Toronto City Airport', country: 'Canada' },
  { code: 'YYC', city: 'Calgary', name: 'Calgary International Airport', country: 'Canada' },
  { code: 'YEG', city: 'Edmonton', name: 'Edmonton International Airport', country: 'Canada' },
  { code: 'YOW', city: 'Ottawa', name: 'Ottawa Macdonald-Cartier International Airport', country: 'Canada' },
  { code: 'YWG', city: 'Winnipeg', name: 'Winnipeg James Armstrong Richardson International Airport', country: 'Canada' },
  { code: 'YHZ', city: 'Halifax', name: 'Halifax Stanfield International Airport', country: 'Canada' },
  { code: 'YQB', city: 'Quebec City', name: 'Québec City Jean Lesage International Airport', country: 'Canada' },
  { code: 'YXE', city: 'Saskatoon', name: 'Saskatoon John G. Diefenbaker International Airport', country: 'Canada' },

  // NORTH AMERICA - Mexico (expanded)
  { code: 'GDL', city: 'Guadalajara', name: 'Guadalajara International Airport', country: 'Mexico' },
  { code: 'MTY', city: 'Monterrey', name: 'Monterrey International Airport', country: 'Mexico' },
  { code: 'PVR', city: 'Puerto Vallarta', name: 'Licenciado Gustavo Díaz Ordaz International Airport', country: 'Mexico' },
  { code: 'SJD', city: 'Los Cabos', name: 'Los Cabos International Airport', country: 'Mexico' },
  { code: 'CZM', city: 'Cozumel', name: 'Cozumel International Airport', country: 'Mexico' },
  { code: 'MZT', city: 'Mazatlán', name: 'General Rafael Buelna International Airport', country: 'Mexico' },
  { code: 'ACA', city: 'Acapulco', name: 'General Juan N. Álvarez International Airport', country: 'Mexico' },
  { code: 'MID', city: 'Merida', name: 'Manuel Crescencio Rejón International Airport', country: 'Mexico' },
  { code: 'TIJ', city: 'Tijuana', name: 'General Abelardo L. Rodríguez International Airport', country: 'Mexico' },

  // SOUTH AMERICA (expanded)
  { code: 'CGH', city: 'São Paulo', name: 'Congonhas Airport', country: 'Brazil' },
  { code: 'SDU', city: 'Rio de Janeiro', name: 'Santos Dumont Airport', country: 'Brazil' },
  { code: 'SSA', city: 'Salvador', name: 'Deputado Luís Eduardo Magalhães International Airport', country: 'Brazil' },
  { code: 'FOR', city: 'Fortaleza', name: 'Pinto Martins International Airport', country: 'Brazil' },
  { code: 'REC', city: 'Recife', name: 'Recife/Guararapes–Gilberto Freyre International Airport', country: 'Brazil' },
  { code: 'BEL', city: 'Belém', name: 'Val de Cans International Airport', country: 'Brazil' },
  { code: 'MAO', city: 'Manaus', name: 'Eduardo Gomes International Airport', country: 'Brazil' },
  { code: 'AEP', city: 'Buenos Aires', name: 'Jorge Newbery Airfield', country: 'Argentina' },
  { code: 'COR', city: 'Córdoba', name: 'Córdoba Airport', country: 'Argentina' },
  { code: 'MDZ', city: 'Mendoza', name: 'Governor Francisco Gabrielli International Airport', country: 'Argentina' },
  { code: 'BOG', city: 'Bogotá', name: 'El Dorado International Airport', country: 'Colombia' },
  { code: 'MDE', city: 'Medellín', name: 'José María Córdova International Airport', country: 'Colombia' },
  { code: 'CTG', city: 'Cartagena', name: 'Rafael Núñez International Airport', country: 'Colombia' },
  { code: 'UIO', city: 'Quito', name: 'Mariscal Sucre International Airport', country: 'Ecuador' },
  { code: 'GYE', city: 'Guayaquil', name: 'José Joaquín de Olmedo International Airport', country: 'Ecuador' },
  { code: 'CCS', city: 'Caracas', name: 'Simón Bolívar International Airport', country: 'Venezuela' },
  { code: 'VLN', city: 'Valencia', name: 'Arturo Michelena International Airport', country: 'Venezuela' },
  { code: 'LPB', city: 'La Paz', name: 'El Alto International Airport', country: 'Bolivia' },
  { code: 'CBB', city: 'Cochabamba', name: 'Jorge Wilstermann International Airport', country: 'Bolivia' },
  { code: 'ASU', city: 'Asunción', name: 'Silvio Pettirossi International Airport', country: 'Paraguay' },
  { code: 'MVD', city: 'Montevideo', name: 'Carrasco International Airport', country: 'Uruguay' },
  { code: 'GEO', city: 'Georgetown', name: 'Cheddi Jagan International Airport', country: 'Guyana' },
  { code: 'PBM', city: 'Paramaribo', name: 'Johan Adolf Pengel International Airport', country: 'Suriname' },

  // OCEANIA (expanded)
  { code: 'ADL', city: 'Adelaide', name: 'Adelaide Airport', country: 'Australia' },
  { code: 'CNS', city: 'Cairns', name: 'Cairns Airport', country: 'Australia' },
  { code: 'DRW', city: 'Darwin', name: 'Darwin Airport', country: 'Australia' },
  { code: 'HBA', city: 'Hobart', name: 'Hobart Airport', country: 'Australia' },
  { code: 'WLG', city: 'Wellington', name: 'Wellington Airport', country: 'New Zealand' },
  { code: 'ZQN', city: 'Queenstown', name: 'Queenstown Airport', country: 'New Zealand' },
  { code: 'ROT', city: 'Rotorua', name: 'Rotorua Airport', country: 'New Zealand' },
  { code: 'NPL', city: 'New Plymouth', name: 'New Plymouth Airport', country: 'New Zealand' },
  { code: 'NOU', city: 'Noumea', name: 'La Tontouta International Airport', country: 'New Caledonia' },
  { code: 'VLI', city: 'Port Vila', name: 'Bauerfield International Airport', country: 'Vanuatu' },
  { code: 'HIR', city: 'Honiara', name: 'Honiara International Airport', country: 'Solomon Islands' },
  { code: 'POM', city: 'Port Moresby', name: 'Jacksons International Airport', country: 'Papua New Guinea' },
  { code: 'APW', city: 'Apia', name: 'Faleolo International Airport', country: 'Samoa' },
  { code: 'TBU', city: 'Nuku\'alofa', name: 'Fuaʻamotu International Airport', country: 'Tonga' },
  { code: 'SUV', city: 'Suva', name: 'Nausori International Airport', country: 'Fiji' },
  { code: 'RAR', city: 'Rarotonga', name: 'Rarotonga International Airport', country: 'Cook Islands' },

  // CARIBBEAN & CENTRAL AMERICA
  { code: 'SJU', city: 'San Juan', name: 'Luis Muñoz Marín International Airport', country: 'Puerto Rico' },
  { code: 'BGI', city: 'Bridgetown', name: 'Grantley Adams International Airport', country: 'Barbados' },
  { code: 'POS', city: 'Port of Spain', name: 'Piarco International Airport', country: 'Trinidad and Tobago' },
  { code: 'KIN', city: 'Kingston', name: 'Norman Manley International Airport', country: 'Jamaica' },
  { code: 'MBJ', city: 'Montego Bay', name: 'Sangster International Airport', country: 'Jamaica' },
  { code: 'HAV', city: 'Havana', name: 'José Martí International Airport', country: 'Cuba' },
  { code: 'SDQ', city: 'Santo Domingo', name: 'Las Américas International Airport', country: 'Dominican Republic' },
  { code: 'PUJ', city: 'Punta Cana', name: 'Punta Cana International Airport', country: 'Dominican Republic' },
  { code: 'PAP', city: 'Port-au-Prince', name: 'Toussaint Louverture International Airport', country: 'Haiti' },
  { code: 'SJO', city: 'San José', name: 'Juan Santamaría International Airport', country: 'Costa Rica' },
  { code: 'PTY', city: 'Panama City', name: 'Tocumen International Airport', country: 'Panama' },
  { code: 'GUA', city: 'Guatemala City', name: 'La Aurora International Airport', country: 'Guatemala' },
  { code: 'SAL', city: 'San Salvador', name: 'Monseñor Óscar Arnulfo Romero International Airport', country: 'El Salvador' },
  { code: 'TGU', city: 'Tegucigalpa', name: 'Toncontín International Airport', country: 'Honduras' },
  { code: 'MGA', city: 'Managua', name: 'Augusto C. Sandino International Airport', country: 'Nicaragua' },
  { code: 'BZE', city: 'Belize City', name: 'Philip S. W. Goldson International Airport', country: 'Belize' },
   // CARIBBEAN & CENTRAL AMERICA (continued)
  { code: 'CUR', city: 'Willemstad', name: 'Curaçao International Airport', country: 'Curaçao' },
  { code: 'AUA', city: 'Oranjestad', name: 'Queen Beatrix International Airport', country: 'Aruba' },
  { code: 'BON', city: 'Kralendijk', name: 'Flamingo International Airport', country: 'Bonaire' },
  { code: 'SXM', city: 'Philipsburg', name: 'Princess Juliana International Airport', country: 'Sint Maarten' },
  { code: 'STT', city: 'Charlotte Amalie', name: 'Cyril E. King Airport', country: 'US Virgin Islands' },
  { code: 'STX', city: 'Christiansted', name: 'Henry E. Rohlsen Airport', country: 'US Virgin Islands' },
  { code: 'SKB', city: 'Basseterre', name: 'Robert L. Bradshaw International Airport', country: 'Saint Kitts and Nevis' },
  { code: 'ANU', city: 'St. John\'s', name: 'V.C. Bird International Airport', country: 'Antigua and Barbuda' },
  { code: 'DOM', city: 'Roseau', name: 'Douglas-Charles Airport', country: 'Dominica' },
  { code: 'UVF', city: 'Castries', name: 'Hewanorra International Airport', country: 'Saint Lucia' },
  { code: 'SVD', city: 'Kingstown', name: 'Argyle International Airport', country: 'Saint Vincent and the Grenadines' },
  { code: 'GND', city: 'St. George\'s', name: 'Maurice Bishop International Airport', country: 'Grenada' },

  // ADDITIONAL AFRICAN AIRPORTS
  { code: 'TNR', city: 'Antananarivo', name: 'Ivato Airport', country: 'Madagascar' },
  { code: 'NOS', city: 'Nosy Be', name: 'Fascene Airport', country: 'Madagascar' },
  { code: 'RUN', city: 'Saint-Denis', name: 'Roland Garros Airport', country: 'Réunion' },
  { code: 'MRU', city: 'Port Louis', name: 'Sir Seewoosagur Ramgoolam International Airport', country: 'Mauritius' },
  { code: 'SEZ', city: 'Victoria', name: 'Seychelles International Airport', country: 'Seychelles' },
  { code: 'RAI', city: 'Praia', name: 'Nelson Mandela International Airport', country: 'Cape Verde' },
  { code: 'SID', city: 'Sal', name: 'Amílcar Cabral International Airport', country: 'Cape Verde' },
  { code: 'ASI', city: 'Asmara', name: 'Asmara International Airport', country: 'Eritrea' },
  { code: 'JUB', city: 'Juba', name: 'Juba International Airport', country: 'South Sudan' },
  { code: 'WAU', city: 'Wau', name: 'Wau Airport', country: 'South Sudan' },

  // ADDITIONAL MIDDLE EASTERN AIRPORTS
  { code: 'YNB', city: 'Marib', name: 'Marib Airport', country: 'Yemen' },
  { code: 'HOD', city: 'Al Hudaydah', name: 'Al Hudaydah Airport', country: 'Yemen' },
  { code: 'THR', city: 'Tehran', name: 'Mehrabad International Airport', country: 'Iran' },
  { code: 'IFN', city: 'Isfahan', name: 'Isfahan International Airport', country: 'Iran' },
  { code: 'MHD', city: 'Mashhad', name: 'Mashhad International Airport', country: 'Iran' },
  { code: 'AWZ', city: 'Ahvaz', name: 'Ahvaz Airport', country: 'Iran' },
  { code: 'ADU', city: 'Ardabil', name: 'Ardabil Airport', country: 'Iran' },
  { code: 'BND', city: 'Bandar Abbas', name: 'Bandar Abbas International Airport', country: 'Iran' },
  { code: 'BSR', city: 'Basra', name: 'Basra International Airport', country: 'Iraq' },
  { code: 'NJF', city: 'Najaf', name: 'Al Najaf International Airport', country: 'Iraq' },
  { code: 'SDA', city: 'Baghdad', name: 'Baghdad International Airport', country: 'Iraq' },

  // ADDITIONAL EUROPEAN AIRPORTS
  { code: 'REK', city: 'Reykjavik', name: 'Reykjavík Airport', country: 'Iceland' },
  { code: 'AES', city: 'Ålesund', name: 'Ålesund Airport', country: 'Norway' },
  { code: 'TOS', city: 'Tromsø', name: 'Tromsø Airport', country: 'Norway' },
  { code: 'BOO', city: 'Bodø', name: 'Bodø Airport', country: 'Norway' },
  { code: 'LYR', city: 'Longyearbyen', name: 'Longyearbyen Airport', country: 'Norway' },
  { code: 'UME', city: 'Umeå', name: 'Umeå City Airport', country: 'Sweden' },
  { code: 'LLA', city: 'Luleå', name: 'Luleå Airport', country: 'Sweden' },
  { code: 'KSD', city: 'Karlstad', name: 'Karlstad Airport', country: 'Sweden' },
  { code: 'JKG', city: 'Jönköping', name: 'Jönköping Airport', country: 'Sweden' },
  { code: 'ROV', city: 'Rovaniemi', name: 'Rovaniemi Airport', country: 'Finland' },
  { code: 'IVL', city: 'Ivalo', name: 'Ivalo Airport', country: 'Finland' },
  { code: 'KTT', city: 'Kittilä', name: 'Kittilä Airport', country: 'Finland' },
  { code: 'KAJ', city: 'Kajaani', name: 'Kajaani Airport', country: 'Finland' },

  // ADDITIONAL ASIAN AIRPORTS
  { code: 'KTH', city: 'Kathmandu', name: 'Tribhuvan International Airport', country: 'Nepal' },
  { code: 'BWP', city: 'Bhairawa', name: 'Gautam Buddha Airport', country: 'Nepal' },
  { code: 'MTN', city: 'Mountain', name: 'Mountain Airport', country: 'Nepal' },
  { code: 'LUA', city: 'Lukla', name: 'Tenzing-Hillary Airport', country: 'Nepal' },
  { code: 'THU', city: 'Thimphu', name: 'Paro Airport', country: 'Bhutan' },
  { code: 'PBH', city: 'Paro', name: 'Paro Airport', country: 'Bhutan' },
  { code: 'JSR', city: 'Jessore', name: 'Jessore Airport', country: 'Bangladesh' },
  { code: 'SPD', city: 'Saidpur', name: 'Saidpur Airport', country: 'Bangladesh' },
  { code: 'RJH', city: 'Rajshahi', name: 'Shah Makhdum Airport', country: 'Bangladesh' },
  { code: 'BZL', city: 'Barisal', name: 'Barisal Airport', country: 'Bangladesh' },

  // ADDITIONAL PACIFIC ISLANDS
  { code: 'PPN', city: 'Papeete', name: 'Tahiti Faa\'a International Airport', country: 'French Polynesia' },
  { code: 'MOZ', city: 'Moorea', name: 'Moorea Airport', country: 'French Polynesia' },
  { code: 'BOB', city: 'Bora Bora', name: 'Bora Bora Airport', country: 'French Polynesia' },
  { code: 'HUH', city: 'Huahine', name: 'Huahine Airport', country: 'French Polynesia' },
  { code: 'RUR', city: 'Rurutu', name: 'Rurutu Airport', country: 'French Polynesia' },
  { code: 'TKP', city: 'Takapoto', name: 'Takapoto Airport', country: 'French Polynesia' },
  { code: 'NHV', city: 'Marquesas', name: 'Nuku Hiva Airport', country: 'French Polynesia' },
  { code: 'UAH', city: 'Ua Huka', name: 'Ua Huka Airport', country: 'French Polynesia' },
  { code: 'UAP', city: 'Ua Pou', name: 'Ua Pou Airport', country: 'French Polynesia' },
  { code: 'GUM', city: 'Hagåtña', name: 'Antonio B. Won Pat International Airport', country: 'Guam' },
  { code: 'SPN', city: 'Saipan', name: 'Saipan International Airport', country: 'Northern Mariana Islands' },
  { code: 'TKK', city: 'Chuuk', name: 'Chuuk International Airport', country: 'Micronesia' },
  { code: 'PNI', city: 'Pohnpei', name: 'Pohnpei International Airport', country: 'Micronesia' },
  { code: 'KSA', city: 'Kosrae', name: 'Kosrae International Airport', country: 'Micronesia' },
  { code: 'YAP', city: 'Yap', name: 'Yap International Airport', country: 'Micronesia' },
  { code: 'ROR', city: 'Koror', name: 'Palau International Airport', country: 'Palau' },
  { code: 'MAJ', city: 'Majuro', name: 'Marshall Islands International Airport', country: 'Marshall Islands' },
  { code: 'KWA', city: 'Kwajalein', name: 'Bucholz Army Airfield', country: 'Marshall Islands' },
  { code: 'TAR', city: 'Tarawa', name: 'Bonriki International Airport', country: 'Kiribati' },
  { code: 'CXI', city: 'Christmas Island', name: 'Cassidy International Airport', country: 'Kiribati' },
  { code: 'FUN', city: 'Funafuti', name: 'Funafuti International Airport', country: 'Tuvalu' },
  { code: 'IUE', city: 'Niue', name: 'Niue International Airport', country: 'Niue' },
  { code: 'PPG', city: 'Pago Pago', name: 'Pago Pago International Airport', country: 'American Samoa' },

  // ADDITIONAL REMOTE TERRITORIES
  { code: 'ASC', city: 'Georgetown', name: 'Wideawake Airfield', country: 'Ascension Island' },
  { code: 'SHN', city: 'Jamestown', name: 'Saint Helena Airport', country: 'Saint Helena' },
  { code: 'TRI', city: 'Edinburgh', name: 'Tristan da Cunha Airport', country: 'Tristan da Cunha' },
  { code: 'FLK', city: 'Stanley', name: 'Mount Pleasant Airport', country: 'Falkland Islands' },
  { code: 'SGI', city: 'King Edward Point', name: 'South Georgia Airport', country: 'South Georgia' },
  { code: 'IPC', city: 'Easter Island', name: 'Mataveri International Airport', country: 'Chile' },
  { code: 'JNX', city: 'Robinson Crusoe Island', name: 'Robinson Crusoe Airport', country: 'Chile' },
  { code: 'USH', city: 'Ushuaia', name: 'Malvinas Argentinas Ushuaia International Airport', country: 'Argentina' },
  { code: 'RGL', city: 'Río Gallegos', name: 'Piloto Civil Norberto Fernández International Airport', country: 'Argentina' },

  // ANTARCTICA RESEARCH STATIONS (Seasonal)
  { code: 'TNM', city: 'Teniente Marsh', name: 'Teniente Marsh Airport', country: 'Antarctica' },
  { code: 'ROT', city: 'Rothera', name: 'Rothera Research Station', country: 'Antarctica' },
  { code: 'MCM', city: 'McMurdo', name: 'McMurdo Station', country: 'Antarctica' },

  // ADDITIONAL INDIAN OCEAN ISLANDS
  { code: 'CXR', city: 'Christmas Island', name: 'Christmas Island Airport', country: 'Christmas Island' },
  { code: 'CCK', city: 'Cocos Islands', name: 'Cocos (Keeling) Islands Airport', country: 'Cocos Islands' },
  { code: 'NLK', city: 'Norfolk Island', name: 'Norfolk Island Airport', country: 'Norfolk Island' },
  { code: 'LRD', city: 'Lord Howe Island', name: 'Lord Howe Island Airport', country: 'Australia' },

  // ADDITIONAL ARCTIC AIRPORTS
  { code: 'SFJ', city: 'Kangerlussuaq', name: 'Kangerlussuaq Airport', country: 'Greenland' },
  { code: 'GOH', city: 'Nuuk', name: 'Nuuk Airport', country: 'Greenland' },
  { code: 'JAV', city: 'Ilulissat', name: 'Ilulissat Airport', country: 'Greenland' },
  { code: 'THU', city: 'Thule', name: 'Thule Air Base', country: 'Greenland' },
  { code: 'LYR', city: 'Longyearbyen', name: 'Longyearbyen Airport', country: 'Svalbard' },
  { code: 'BJN', city: 'Barentsburg', name: 'Barentsburg Airport', country: 'Svalbard' },
  { code: 'YFB', city: 'Iqaluit', name: 'Iqaluit Airport', country: 'Canada' },
  { code: 'YRT', city: 'Rankin Inlet', name: 'Rankin Inlet Airport', country: 'Canada' },
  { code: 'YCB', city: 'Cambridge Bay', name: 'Cambridge Bay Airport', country: 'Canada' },
  { code: 'YUX', city: 'Hall Beach', name: 'Hall Beach Airport', country: 'Canada' },

  // FINAL REMOTE AND SPECIAL USE AIRPORTS
  { code: 'WLS', city: 'Wallis', name: 'Wallis Airport', country: 'Wallis and Futuna' },
  { code: 'FUT', city: 'Futuna', name: 'Pointe Vele Airport', country: 'Wallis and Futuna' },
  { code: 'AUK', city: 'Alofi', name: 'Niue International Airport', country: 'Niue' },
  { code: 'CKI', city: 'Crooked Island', name: 'Crooked Island Airport', country: 'Bahamas' },
  { code: 'GHB', city: 'Governor\'s Harbour', name: 'Governor\'s Harbour Airport', country: 'Bahamas' },
  { code: 'TCB', city: 'Treasure Cay', name: 'Treasure Cay Airport', country: 'Bahamas' },
  { code: 'ELH', city: 'North Eleuthera', name: 'North Eleuthera Airport', country: 'Bahamas' },
  { code: 'RSD', city: 'Rock Sound', name: 'Rock Sound Airport', country: 'Bahamas' },
  { code: 'MHH', city: 'Marsh Harbour', name: 'Marsh Harbour Airport', country: 'Bahamas' },
  { code: 'FPO', city: 'Freeport', name: 'Grand Bahama International Airport', country: 'Bahamas' },
  { code: 'GGT', city: 'George Town', name: 'Exuma International Airport', country: 'Bahamas' }

]

// Utility functions
const getAirlineName = (code) => airlineNames[code] || code
const getAirportName = (code) => airportNames[code] || code
const getAirlineLogo = (code) => `https://content.airhex.com/content/logos/airlines_${code}_200_200_s.png`

const formatCabinClass = (cabin) => {
  const classMap = {
    'ECONOMY': 'Economy',
    'PREMIUM_ECONOMY': 'Premium Economy',
    'BUSINESS': 'Business Class',
    'FIRST': 'First Class'
  }
  return classMap[cabin] || cabin || 'Economy'
}

const getClassBadgeColor = (cabin) => {
  const colorMap = {
    'ECONOMY': 'bg-gray-100 text-gray-800',
    'PREMIUM_ECONOMY': 'bg-blue-100 text-blue-800',
    'BUSINESS': 'bg-purple-100 text-purple-800',
    'FIRST': 'bg-yellow-100 text-yellow-800'
  }
  return colorMap[cabin] || 'bg-gray-100 text-gray-800'
}

const calculateLayover = (arrivalTime, departureTime) => {
  const arrival = new Date(arrivalTime)
  const departure = new Date(departureTime)
  const diff = departure - arrival
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  return `${hours}h ${minutes}m`
}

const handleImageError = (event) => {
  event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iMjAiIGZpbGw9IiNGM0Y0RjYiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAyTDEzLjA5IDguMjZMMjAgOUwxNC0xTDEzLjA5IDE1Ljc0TDEyIDIyTDEwLjkxIDE1Ljc0TDQgMTVMMTAgOUwxMC45MSA4LjI2TDEyIDJaIiBmaWxsPSIjOTM5M0EzIi8+Cjwvc3ZnPgo8L3N2Zz4='
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString(undefined, { 
    dateStyle: 'medium', 
    timeStyle: 'short' 
  })
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(undefined, { 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString(undefined, { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const formatDuration = (iso) => {
  const match = iso?.match(/PT(?:(\d+)H)?(?:(\d+)M)?/)
  if (!match) return 'N/A'
  
  const hours = match[1] ? `${match[1]}h` : ''
  const minutes = match[2] ? `${match[2]}m` : ''
  return `${hours} ${minutes}`.trim() || 'N/A'
}
const fetchExchangeRates = async () => {
  try {
    currencyUpdateLoading.value = true
    
    // Try multiple APIs for better reliability
    let response
    try {
      response = await axios.get('https://api.fxratesapi.com/latest?base=EUR&symbols=USD,RWF')
    } catch {
      response = await axios.get('https://api.exchangerate-api.com/v4/latest/EUR')
    }
    
    console.log('Exchange rate response:', response.data) // Debug log
    
    if (response.data.rates) {
      exchangeRates.value = {
        EUR: 1,
        USD: response.data.rates.USD || 1.1,
        RWF: response.data.rates.RWF || 1200,
        ...response.data.rates
      }
    } else {
      throw new Error('No rates in response')
    }
    
    console.log('Final exchange rates:', exchangeRates.value) // Debug log
    
  } catch (error) {
    console.error('Failed to fetch exchange rates:', error)
    // More realistic fallback rates
    exchangeRates.value = {
      EUR: 1,
      USD: 1.08,
      RWF: 1250
    }
  } finally {
    currencyUpdateLoading.value = false
  }
}

const updateCurrencyRates = () => {
  // Always fetch fresh rates when currency ch
  fetchExchangeRates()
}

const getConvertedPrice = (priceString) => {
  if (!priceString || !exchangeRates.value) {
    console.log('Missing priceString or rates:', { priceString, rates: exchangeRates.value })
    return priceString
  }
  
  // Extract numeric value from price string (assuming EUR base)
  const match = priceString.match(/[\d,]+/)
  if (!match) {
    console.log('No price match found:', priceString)
    return priceString
  }
  
  const eurPrice = parseFloat(match[0].replace(/,/g, ''))
  const rate = exchangeRates.value[selectedCurrency.value]
  const convertedPrice = eurPrice * rate
  
  console.log('Price conversion:', { 
    original: priceString, 
    eurPrice, 
    rate, 
    convertedPrice, 
    currency: selectedCurrency.value 
  })
  
  return formatCurrency(convertedPrice, selectedCurrency.value)
}

const formatCurrency = (amount, currency) => {
  const symbols = {
    EUR: '€',
    USD: '$',
    RWF: 'Rwf'
  }
  
  if (currency === 'RWF') {
    return `${symbols[currency]}${Math.round(amount).toLocaleString()}`
  }
  
  return `${symbols[currency]}${amount.toFixed(2)}`
}

const calculateMiles = (priceString) => {
  if (!priceString || !exchangeRates.value) return 0
  
  // Extract numeric value and convert to RWF for miles calculation
  const match = priceString.match(/[\d,]+/)
  if (!match) return 0
  
  const eurPrice = parseFloat(match[0].replace(/,/g, ''))
  const rwfPrice = eurPrice * exchangeRates.value.RWF
  
  // 1000 RWF = 1 mile
  return Math.floor(rwfPrice / 1000)
}

const getMinutes = (iso) => {
  const match = iso?.match(/PT(?:(\d+)H)?(?:(\d+)M)?/)
  if (!match) return 0
  
  const h = parseInt(match[1] || '0')
  const m = parseInt(match[2] || '0')
  return h * 60 + m
}

// Computed properties for airport filtering
const filteredOriginAirports = computed(() => {
  if (!originSearch.value || originSearch.value.length < 1) return []
  
  const query = originSearch.value.toLowerCase()
  return airports.filter(airport => 
    airport.city.toLowerCase().includes(query) ||
    airport.code.toLowerCase().includes(query) ||
    airport.name.toLowerCase().includes(query) ||
    airport.country.toLowerCase().includes(query)
  ).slice(0, 8) // Limit to 8 results
})

const filteredDestinationAirports = computed(() => {
  if (!destinationSearch.value || destinationSearch.value.length < 1) return []
  
  const query = destinationSearch.value.toLowerCase()
  return airports.filter(airport => 
    airport.city.toLowerCase().includes(query) ||
    airport.code.toLowerCase().includes(query) ||
    airport.name.toLowerCase().includes(query) ||
    airport.country.toLowerCase().includes(query)
  ).slice(0, 8) // Limit to 8 results
})

// Computed properties
const sortedFlights = computed(() => {
  return [...flights.value].sort((a, b) => {
    if (sortOrder.value === 'duration') {
      const durationA = getMinutes(a.itineraries[0]?.duration || 'PT0M')
      const durationB = getMinutes(b.itineraries[0]?.duration || 'PT0M')
      return durationA - durationB
    }
    
    const pA = parseFloat(a.price?.replace(/[^\d.]/g, '') || '0')
    const pB = parseFloat(b.price?.replace(/[^\d.]/g, '') || '0')
    return sortOrder.value === 'asc' ? pA - pB : pB - pA
  })
})

const paginatedFlights = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = currentPage.value * itemsPerPage
  return sortedFlights.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(flights.value.length / itemsPerPage))

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// Validation
const validateForm = () => {
  errors.value = {}
  
  if (!origin.value.trim()) {
    errors.value.origin = 'Origin is required'
  }
  
  if (!destination.value.trim()) {
    errors.value.destination = 'Destination is required'
  }
  
  if (origin.value.trim() === destination.value.trim()) {
    errors.value.destination = 'Destination must be different from origin'
  }
  
  if (!departureDate.value) {
    errors.value.departureDate = 'Departure date is required'
  } else if (new Date(departureDate.value) < new Date(minDate)) {
    errors.value.departureDate = 'Departure date cannot be in the past'
  }
  
  if (tripType.value === 'round') {
    if (!returnDate.value) {
      errors.value.returnDate = 'Return date is required for round trips'
    } else if (new Date(returnDate.value) <= new Date(departureDate.value)) {
      errors.value.returnDate = 'Return date must be after departure date'
    }
  }
  
  return Object.keys(errors.value).length === 0
}

// Airport autocomplete functions
const handleOriginInput = () => {
  showOriginDropdown.value = true
  // Clear the selected airport code when user types
  if (originSearch.value !== getDisplayText(origin.value)) {
    origin.value = ''
  }
}

const handleDestinationInput = () => {
  showDestinationDropdown.value = true
  // Clear the selected airport code when user types
  if (destinationSearch.value !== getDisplayText(destination.value)) {
    destination.value = ''
  }
}

const handleOriginBlur = () => {
  setTimeout(() => {
    showOriginDropdown.value = false
  }, 150)
}

const handleDestinationBlur = () => {
  setTimeout(() => {
    showDestinationDropdown.value = false
  }, 150)
}

const selectOriginAirport = (airport) => {
  origin.value = airport.code
  originSearch.value = `${airport.city} (${airport.code})`
  showOriginDropdown.value = false
  if (errors.value.origin) delete errors.value.origin
}

const selectDestinationAirport = (airport) => {
  destination.value = airport.code
  destinationSearch.value = `${airport.city} (${airport.code})`
  showDestinationDropdown.value = false
  if (errors.value.destination) delete errors.value.destination
}

const getDisplayText = (code) => {
  const airport = airports.find(a => a.code === code)
  return airport ? `${airport.city} (${airport.code})` : code
}

// Main functions
const searchFlights = async () => {
  if (!validateForm()) {
    return
  }
  
  searched.value = true
  flights.value = []
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await axios.get('/api/flight-search', {
      params: {
        origin: origin.value.trim().toUpperCase(),
        destination: destination.value.trim().toUpperCase(),
        departureDate: departureDate.value,
        returnDate: returnDate.value,
        travelers: travelers.value,
        tripType: tripType.value,
        flightClass: flightClass.value
      },
      timeout: 30000 // 30 second timeout
    })
    
    flights.value = response.data || []
    currentPage.value = 1
  } catch (error) {
    console.error('Flight search error:', error)
    
    if (error.code === 'ECONNABORTED') {
      errorMessage.value = 'Search timeout. Please try again.'
    } else if (error.response?.status === 429) {
      errorMessage.value = 'Too many requests. Please wait a moment and try again.'
    } else if (error.response?.status >= 500) {
      errorMessage.value = 'Server error. Please try again later.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to search flights. Please try again.'
    }
    
    flights.value = []
  } finally {
    loading.value = false
  }
}

const bookFlight = async (flight, index) => {
  if (!user) {
    alert('Please login to continue booking your flight.')
    window.location.href = '/login'
    return
  }

  bookingLoading.value = { ...bookingLoading.value, [index]: true }

  try {
    await axios.post('/api/cart/flight', { 
      flight,
      timestamp: new Date().toISOString()
    })
    
    // Success feedback
    window.location.href = '/flights/checkout'
  } catch (error) {
    console.error('Booking error:', error)
    
    if (error.response?.status === 409) {
      alert('This flight is no longer available. Please search for other options.')
    } else if (error.response?.status === 422) {
      alert('Invalid flight data. Please try searching again.')
    } else {
      alert('Unable to book this flight. Please try again.')
    }
  } finally {
    bookingLoading.value = { ...bookingLoading.value, [index]: false }
  }
}

const resetSearch = () => {
  origin.value = ''
  destination.value = ''
  originSearch.value = ''
  destinationSearch.value = ''
  departureDate.value = ''
  returnDate.value = ''
  travelers.value = 1
  flightClass.value = ''
  sortOrder.value = 'asc'
  flights.value = []
  searched.value = false
  errorMessage.value = ''
  errors.value = {}
  currentPage.value = 1
  showFareDetails.value = {}
  showOriginDropdown.value = false
  showDestinationDropdown.value = false
  showBookingModal.value = false
selectedFlight.value = null
bookingStep.value = 'confirm'


}

const toggleFareDetails = (index) => {
  showFareDetails.value = {
    ...showFareDetails.value,
    [index]: !showFareDetails.value[index]
  }
}

// Watchers
watch(tripType, (newValue) => {
  if (newValue === 'oneway') {
    returnDate.value = ''
    delete errors.value.returnDate
  }
})

watch(departureDate, () => {
  if (errors.value.departureDate) {
    delete errors.value.departureDate
  }
})

watch([originSearch, destinationSearch], () => {
  if (errors.value.origin) delete errors.value.origin
  if (errors.value.destination) delete errors.value.destination
})

// Initialize display values if codes are pre-filled
watch([origin, destination], ([newOrigin, newDestination]) => {
  if (newOrigin && !originSearch.value) {
    originSearch.value = getDisplayText(newOrigin)
  }
  if (newDestination && !destinationSearch.value) {
    destinationSearch.value = getDisplayText(newDestination)
  }
}, { immediate: true })
onMounted(() => {
  fetchExchangeRates()
})
</script>