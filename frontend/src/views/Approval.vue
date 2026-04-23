<template>
  <div class="space-y-6">
    <div>
      <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Publications</p>
      <h1 class="text-3xl font-semibold">Approval Queue</h1>
      <p class="mt-2 text-sm text-slate-400">
        Review researcher-submitted publications and approve or reject them.
      </p>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Year</label>
          <input
            v-model="filters.year"
            type="number"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @input="debounceSearch"
          />
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">College</label>
          <input
            v-model="filters.college"
            type="text"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @input="debounceSearch"
          />
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Type</label>
          <input
            v-model="filters.type"
            type="text"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @input="debounceSearch"
          />
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            placeholder="Title, authors, keywords..."
            @input="debounceSearch"
          />
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-4">
      <div v-if="loading" class="flex items-center justify-center py-10 text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading queue
      </div>
      <div v-else-if="error" class="px-4 py-6 text-sm text-rose-300">
        {{ error }}
      </div>
      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-200">
            <thead class="bg-slate-900/90 text-xs uppercase tracking-[0.22em] text-slate-400">
              <tr>
                <th class="px-4 py-4">Year</th>
                <th class="px-4 py-4">Title</th>
                <th class="px-4 py-4">Authors</th>
                <th class="px-4 py-4">Type</th>
                <th class="px-4 py-4">College</th>
                <th class="px-4 py-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pub in rows" :key="pub.id" class="border-t border-slate-800/70">
                <td class="px-4 py-4">{{ pub.year || '-' }}</td>
                <td class="px-4 py-4">{{ pub.title || '-' }}</td>
                <td class="px-4 py-4">{{ formatAuthors(pub.authors) }}</td>
                <td class="px-4 py-4">{{ pub.publication_type || '-' }}</td>
                <td class="px-4 py-4">{{ pub.college_institute || '-' }}</td>
                <td class="px-4 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      class="rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500"
                      @click="openView(pub)"
                    >
                      View
                    </button>
                    <button
                      class="rounded-full border border-emerald-400/40 px-3 py-1 text-xs uppercase tracking-[0.22em] text-emerald-200 hover:border-emerald-300"
                      :disabled="actionLoading[pub.id]"
                      @click="reviewPublication(pub.id, 'approved')"
                    >
                      Approve
                    </button>
                    <button
                      class="rounded-full border border-rose-500/40 px-3 py-1 text-xs uppercase tracking-[0.22em] text-rose-200 hover:border-rose-400"
                      :disabled="actionLoading[pub.id]"
                      @click="rejectPublication(pub.id)"
                    >
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="rows.length === 0">
                <td colspan="6" class="px-4 py-6 text-slate-400">No publications pending approval.</td>
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

    <div
      v-if="showViewModal && selectedPublication"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-3xl rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-semibold">Publication Details</h2>
          <button
            class="text-xs uppercase tracking-[0.22em] text-slate-400 hover:text-slate-200"
            @click="closeView"
          >
            Close
          </button>
        </div>

        <div class="mt-4 grid gap-3 text-sm text-slate-200">
          <div><span class="text-slate-400">Year:</span> {{ selectedPublication.year || '-' }}</div>
          <div><span class="text-slate-400">Title:</span> {{ selectedPublication.title || '-' }}</div>
          <div><span class="text-slate-400">Authors:</span> {{ formatAuthors(selectedPublication.authors) }}</div>
          <div><span class="text-slate-400">Type:</span> {{ selectedPublication.publication_type || '-' }}</div>
          <div><span class="text-slate-400">College/Institute:</span> {{ selectedPublication.college_institute || '-' }}</div>
          <div><span class="text-slate-400">Journal/Book:</span> {{ selectedPublication.journal_book || '-' }}</div>
          <div><span class="text-slate-400">Keywords:</span> {{ selectedPublication.keywords || '-' }}</div>
          <div><span class="text-slate-400">URL:</span> {{ selectedPublication.url || '-' }}</div>
          <div><span class="text-slate-400">Remarks:</span> {{ selectedPublication.remarks || '-' }}</div>
        </div>
      </div>
    </div>

    <div
      v-if="showRejectModal && rejectTargetId"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
    >
      <div class="w-full max-w-xl rounded-3xl border border-slate-800 bg-slate-950 p-6">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-semibold">Reject Publication</h2>
          <button
            class="text-xs uppercase tracking-[0.22em] text-slate-400 hover:text-slate-200"
            @click="closeRejectModal"
          >
            Close
          </button>
        </div>

        <div class="mt-4">
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Reason (optional)</label>
          <textarea
            v-model="rejectRemarks"
            rows="4"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            placeholder="Enter rejection reason..."
          />
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button
            class="rounded-full border border-slate-700 px-4 py-2 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500"
            @click="closeRejectModal"
          >
            Cancel
          </button>
          <button
            class="rounded-full border border-rose-500/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-rose-200 hover:border-rose-400"
            :disabled="!!actionLoading[rejectTargetId]"
            @click="confirmReject"
          >
            Confirm Reject
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
const actionLoading = ref({})
const showViewModal = ref(false)
const selectedPublication = ref(null)
const showRejectModal = ref(false)
const rejectTargetId = ref(null)
const rejectRemarks = ref('')
const filters = ref({
  year: '',
  college: '',
  type: '',
  search: ''
})
const pagination = ref({
  current_page: 1,
  per_page: 20,
  total: 0,
  total_pages: 1
})

const parseAuthorsJson = (value) => {
  if (typeof value !== 'string') return null
  const trimmed = value.trim()
  if (!trimmed.startsWith('[')) return null
  try {
    const parsed = JSON.parse(trimmed)
    if (Array.isArray(parsed)) {
      return parsed.map((author) => String(author).trim()).filter(Boolean)
    }
  } catch {}
  return null
}

const formatAuthors = (value) => {
  if (Array.isArray(value)) return value.join(', ')
  const parsed = parseAuthorsJson(value)
  if (parsed) return parsed.join(', ')
  return value || ''
}

const fetchQueue = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      year: filters.value.year || undefined,
      college: filters.value.college || undefined,
      type: filters.value.type || undefined,
      search: filters.value.search || undefined
    }
    const response = await axios.get(`${apiBase}/publications/review-queue`, { params })
    rows.value = response.data?.data || []
    pagination.value = response.data?.pagination || pagination.value
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to load approval queue.'
  } finally {
    loading.value = false
  }
}

const reviewPublication = async (id, status, remarks = '') => {
  actionLoading.value[id] = true
  try {
    await axios.put(`${apiBase}/publications/${id}/review`, { status, remarks })
    await fetchQueue()
  } catch (err) {
    error.value = err?.response?.data?.message || 'Failed to update review status.'
  } finally {
    actionLoading.value[id] = false
  }
}

const rejectPublication = async (id) => {
  rejectTargetId.value = id
  rejectRemarks.value = ''
  showRejectModal.value = true
}

const openView = (pub) => {
  selectedPublication.value = pub || null
  showViewModal.value = true
}

const closeView = () => {
  showViewModal.value = false
  selectedPublication.value = null
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectTargetId.value = null
  rejectRemarks.value = ''
}

const confirmReject = async () => {
  if (!rejectTargetId.value) return
  await reviewPublication(rejectTargetId.value, 'rejected', rejectRemarks.value || '')
  closeRejectModal()
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.total_pages) return
  pagination.value.current_page = page
  fetchQueue()
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
    fetchQueue()
  }, 400)
}

onMounted(fetchQueue)
</script>
