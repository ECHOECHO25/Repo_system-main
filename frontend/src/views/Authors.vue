<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Publications</p>
      <h1 class="text-3xl font-semibold">Authors</h1>
      <p class="mt-2 text-sm text-slate-400">
        Confirmed author links from imported and reviewed publications.
      </p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex-1">
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500" for="authors-search">
            Search Confirmed Authors
          </label>
          <input
            id="authors-search"
            v-model="searchQuery"
            type="text"
            placeholder="Search author or publication title..."
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
            @input="debounceSearch"
          />
        </div>
        <div class="w-full lg:w-56">
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500" for="authors-type">
            Type
          </label>
          <select
            id="authors-type"
            v-model="selectedType"
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 focus:border-emerald-400 focus:outline-none"
            @change="onTypeChange"
          >
            <option value="">All</option>
            <option value="faculty">Faculty</option>
            <option value="non-faculty">Non-Faculty</option>
          </select>
        </div>
        <div class="text-xs uppercase tracking-[0.3em] text-slate-500">
          <span>{{ pagination.total || rows.length }} confirmed</span>
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4">
      <div v-if="loading" class="flex items-center justify-center py-10 text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading authors
      </div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">
        {{ error }}
      </div>
      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-200">
            <thead class="bg-slate-900/90 text-xs uppercase tracking-[0.22em] text-slate-400">
              <tr>
                <th class="px-4 py-4">Author</th>
                <th class="px-4 py-4">Publications</th>
                <th class="px-4 py-4">Type</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in rows" :key="item.id" class="border-t border-slate-800/70">
                <td class="px-4 py-4 font-medium text-slate-100">{{ item.author_name }}</td>
                <td class="px-4 py-4">{{ item.author_publication_count ?? 0 }}</td>
                <td class="px-4 py-4">
                  <span
                    class="rounded-full px-3 py-1 text-[10px] uppercase tracking-[0.22em]"
                    :class="item.faculty_id ? 'bg-emerald-500/10 text-emerald-200' : 'bg-cyan-500/10 text-cyan-200'"
                  >
                    {{ item.faculty_id ? 'Faculty' : 'Non-Faculty' }}
                  </span>
                </td>
              </tr>
              <tr v-if="rows.length === 0">
                <td class="px-4 py-6 text-slate-400" colspan="3">
                  No confirmed authors found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination.total_pages > 1" class="mt-4 flex items-center justify-center gap-2">
          <button
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            Prev
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="rounded-full border px-4 py-2 text-xs uppercase tracking-[0.3em]"
            :class="page === pagination.current_page
              ? 'border-emerald-400 bg-emerald-400/10 text-emerald-100'
              : 'border-slate-800 text-slate-200 hover:border-emerald-400'"
            @click="changePage(page)"
          >
            {{ page }}
          </button>
          <button
            class="rounded-full border border-slate-800 px-4 py-2 text-xs uppercase tracking-[0.3em] text-slate-200 disabled:opacity-40"
            :disabled="pagination.current_page === pagination.total_pages"
            @click="changePage(pagination.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost/Repo_system-main/backend/public/api'

const loading = ref(false)
const error = ref('')
const rows = ref([])
const searchQuery = ref('')
const selectedType = ref('')
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  total_pages: 0
})

const fetchRows = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    if (selectedType.value) {
      params.type = selectedType.value
    }
    const response = await axios.get(`${apiBase}/publication-author-links/confirmed`, { params })
    rows.value = response.data?.data || []
    if (response.data?.pagination) {
      pagination.value = response.data.pagination
    }
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load authors.'
    rows.value = []
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.total_pages) return
  pagination.value.current_page = page
  fetchRows()
}

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const total = pagination.value.total_pages
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i += 1) pages.push(i)
  return pages
})

let searchTimeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchRows()
  }, 300)
}

const onTypeChange = () => {
  pagination.value.current_page = 1
  fetchRows()
}

onMounted(fetchRows)
</script>
