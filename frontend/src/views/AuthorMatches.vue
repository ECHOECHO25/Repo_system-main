<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Publications</p>
      <h1 class="text-3xl font-semibold">Author Match Review</h1>
      <p class="mt-2 text-sm text-slate-400">
        Review unmatched authors from recent publication uploads and link them to faculty profiles.
      </p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex-1">
          <label class="text-xs uppercase tracking-[0.3em] text-slate-500" for="match-search">
            Search Pending Matches
          </label>
          <input
            id="match-search"
            v-model="searchQuery"
            type="text"
            placeholder="Search author or publication title..."
            class="mt-2 w-full rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-400 focus:outline-none"
            @input="debounceSearch"
          />
        </div>
        <div class="text-xs uppercase tracking-[0.3em] text-slate-500">
          <span>{{ pagination.total || matches.length }} pending</span>
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4">
      <div v-if="loading" class="flex items-center justify-center py-10 text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading matches
      </div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">
        {{ error }}
      </div>
      <div v-else>
        <div class="overflow-x-auto overflow-y-visible">
          <table class="w-full text-left text-sm text-slate-200">
            <thead class="bg-slate-900/90 text-xs uppercase tracking-[0.22em] text-slate-400">
              <tr>
                <th class="px-4 py-4">Author</th>
                <th class="px-4 py-4">Publication</th>
                <th class="px-4 py-4">Year</th>
                <th class="px-4 py-4">Faculty Link</th>
                <th class="px-4 py-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="match in matches"
                :key="match.id"
                class="border-t border-slate-800/70"
              >
                <td class="px-4 py-4 font-medium text-slate-100">
                  {{ match.author_name }}
                </td>
                <td class="px-4 py-4">
                  <span class="text-slate-200">{{ match.publication_title || 'Untitled' }}</span>
                </td>
                <td class="px-4 py-4">{{ match.publication_year || '-' }}</td>
                <td class="px-4 py-4">
                  <select
                    v-model="selectedFaculty[match.id]"
                    class="w-full min-w-[220px] rounded-2xl border border-slate-700 bg-slate-950/80 px-3 py-2 text-xs text-slate-100 focus:border-emerald-400 focus:outline-none"
                  >
                    <option value="">Select faculty</option>
                    <option
                      v-for="option in facultyOptionsByAuthor(match.author_name)"
                      :key="`${match.id}-${option.id}`"
                      :value="String(option.id)"
                    >
                      {{ option.name }}
                    </option>
                  </select>
                  <p v-if="rowErrors[match.id]" class="mt-2 text-xs text-rose-300">
                    {{ rowErrors[match.id] }}
                  </p>
                </td>
                <td class="px-4 py-4 text-right">
                  <button
                    type="button"
                    class="rounded-full border border-emerald-400/40 px-3 py-1 text-xs uppercase tracking-[0.3em] text-emerald-200 hover:border-emerald-300"
                    :disabled="actionLoading[match.id]"
                    @click="confirmMatch(match.id)"
                  >
                    Confirm
                  </button>
                  <button
                    type="button"
                    class="ml-2 rounded-full border border-rose-500/40 px-3 py-1 text-xs uppercase tracking-[0.3em] text-rose-200 hover:border-rose-400"
                    :disabled="actionLoading[match.id]"
                    @click="rejectMatch(match.id)"
                  >
                    Reject
                  </button>
                </td>
              </tr>
              <tr v-if="matches.length === 0">
                <td class="px-4 py-6 text-slate-400" colspan="5">
                  No pending matches found.
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

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const loading = ref(false)
const error = ref('')
const matches = ref([])
const searchQuery = ref('')
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  total_pages: 0
})

const facultyOptions = ref([])
const selectedFaculty = ref({})
const rowErrors = ref({})
const actionLoading = ref({})

const normalizePerson = (value) =>
  String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

const facultyOptionsByAuthor = (authorName) => {
  if (!facultyOptions.value.length) return []

  const author = normalizePerson(authorName)
  if (!author) {
    return facultyOptions.value.slice(0, 150)
  }

  const matched = facultyOptions.value.filter((option) => {
    const name = normalizePerson(option.name)
    return name.includes(author) || author.includes(name)
  })

  if (matched.length) return matched.slice(0, 150)
  return facultyOptions.value.slice(0, 150)
}

const autoSelectExactMatches = () => {
  if (!matches.value.length || !facultyOptions.value.length) return

  const next = { ...selectedFaculty.value }
  for (const match of matches.value) {
    if (next[match.id]) continue
    const author = normalizePerson(match.author_name)
    if (!author) continue
    const exact = facultyOptions.value.find(
      (option) => normalizePerson(option.name) === author
    )
    if (exact) {
      next[match.id] = String(exact.id)
    }
  }
  selectedFaculty.value = next
}

const fetchMatches = async () => {
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
    const response = await axios.get(`${apiBase}/publication-author-links/pending`, { params })
    matches.value = response.data.data || []
    if (response.data.pagination) {
      pagination.value = response.data.pagination
    }
    autoSelectExactMatches()
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load pending matches.'
  } finally {
    loading.value = false
  }
}

const fetchFacultyOptions = async () => {
  try {
    const perPage = 500
    let page = 1
    let totalPages = 1
    const all = []

    do {
      const response = await axios.get(`${apiBase}/faculty`, {
        params: { page, per_page: perPage, sort: 'name', order: 'asc' }
      })
      const rows = response.data?.data || []
      all.push(...rows)
      totalPages = Number(response.data?.pagination?.total_pages || 1)
      page += 1
    } while (page <= totalPages)

    facultyOptions.value = all
      .filter((row) => row && row.id && row.name)
      .map((row) => ({
        id: Number(row.id),
        name: row.name
      }))
    autoSelectExactMatches()
  } catch (err) {
    facultyOptions.value = []
  }
}

const confirmMatch = async (id) => {
  rowErrors.value[id] = ''
  const facultyId = selectedFaculty.value[id]
  if (!facultyId) {
    rowErrors.value[id] = 'Select a faculty to confirm.'
    return
  }
  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'confirmed',
      faculty_id: facultyId
    })
    await fetchMatches()
  } catch (err) {
    rowErrors.value[id] = err?.response?.data?.message || 'Failed to confirm match.'
  } finally {
    actionLoading.value[id] = false
  }
}

const rejectMatch = async (id) => {
  rowErrors.value[id] = ''
  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publication-author-links/${id}`, {
      status: 'rejected'
    })
    await fetchMatches()
  } catch (err) {
    rowErrors.value[id] = err?.response?.data?.message || 'Failed to reject match.'
  } finally {
    actionLoading.value[id] = false
  }
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.total_pages) return
  pagination.value.current_page = page
  fetchMatches()
}

const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const total = pagination.value.total_pages
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  for (let i = start; i <= end; i += 1) {
    pages.push(i)
  }
  return pages
})

let searchTimeout
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchMatches()
  }, 300)
}

onMounted(() => {
  fetchMatches()
  fetchFacultyOptions()
})
</script>
