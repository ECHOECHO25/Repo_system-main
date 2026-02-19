<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Admin</p>
      <h1 class="text-3xl font-semibold">Add User</h1>
      <p class="mt-2 text-sm text-slate-400">
        Create a new account with a role and status.
      </p>
    </div>

    <div class="max-w-2xl rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Admin</p>
          <h2 class="text-lg font-semibold">Create User</h2>
        </div>
        <button
          type="button"
          class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
          :disabled="!isAdmin"
          @click="openModal"
        >
          Add User
        </button>
      </div>

      <p v-if="!isAdmin" class="mt-4 text-sm text-rose-300">
        Only admins can add users.
      </p>
      <p v-if="success" class="mt-4 text-sm text-emerald-300">{{ success }}</p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-slate-500">User List</p>
          <h2 class="text-lg font-semibold">Accounts</h2>
        </div>
        <button
          v-if="isAdmin"
          type="button"
          class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
          @click="fetchUsers"
        >
          Refresh
        </button>
      </div>

      <p v-if="!isAdmin" class="mt-4 text-sm text-rose-300">Only admins can view users.</p>
      <p v-else-if="usersLoading" class="mt-4 text-sm text-slate-400">Loading users...</p>
      <p v-else-if="usersError" class="mt-4 text-sm text-rose-300">{{ usersError }}</p>
      <p v-else-if="users.length === 0" class="mt-4 text-sm text-slate-400">No users found.</p>

      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="text-xs uppercase tracking-[0.2em] text-slate-400">
            <tr class="border-b border-slate-800">
              <th class="px-3 py-2">Username</th>
              <th class="px-3 py-2">Role</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2">Created</th>
              <th class="px-3 py-2">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800 text-slate-200">
            <tr v-for="user in users" :key="user.id">
              <td class="px-3 py-2">{{ user.username }}</td>
              <td class="px-3 py-2 capitalize">{{ user.role }}</td>
              <td class="px-3 py-2">
                <select
                  v-model="user.status"
                  class="rounded-lg border border-slate-800 bg-slate-950/60 px-2 py-1 text-xs text-slate-100 focus:border-emerald-400 focus:outline-none"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </td>
              <td class="px-3 py-2">{{ formatDate(user.created_at) }}</td>
              <td class="px-3 py-2">
                <button
                  type="button"
                  class="rounded-full border border-emerald-500/60 bg-emerald-500/10 px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-emerald-200 hover:border-emerald-400"
                  :disabled="userSavingId === user.id"
                  @click="updateStatus(user)"
                >
                  {{ userSavingId === user.id ? 'Saving...' : 'Save' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <transition name="fade">
    <div
      v-if="showModal"
      class="fixed inset-0 z-40 bg-black/60"
      @click="closeModal"
    ></div>
  </transition>

  <transition name="modal">
    <div v-if="showModal" class="fixed inset-0 z-50 grid place-items-center p-4">
      <div class="w-full max-w-2xl rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-2xl" @click.stop>
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-200">Add User</h3>
          <button
            type="button"
            class="rounded-full border border-slate-800 px-3 py-1 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
            @click="closeModal"
          >
            Close
          </button>
        </div>

        <form class="grid gap-4" @submit.prevent="handleSubmit">
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Username</label>
            <input
              v-model="form.username"
              type="text"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              required
            />
          </div>
          <div>
            <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              required
            />
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Role</label>
              <select
                v-model="form.role"
                class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              >
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
              </select>
            </div>
            <div>
              <label class="text-xs uppercase tracking-[0.3em] text-slate-500">Status</label>
              <select
                v-model="form.status"
                class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <p v-if="error" class="text-sm text-rose-300">{{ error }}</p>

          <div class="flex items-center justify-end gap-3">
            <button
              type="button"
              class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 hover:border-emerald-400"
              @click="resetForm"
            >
              Reset
            </button>
            <button
              type="submit"
              class="rounded-full bg-emerald-400/20 px-4 py-2 text-xs uppercase tracking-[0.3em] text-emerald-100 hover:bg-emerald-400/30"
              :disabled="loading || !isAdmin"
            >
              {{ loading ? 'Saving...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import { useAuth } from '../composables/useAuth'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
const { role } = useAuth()
const isAdmin = computed(() => role.value === 'admin')

const form = ref({
  username: '',
  password: '',
  role: 'editor',
  status: 'active'
})
const loading = ref(false)
const error = ref('')
const success = ref('')
const showModal = ref(false)
const users = ref([])
const usersLoading = ref(false)
const usersError = ref('')
const userSavingId = ref(null)

const resetForm = () => {
  form.value = {
    username: '',
    password: '',
    role: 'editor',
    status: 'active'
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    if (!isAdmin.value) {
      error.value = 'Only admins can add users.'
      return
    }
    await axios.post(`${apiBase}/users`, form.value)
    success.value = 'User created.'
    showModal.value = false
    await fetchUsers()
    resetForm()
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to create user'
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  if (!isAdmin.value) return
  usersLoading.value = true
  usersError.value = ''
  try {
    const response = await axios.get(`${apiBase}/users`)
    users.value = response.data?.data || []
  } catch (err) {
    usersError.value = err?.response?.data?.message || 'Failed to load users'
  } finally {
    usersLoading.value = false
  }
}

const updateStatus = async (user) => {
  if (!isAdmin.value) return
  userSavingId.value = user.id
  try {
    await axios.put(`${apiBase}/users/${user.id}`, { status: user.status })
  } catch (err) {
    usersError.value = err?.response?.data?.message || 'Failed to update user'
  } finally {
    userSavingId.value = null
  }
}

const formatDate = (value) => {
  if (!value) return '-'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString()
}

const openModal = () => {
  if (!isAdmin.value) return
  error.value = ''
  success.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

onMounted(fetchUsers)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
