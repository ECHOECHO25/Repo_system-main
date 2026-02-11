<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">System</p>
        <h1 class="text-3xl font-semibold">Audit Logs</h1>
        <p class="mt-2 text-sm text-slate-400">Track logins and all create/update/delete actions.</p>
      </div>
      <div class="flex items-center gap-2">
        <input
          v-model="search"
          type="text"
          placeholder="Search actions, users, entities..."
          class="w-full rounded-full border border-slate-800 bg-slate-950/60 px-4 py-2 text-sm text-slate-100 md:w-72"
          @input="debounceSearch"
        />
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div v-if="loading" class="py-10 text-center text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading audit logs
      </div>
      <div v-else>
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/40">
          <table class="w-full table-fixed text-sm">
            <thead class="bg-slate-950/90 text-left text-xs uppercase tracking-[0.24em] text-slate-400">
              <tr>
                <th class="px-4 py-3 w-32">Date</th>
                <th class="px-4 py-3 w-24">User</th>
                <th class="px-4 py-3 w-24">Role</th>
                <th class="px-4 py-3 w-40">Action</th>
                <th class="px-4 py-3">Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-900/60">
                <td class="px-4 py-4 align-top text-slate-300">{{ formatDate(log.created_at) }}</td>
                <td class="px-4 py-4 align-top text-slate-200">{{ log.username || '—' }}</td>
                <td class="px-4 py-4 align-top text-slate-400">{{ log.role || '—' }}</td>
                <td class="px-4 py-4 align-top text-slate-200">{{ log.action }}</td>
                <td class="px-4 py-4 align-top text-slate-300">
                  {{ log.description || `${log.entity_type || 'system'} ${log.entity_id || ''}` }}
                </td>
              </tr>
              <tr v-if="logs.length === 0">
                <td colspan="5" class="px-4 py-6 text-center text-xs uppercase tracking-[0.22em] text-slate-500">
                  No audit logs found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <nav v-if="pagination.total_pages > 1" class="mt-6 flex items-center justify-center gap-2">
          <button
            class="rounded-lg border border-slate-700 px-3 py-1 text-xs text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="rounded-lg border px-3 py-1 text-xs"
            :class="page === pagination.current_page
              ? 'border-lime-400 bg-lime-500/10 text-lime-200'
              : 'border-slate-700 text-slate-200 hover:border-slate-500'"
            @click="changePage(page)"
          >
            {{ page }}
          </button>
          <button
            class="rounded-lg border border-slate-700 px-3 py-1 text-xs text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === pagination.total_pages"
            @click="changePage(pagination.current_page + 1)"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const logs = ref([])
const loading = ref(false)
const search = ref('')
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  total_pages: 0
})

let searchTimeout

const fetchLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    if (search.value.trim()) params.search = search.value.trim()
    const response = await axios.get(`${apiBase}/audit-logs`, { params })
    logs.value = response.data.data || []
    pagination.value = response.data.pagination
  } catch (err) {
    logs.value = []
  } finally {
    loading.value = false
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchLogs()
  }, 400)
}

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const total = pagination.value.total_pages
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  for (let i = start; i <= end; i += 1) pages.push(i)
  return pages
})

const changePage = (page) => {
  if (page < 1 || page > pagination.value.total_pages) return
  pagination.value.current_page = page
  fetchLogs()
}

const formatDate = (value) => {
  if (!value) return '—'
  return new Date(value).toLocaleString()
}

onMounted(fetchLogs)
</script>
