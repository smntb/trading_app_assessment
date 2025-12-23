<template>
  <div class="min-h-screen bg-gray-50 font-sans text-gray-800">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 flex items-center justify-between shadow-md">
      <h1 class="text-2xl font-bold tracking-wide">Trading Dashboard</h1>
      <div>
        <template v-if="!token">
          <button @click="showLogin = true" class="border border-white px-4 py-2 rounded-lg mr-2 hover:bg-white hover:text-blue-600 transition font-semibold">Login</button>
          <button @click="showRegister = true" class="border border-white px-4 py-2 rounded-lg hover:bg-white hover:text-blue-600 transition font-semibold">Register</button>
        </template>
        <template v-else>
          <button @click="logout" class="border border-white px-4 py-2 rounded-lg hover:bg-white hover:text-blue-600 transition font-semibold">Logout</button>
        </template>
      </div>
    </nav>

    <!-- Login Modal -->
    <div v-if="showLogin" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 w-96 shadow-lg">
        <h2 class="text-xl font-semibold mb-5 text-center">Login</h2>
        <input v-model="loginForm.email" type="email" placeholder="Email" class="w-full border p-3 rounded-lg mb-4 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
        <input v-model="loginForm.password" type="password" placeholder="Password" class="w-full border p-3 rounded-lg mb-6 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
        <button @click="login" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">Login</button>
      </div>
    </div>

    <!-- Register Modal -->
    <div v-if="showRegister" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-8 w-96 shadow-lg">
        <h2 class="text-xl font-semibold mb-5 text-center">Register</h2>
        <input v-model="registerForm.name" placeholder="Name" class="w-full border p-3 rounded-lg mb-3 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
        <input v-model="registerForm.email" placeholder="Email" class="w-full border p-3 rounded-lg mb-3 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
        <input v-model="registerForm.password" type="password" placeholder="Password" class="w-full border p-3 rounded-lg mb-6 focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
        <button @click="register" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">Register</button>
      </div>
    </div>

    <div v-if="token" class="container mx-auto px-6 py-8">
      <!-- Main Layout -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Balances -->
        <div class="lg:w-1/4 bg-white rounded-xl shadow p-6">
          <h2 class="font-bold text-lg mb-4 border-b pb-2">Balances</h2>
          <div class="mb-2"><span class="font-semibold">USD:</span> ${{ profile.balance.toFixed(2) }}</div>
          <div v-for="asset in profile.assets" :key="asset.symbol" class="mb-1">
            <span class="font-semibold">{{ asset.symbol }}:</span> {{ asset.amount }}
          </div>
        </div>

        <!-- Orders and Orderbook -->
        <div class="lg:w-3/4 flex flex-col gap-6">
          <!-- Place Order -->
          <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-bold text-lg mb-4 border-b pb-2">Place Order</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
              <select v-model="form.symbol" class="border p-3 rounded-lg focus:ring-2 focus:ring-green-400 focus:outline-none">
                <option value="BTC">BTC</option>
                <option value="ETH">ETH</option>
              </select>
              <select v-model="form.side" class="border p-3 rounded-lg focus:ring-2 focus:ring-green-400 focus:outline-none">
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
              </select>
              <input v-model="form.price" type="number" placeholder="Price" class="border p-3 rounded-lg focus:ring-2 focus:ring-green-400 focus:outline-none" />
              <input v-model="form.amount" type="number" placeholder="Amount" class="border p-3 rounded-lg focus:ring-2 focus:ring-green-400 focus:outline-none" />
            </div>
            <button @click="placeOrder" class="bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition font-semibold">Place Order</button>
          </div>

          <!-- My Orders -->
          <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
            <h2 class="font-bold text-lg mb-4 border-b pb-2">My Orders</h2>
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase text-sm">
                  <th class="px-4 py-2">Side</th>
                  <th class="px-4 py-2">Symbol</th>
                  <th class="px-4 py-2">Amount</th>
                  <th class="px-4 py-2">Price</th>
                  <th class="px-4 py-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id" class="border-b hover:bg-gray-50 transition">
                  <td class="px-4 py-2 capitalize">{{ order.side }}</td>
                  <td class="px-4 py-2">{{ order.symbol }}</td>
                  <td class="px-4 py-2">{{ order.amount }}</td>
                  <td class="px-4 py-2">{{ order.price }}</td>
                  <td class="px-4 py-2">
                    <span :class="statusClass(order.status)">{{ statusText(order.status) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Orderbook -->
          <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
            <h2 class="font-bold text-lg mb-4 border-b pb-2">Orderbook ({{ selectedSymbol }})</h2>
            <select v-model="selectedSymbol" class="border p-3 rounded-lg mb-4 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
              <option value="BTC">BTC</option>
              <option value="ETH">ETH</option>
            </select>
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase text-sm">
                  <th class="px-4 py-2">Side</th>
                  <th class="px-4 py-2">Amount</th>
                  <th class="px-4 py-2">Price</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in orderbook" :key="item.id" class="border-b hover:bg-gray-50 transition">
                  <td class="px-4 py-2">
                    <span :class="item.side === 'buy' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                      {{ item.side.toUpperCase() }}
                    </span>
                  </td>
                  <td class="px-4 py-2">{{ item.amount }}</td>
                  <td class="px-4 py-2">{{ item.price }}</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

    <div v-else class="container mx-auto px-6 py-12">
      <div class="text-center bg-white rounded-xl shadow p-8 text-gray-700 text-lg">
        Please login or register to access the dashboard.
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const token = ref(localStorage.getItem('token') || null)
const profile = ref({ balance: 0, assets: [], name: '', id: null })
const orders = ref([])
const orderbook = ref([])
const selectedSymbol = ref('BTC')
const form = ref({ symbol: 'BTC', side: 'buy', price: '', amount: '' })

const showLogin = ref(false)
const showRegister = ref(false)
const loginForm = ref({ email: '', password: '' })
const registerForm = ref({ name:'', email:'', password:'' })

const setAuthHeader = () => {
  if(token.value) axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
}
setAuthHeader()

// API Calls
const fetchProfile = async () => {
  try { const res = await axios.get('/api/profile'); profile.value = res.data } catch {}
}
const fetchOrders = async () => {
  try { const res = await axios.get('/api/orders'); orders.value = res.data.orders } catch {}
}
const fetchOrderbook = async () => {
  try { const res = await axios.get(`/api/orders?symbol=${selectedSymbol.value}`); orderbook.value = res.data.orders } catch {}
}
const placeOrder = async () => {
  try {
    await axios.post('/api/orders', form.value)
    await fetchProfile()
    await fetchOrders()
    await fetchOrderbook()
    form.value.price = ''
    form.value.amount = ''
  } catch(err) {
    alert(err.response?.data?.message || 'Order failed')
  }
}

const statusText = (status) => {
  if(status === 1) return 'Open'
  if(status === 2) return 'Filled'
  if(status === 3) return 'Cancelled'
  return status
}

const statusClass = (status) => {
  if(status === 1) return 'bg-yellow-200 text-yellow-800 px-2 py-1 rounded'
  if(status === 2) return 'bg-green-200 text-green-800 px-2 py-1 rounded'
  if(status === 3) return 'bg-red-200 text-red-800 px-2 py-1 rounded'
  return ''
}

const logout = () => {
  token.value = null
  localStorage.removeItem('token')
  profile.value = { balance:0, assets:[], name:'', id:null }
  orders.value = []
  orderbook.value = []
}

const login = async () => {
  try {
    const res = await axios.post('/api/login', loginForm.value)
    token.value = res.data.token
    localStorage.setItem('token', token.value)
    setAuthHeader()
    showLogin.value = false
    await fetchProfile()
    await fetchOrders()
    await fetchOrderbook()
  } catch(err) {
    alert(err.response?.data?.message || 'Login failed')
  }
}

const register = async () => {
  try {
    await axios.post('/api/register', registerForm.value)
    showRegister.value = false
    alert('Registration successful! Please login.')
  } catch(err) {
    alert(err.response?.data?.message || 'Registration failed')
  }
}

onMounted(async () => {
  if(token.value){
    await fetchProfile()
    await fetchOrders()
    await fetchOrderbook()
  }
})

watch(selectedSymbol, async () => { await fetchOrderbook() })
</script>
