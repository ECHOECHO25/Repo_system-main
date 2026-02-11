<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Publications</p>
        <h1 class="text-3xl font-semibold">Manage Publications</h1>
        <p class="mt-2 text-sm text-slate-400">
          Filter, review, and update publication records with consistent metadata.
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <input
          ref="fileInput"
          type="file"
          accept=".xlsx,.xls,.csv"
          class="hidden"
          @change="handleFileChange"
        >
        <button
          class="rounded-full border border-slate-700 px-4 py-2 text-xs uppercase tracking-[0.28em] text-slate-200 hover:border-slate-500"
          :disabled="importing"
          @click="triggerImport"
        >
          {{ importing ? 'Importing...' : 'Import Excel' }}
        </button>
        <button
          class="rounded-full border border-slate-700 px-4 py-2 text-xs uppercase tracking-[0.28em] text-slate-200 hover:border-slate-500"
          :disabled="exporting"
          @click="openExportModal"
        >
          {{ exporting ? 'Exporting...' : 'Export Excel' }}
        </button>
        <button
          class="rounded-full border border-red-400/40 px-4 py-2 text-xs uppercase tracking-[0.28em] text-red-200 hover:border-red-400 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="selectedIds.length === 0"
          @click="openBulkDeleteModal"
        >
          Delete
        </button>
        <button
          class="rounded-full bg-lime-300 px-4 py-2 text-xs uppercase tracking-[0.28em] text-slate-900 hover:bg-lime-200"
          @click="showAddModal"
        >
          Add publication
        </button>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Year</label>
          <select
            v-model="filters.year"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @change="fetchPublications"
          >
            <option value="">All Years</option>
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">College/Institute</label>
          <select
            v-model="filters.college"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @change="fetchPublications"
          >
            <option value="">All Colleges</option>
            <option v-for="c in colleges" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Type</label>
          <select
            v-model="filters.type"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            @change="fetchPublications"
          >
            <option value="">All Types</option>
            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            placeholder="Title, authors, keywords..."
            @input="debounceSearch"
          >
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
      <div v-if="loading" class="flex items-center justify-center py-10 text-xs uppercase tracking-[0.3em] text-slate-400">
        Loading publications
      </div>

      <div v-else>
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/40">
          <div class="hidden lg:block">
            <table class="w-full table-fixed text-sm">
              <thead class="sticky top-0 z-10 bg-slate-950/90 text-left text-xs uppercase tracking-[0.24em] text-slate-400">
                <tr>
                  <th class="px-4 py-3 w-10">
                    <input
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-emerald-400"
                      :checked="allSelected"
                      @change="toggleSelectAll"
                    />
                  </th>
                  <th class="px-4 py-3 w-16">Year</th>
                  <th class="px-4 py-3">Title</th>
                  <th class="px-4 py-3">Authors</th>
                  <th class="px-4 py-3 w-24">Type</th>
                  <th class="px-4 py-3">College</th>
                  <th class="px-4 py-3 w-28 text-right">Citations</th>
                  <th class="px-4 py-3 w-44 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr
                  v-for="pub in publications"
                  :key="pub.id"
                  class="transition hover:bg-slate-900/60"
                >
                  <td class="px-4 py-5 align-top">
                    <input
                      type="checkbox"
                      class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-emerald-400"
                      :checked="selectedIds.includes(pub.id)"
                      @change="toggleSelectRow(pub.id)"
                    />
                  </td>
                  <td class="px-4 py-5 align-top text-slate-300">{{ pub.year }}</td>
                  <td class="px-4 py-5 align-top">
                    <div class="min-w-0">
                      <div class="group relative">
                        <div class="break-words font-semibold text-slate-100">
                          {{ pub.title }}
                        </div>
                        <div
                          class="pointer-events-none absolute left-0 top-full z-20 mt-2 w-max max-w-[520px] rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-100 opacity-0 shadow-xl transition group-hover:opacity-100"
                        >
                          {{ pub.title }}
                        </div>
                      </div>
                      <div class="mt-1 text-xs text-slate-500" v-if="pub.journal_book">
                        {{ pub.journal_book }}
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-5 align-top">
                    <div class="group relative min-w-0">
                      <div class="break-words text-slate-300">
                        {{ formatAuthors(pub.authors) }}
                      </div>
                      <div
                        class="pointer-events-none absolute left-0 top-full z-20 mt-2 w-max max-w-[520px] rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-100 opacity-0 shadow-xl transition group-hover:opacity-100"
                      >
                        {{ formatAuthors(pub.authors) }}
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-5 align-top">
                    <span class="rounded-full bg-lime-500/10 px-3 py-1 text-xs text-lime-200">
                      {{ pub.publication_type }}
                    </span>
                  </td>
                  <td class="px-4 py-5 align-top text-slate-300">
                    <span class="rounded-full border border-slate-800 px-3 py-1 text-xs uppercase tracking-[0.22em]">
                      <span class="break-words">{{ pub.college_institute || 'Unknown' }}</span>
                    </span>
                  </td>
                  <td class="px-4 py-5 align-top text-right">
                    <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs text-emerald-200" v-if="pub.citations">
                      {{ pub.citations }}
                    </span>
                    <span class="text-slate-500" v-else>-</span>
                  </td>
                  <td class="px-4 py-5 align-top text-right">
                    <div class="flex flex-wrap justify-end gap-2">
                      <button
                        class="rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500"
                        @click="viewPublication(pub)"
                      >
                        View
                      </button>
                      <button
                        class="rounded-full border border-yellow-400/40 px-3 py-1 text-xs uppercase tracking-[0.22em] text-yellow-200 hover:border-yellow-400"
                        @click="confirmEdit(pub)"
                      >
                        Edit
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="space-y-4 p-4 lg:hidden">
            <article
              v-for="pub in publications"
              :key="`card-${pub.id}`"
              class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="text-xs uppercase tracking-[0.22em] text-slate-500">
                  {{ pub.year }}
                </div>
                <span class="rounded-full bg-lime-500/10 px-3 py-1 text-xs text-lime-200">
                  {{ pub.publication_type }}
                </span>
              </div>
              <div class="mt-2 text-base font-semibold text-slate-100">
                {{ pub.title }}
              </div>
              <div class="mt-2 text-xs text-slate-500" v-if="pub.journal_book">
                {{ pub.journal_book }}
              </div>
              <div class="mt-3 text-sm text-slate-300">
                {{ formatAuthors(pub.authors) }}
              </div>
              <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <span class="rounded-full border border-slate-800 px-3 py-1 uppercase tracking-[0.22em]">
                  {{ pub.college_institute || 'Unknown' }}
                </span>
                <span class="rounded-full border border-slate-800 px-3 py-1 uppercase tracking-[0.22em]">
                  Citations: {{ pub.citations || 0 }}
                </span>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-2">
                <button
                  class="rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500"
                  @click="viewPublication(pub)"
                >
                  View
                </button>
                <button
                  class="rounded-full border border-yellow-400/40 px-3 py-1 text-xs uppercase tracking-[0.22em] text-yellow-200 hover:border-yellow-400"
                  @click="confirmEdit(pub)"
                >
                  Edit
                </button>
              </div>
            </article>
          </div>
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

        <div class="mt-4 text-center text-xs uppercase tracking-[0.22em] text-slate-500">
          Showing {{ publications.length }} of {{ pagination.total }} publications
        </div>
      </div>
    </div>

    <PublicationModal
      :show="showModal"
      :publication="selectedPublication"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />

    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/95 p-6 text-slate-100 shadow-2xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">{{ confirmTitle }}</h3>
          <button class="text-slate-400 hover:text-white" @click="closeConfirmModal">x</button>
        </div>
        <p class="mt-4 text-sm text-slate-300">{{ confirmMessage }}</p>
        <div class="mt-6 flex justify-end gap-3">
          <button class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500" @click="closeConfirmModal">
            Cancel
          </button>
          <button
            class="rounded-lg px-4 py-2 text-sm font-semibold"
            :class="confirmAction === 'delete' ? 'bg-red-400/20 text-red-100 hover:bg-red-400/30' : 'bg-lime-300 text-slate-900 hover:bg-lime-200'"
            @click="handleConfirm"
          >
            {{ confirmAction === 'delete' ? 'Delete' : 'Continue' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showExportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/95 p-6 text-slate-100 shadow-2xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Export Publications</h3>
          <button class="text-slate-400 hover:text-white" @click="closeExportModal">x</button>
        </div>
        <div class="mt-4 grid gap-4">
          <div>
            <label class="text-xs uppercase tracking-[0.22em] text-slate-400">From year</label>
            <select
              v-model="exportFilters.fromYear"
              class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            >
              <option value="">Any</option>
              <option v-for="y in years" :key="`from-${y}`" :value="y">{{ y }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs uppercase tracking-[0.22em] text-slate-400">To year</label>
            <select
              v-model="exportFilters.toYear"
              class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm text-slate-100"
            >
              <option value="">Any</option>
              <option v-for="y in years" :key="`to-${y}`" :value="y">{{ y }}</option>
            </select>
          </div>
        </div>
        <p v-if="exportError" class="mt-3 text-xs text-rose-300">{{ exportError }}</p>
        <div class="mt-6 flex justify-end gap-3">
          <button class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500" @click="closeExportModal">
            Cancel
          </button>
          <button
            class="rounded-lg bg-lime-300 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-200"
            :disabled="exporting"
            @click="confirmExport"
          >
            Export
          </button>
        </div>
      </div>
    </div>

    <div v-if="showBulkDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
      <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/95 p-6 text-slate-100 shadow-2xl">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Delete publications</h3>
          <button class="text-slate-400 hover:text-white" @click="closeBulkDeleteModal">x</button>
        </div>
        <p class="mt-4 text-sm text-slate-300">
          Delete {{ selectedIds.length }} selected publication(s)? This cannot be undone.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500" @click="closeBulkDeleteModal">
            Cancel
          </button>
          <button class="rounded-lg bg-red-400/20 px-4 py-2 text-sm font-semibold text-red-100 hover:bg-red-400/30" @click="confirmBulkDelete">
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-if="showImportResult" :key="importRunId" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
      <div class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900/95 p-6 text-slate-100 shadow-2xl">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold">Import Summary</h3>
            <p class="text-xs uppercase tracking-[0.22em] text-slate-400">
              {{ importMeta.fileName || 'Unknown file' }} - {{ importMeta.time || '-' }}
            </p>
          </div>
          <button class="text-slate-400 hover:text-white" @click="closeImportResult">x</button>
        </div>
        <div class="mt-4 space-y-2 text-sm">
          <p>Inserted: <span class="font-semibold text-lime-200">{{ importResult.inserted }}</span></p>
          <p>Failed: <span class="font-semibold text-red-200">{{ importResult.failed }}</span></p>
          <p>Duplicates: <span class="font-semibold text-yellow-200">{{ importResult.duplicates }}</span></p>
        </div>
        <div v-if="currentFailedRows.length" class="mt-4">
          <button
            class="text-xs uppercase tracking-[0.22em] text-slate-400 hover:text-slate-200"
            @click="showImportDetails = !showImportDetails"
          >
            {{ showImportDetails ? 'Hide details' : 'Show details' }}
          </button>
        </div>
        <div v-if="showImportDetails && currentFailedRows.length" class="mt-4">
          <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Not imported</p>
          <ul class="mt-2 max-h-40 space-y-1 overflow-y-auto text-xs text-slate-300">
            <li v-for="item in currentFailedRows" :key="item.row">
              Row {{ item.row }}: {{ item.message }}
            </li>
          </ul>
        </div>
        <div class="mt-6 flex justify-end">
          <button class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500" @click="closeImportResult">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import * as XLSX from 'xlsx';
import PublicationModal from '../components/PublicationModal.vue';

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

export default {
  name: 'Publications',
  components: {
    PublicationModal
  },
  data() {
    return {
      loading: true,
      publications: [],
      filters: {
        year: '',
        college: '',
        type: '',
        search: ''
      },
      pagination: {
        current_page: 1,
        per_page: 20,
        total: 0,
        total_pages: 0
      },
      years: [],
      colleges: [],
      types: [],
      showModal: false,
      selectedPublication: null,
      modalMode: 'view',
      selectedIds: [],
      showBulkDeleteModal: false,
      showConfirmModal: false,
      showExportModal: false,
      exportFilters: {
        fromYear: '',
        toYear: ''
      },
      exportError: '',
      confirmAction: '',
      confirmTarget: null,
      confirmTitle: '',
      confirmMessage: '',
      searchTimeout: null,
      importing: false,
      exporting: false,
      showImportResult: false,
      importResult: {
        inserted: 0,
        failed: 0,
        duplicates: 0,
        skippedMissingYear: 0,
        skippedRows: [],
        failedRows: []
      },
      importMeta: {
        fileName: '',
        time: ''
      },
      showImportDetails: false,
      importRunId: 0
    }
  },
  computed: {
    visiblePages() {
      const pages = [];
      const current = this.pagination.current_page;
      const total = this.pagination.total_pages;

      let start = Math.max(1, current - 2);
      let end = Math.min(total, current + 2);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      return pages;
    },
    currentFailedRows() {
      return this.importResult.failedRows.filter(item => item.runId === this.importRunId);
    },
    allSelected() {
      if (!this.publications.length) {
        return false;
      }
      return this.publications.every(pub => this.selectedIds.includes(pub.id));
    }
  },
  mounted() {
    this.fetchPublications();
    this.fetchFilterOptions();
  },
  methods: {
    async fetchPublications() {
      this.loading = true;
      try {
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          ...this.filters
        };

        const response = await axios.get(`${apiBase}/publications`, { params });
        this.publications = response.data.data;
        this.pagination = response.data.pagination;
        this.selectedIds = [];
      } catch (error) {
        console.error('Error fetching publications:', error);
        alert('Failed to load publications');
      } finally {
        this.loading = false;
      }
    },

    async fetchFilterOptions() {
      // Fetch years, colleges, and types for filters
      try {
        const response = await axios.get(`${apiBase}/dashboard/publications-summary`);
        const data = response.data.data;

        this.years = data.by_year.map(y => y.year).sort((a, b) => b - a);
        this.colleges = [...new Set(data.by_college.map(c => c.college_institute))];
        this.types = data.by_type.map(t => t.publication_type);
      } catch (error) {
        console.error('Error fetching filter options:', error);
      }
    },

    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.pagination.current_page = 1;
        this.fetchPublications();
      }, 500);
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.total_pages) {
        this.pagination.current_page = page;
        this.fetchPublications();
      }
    },
    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedIds = [];
        return;
      }
      this.selectedIds = this.publications.map(pub => pub.id);
    },
    toggleSelectRow(id) {
      if (!id) return;
      if (this.selectedIds.includes(id)) {
        this.selectedIds = this.selectedIds.filter(item => item !== id);
      } else {
        this.selectedIds = [...this.selectedIds, id];
      }
    },
    openBulkDeleteModal() {
      if (!this.selectedIds.length) return;
      this.showBulkDeleteModal = true;
    },
    closeBulkDeleteModal() {
      this.showBulkDeleteModal = false;
    },
    async confirmBulkDelete() {
      if (!this.selectedIds.length) return;
      try {
        await Promise.all(this.selectedIds.map(id => axios.delete(`${apiBase}/publications/${id}`)));
        this.closeBulkDeleteModal();
        this.fetchPublications();
      } catch (error) {
        console.error('Error deleting publications:', error);
        alert('Failed to delete publications');
      }
    },

    showAddModal() {
      this.selectedPublication = null;
      this.modalMode = 'add';
      this.showModal = true;
    },

    viewPublication(pub) {
      this.selectedPublication = pub;
      this.modalMode = 'view';
      this.showModal = true;
    },

    editPublication(pub) {
      this.selectedPublication = pub;
      this.modalMode = 'edit';
      this.showModal = true;
    },

    async deletePublication(id) {
      try {
        await axios.delete(`${apiBase}/publications/${id}`);
        alert('Publication deleted successfully');
        this.fetchPublications();
      } catch (error) {
        console.error('Error deleting publication:', error);
        alert('Failed to delete publication');
      }
    },

    confirmEdit(pub) {
      this.confirmAction = 'edit';
      this.confirmTarget = pub;
      this.confirmTitle = 'Edit publication';
      this.confirmMessage = 'Do you want to edit this publication?';
      this.showConfirmModal = true;
    },

    confirmDelete(pub) {
      this.confirmAction = 'delete';
      this.confirmTarget = pub;
      this.confirmTitle = 'Delete publication';
      this.confirmMessage = 'Are you sure you want to delete this publication? This cannot be undone.';
      this.showConfirmModal = true;
    },

    closeConfirmModal() {
      this.showConfirmModal = false;
      this.confirmAction = '';
      this.confirmTarget = null;
      this.confirmTitle = '';
      this.confirmMessage = '';
    },
    openExportModal() {
      this.exportError = '';
      this.exportFilters = {
        fromYear: this.filters.year || '',
        toYear: this.filters.year || ''
      };
      this.showExportModal = true;
    },
    closeExportModal() {
      this.showExportModal = false;
      this.exportError = '';
    },
    confirmExport() {
      if (this.exportFilters.fromYear && this.exportFilters.toYear) {
        if (Number(this.exportFilters.fromYear) > Number(this.exportFilters.toYear)) {
          this.exportError = 'From year cannot be greater than To year.';
          return;
        }
      }
      this.showExportModal = false;
      this.exportExcel();
    },

    handleConfirm() {
      if (this.confirmAction === 'edit' && this.confirmTarget) {
        this.editPublication(this.confirmTarget);
      }
      if (this.confirmAction === 'delete' && this.confirmTarget) {
        this.deletePublication(this.confirmTarget.id);
      }
      this.closeConfirmModal();
    },

    closeModal() {
      this.showModal = false;
      this.selectedPublication = null;
    },

    handleSaved() {
      this.closeModal();
      this.fetchPublications();
    },

    triggerImport() {
      if (this.$refs.fileInput) {
        this.resetImportState();
        this.importMeta = { fileName: '', time: '' };
        this.$refs.fileInput.value = '';
        this.$refs.fileInput.click();
      }
    },

    normalizeHeader(value) {
      return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
    },

    mapHeaderToField(header) {
      const map = {
        year: 'year',
        year_period: 'year',
        year_s: 'year',
        college: 'college_institute',
        college_institute: 'college_institute',
        college_institution: 'college_institute',
        institute: 'college_institute',
        college_institute_r_d_center: 'college_institute',
        college_institute_rd_center: 'college_institute',
        college_institute_rnd_center: 'college_institute',
        title: 'title',
        title_of_publication_article: 'title',
        title_of_publication: 'title',
        publication_title: 'title',
        authors: 'authors',
        author: 'authors',
        author_s: 'authors',
        publication_type: 'publication_type',
        publication_type_s: 'publication_type',
        type: 'publication_type',
        journal_book: 'journal_book',
        journal: 'journal_book',
        title_of_journal_vol_no_book_proceedings: 'journal_book',
        title_of_journal_vol_no_book: 'journal_book',
        title_of_journal: 'journal_book',
        category: 'category',
        keywords: 'keywords',
        keyword: 'keywords',
        url: 'url',
        url_if_any: 'url',
        link: 'url',
        citations: 'citations',
        no_of_citations_citation_link: 'citations',
        no_of_citations: 'citations',
        remarks: 'remarks',
        remark: 'remarks',
        entry_by: 'entry_by',
        claim_for_incentive: 'claim_for_incentive',
        claim_for_incentives: 'claim_for_incentive',
        ors_registration: 'ors_registration',
        ors_registrati_on: 'ors_registration',
        ors_registratiion: 'ors_registration'
      };

      return map[header] || null;
    },

    isLikelyUrl(value) {
      return typeof value === 'string' && /^https?:\/\//i.test(value.trim());
    },

    splitAuthors(value) {
      if (!value) return [];
      if (Array.isArray(value)) {
        return value.map(author => String(author).trim()).filter(Boolean);
      }

      const parsed = this.parseAuthorsJson(value);
      if (parsed) {
        return parsed;
      }

      const normalized = String(value).replace(/\s+and\s+/gi, ',').replace(/&/g, ',');
      return normalized
        .split(/[,;/]/)
        .map(author => author.trim())
        .filter(Boolean);
    },

    parseAuthorsJson(value) {
      if (typeof value !== 'string') return null;
      const trimmed = value.trim();
      if (!trimmed.startsWith('[')) return null;
      try {
        const parsed = JSON.parse(trimmed);
        if (Array.isArray(parsed)) {
          return parsed.map(author => String(author).trim()).filter(Boolean);
        }
      } catch {
        return null;
      }

      return null;
    },

    formatAuthors(value) {
      if (Array.isArray(value)) {
        return value.join(', ');
      }

      const parsed = this.parseAuthorsJson(value);
      if (parsed) {
        return parsed.join(', ');
      }

      return value || '';
    },

    detectHeaderRow(rows) {
      const maxScan = Math.min(rows.length, 5);
      let bestIndex = 0;
      let bestScore = 0;

      for (let i = 0; i < maxScan; i++) {
        const row = rows[i] || [];
        let score = 0;

        row.forEach(cell => {
          const header = this.normalizeHeader(cell);
          if (header && this.mapHeaderToField(header)) {
            score += 1;
          }
        });

        if (score > bestScore) {
          bestScore = score;
          bestIndex = i;
        }
      }

      return bestScore >= 2 ? bestIndex : 0;
    },

    parseYearValue(value) {
      if (value === '' || value === null || typeof value === 'undefined') {
        return null;
      }

      if (value instanceof Date && !Number.isNaN(value.getTime())) {
        return value.getFullYear();
      }

      if (typeof value === 'number') {
        if (value >= 1900 && value <= 2100) {
          return Math.round(value);
        }

        if (value > 20000) {
          const parsed = XLSX.SSF.parse_date_code(value);
          if (parsed && parsed.y) {
            return parsed.y;
          }
        }
      }

      const text = String(value).trim();
      const match = text.match(/\b(19|20)\d{2}\b/);
      if (match) {
        return parseInt(match[0], 10);
      }

      return null;
    },

    extractYearFromRow(row) {
      for (const value of row) {
        const parsed = this.parseYearValue(value);
        if (parsed) {
          return parsed;
        }
      }

      return null;
    },

    async handleFileChange(event) {
      const file = event.target.files?.[0];
      if (!file) {
        return;
      }

      this.importRunId = Date.now();
      this.resetImportState();
      this.importMeta = {
        fileName: file.name,
        time: new Date().toLocaleString()
      };
      this.importing = true;
      try {
        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data, { type: 'array' });
        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });

        if (!rows.length) {
          alert('The file is empty.');
          return;
        }

        const headerRowIndex = this.detectHeaderRow(rows);
        const headers = rows[headerRowIndex].map(this.normalizeHeader);
        const publications = [];
        let skippedMissingYear = 0;
        const skippedRows = [];

        for (let i = headerRowIndex + 1; i < rows.length; i++) {
          const row = rows[i];
          const hasValues = row.some(value => String(value).trim() !== '');
          if (!hasValues) {
            continue;
          }

          const pub = {};
          headers.forEach((header, index) => {
            const field = this.mapHeaderToField(header);
            if (!field) {
              return;
            }

            const value = row[index];
            if (value === '' || value === null || typeof value === 'undefined') {
              return;
            }

            if (field === 'year') {
              const parsedYear = this.parseYearValue(value);
              if (parsedYear) {
                pub.year = parsedYear;
              }
              return;
            }

            if (field === 'citations') {
              const parsed = parseInt(value, 10);
              if (Number.isNaN(parsed)) {
                if (this.isLikelyUrl(value) && !pub.url) {
                  pub.url = value;
                } else {
                  pub.citations = value;
                }
              } else {
                pub.citations = parsed;
              }
              return;
            }

            if (field === 'authors') {
              pub.authors = this.splitAuthors(value);
            } else {
              pub[field] = value;
            }
          });

          if (!pub.year) {
            const fallbackYear = this.extractYearFromRow(row);
            if (fallbackYear) {
              pub.year = fallbackYear;
            }
          }

          if (!pub.year) {
            skippedMissingYear += 1;
            skippedRows.push({
              runId: this.importRunId,
              row: i + 1,
              title: pub.title || '',
              authors: this.formatAuthors(pub.authors),
              college: pub.college_institute || ''
            });
            continue;
          }

          if (Object.keys(pub).length) {
            publications.push(pub);
          }
        }

        if (!publications.length) {
          alert('No valid rows found in the file.');
          return;
        }

        const response = await axios.post(`${apiBase}/publications/bulk-import`, publications);
        const summary = response.data?.summary;
        const failedRows = (response.data?.errors || []).map(err => ({
          runId: this.importRunId,
          row: headerRowIndex + 2 + (err.index ?? 0),
          message: Object.values(err.errors || {}).join(', ') || 'Failed to insert'
        }));
        this.importResult = {
          inserted: summary?.inserted ?? 0,
          failed: summary?.failed ?? 0,
          duplicates: summary?.duplicates ?? 0,
          skippedMissingYear,
          skippedRows,
          failedRows
        };
        this.recordAudit('publication.import', {
          total: publications.length,
          inserted: summary?.inserted ?? 0,
          failed: summary?.failed ?? 0,
          duplicates: summary?.duplicates ?? 0,
          skipped_missing_year: skippedMissingYear
        });
        this.showImportResult = true;

        this.fetchPublications();
      } catch (error) {
        console.error('Error importing publications:', error);
        alert('Failed to import publications. Please check the file format.');
      } finally {
        event.target.value = '';
        this.importing = false;
      }
    },

    async exportExcel() {
      this.exporting = true;
      try {
        const fromYear = this.exportFilters.fromYear;
        const toYear = this.exportFilters.toYear;
        const yearRangeFilter = (pub) => {
          if (!fromYear && !toYear) return true;
          if (fromYear && Number(pub.year) < Number(fromYear)) return false;
          if (toYear && Number(pub.year) > Number(toYear)) return false;
          return true;
        };

        const params = {
          page: 1,
          per_page: 10000,
          ...this.filters
        };

        const response = await axios.get(`${apiBase}/publications`, { params });
        const publications = (response.data.data || []).filter(yearRangeFilter);

        if (!publications.length) {
          alert('No publications to export.');
          return;
        }

        const rows = publications.map(pub => ({
          Year: pub.year,
          'College/Institute': pub.college_institute,
          Title: pub.title,
          Authors: this.formatAuthors(pub.authors),
          'Publication Type': pub.publication_type,
          Citations: pub.citations,
          'Journal/Book': pub.journal_book,
          Category: pub.category,
          Keywords: pub.keywords,
          URL: pub.url,
          Remarks: pub.remarks,
          'Entry By': pub.entry_by,
          'Claim for Incentive': pub.claim_for_incentive,
          'ORS Registration': pub.ors_registration
        }));

        const worksheet = XLSX.utils.json_to_sheet(rows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Publications');

        const dateStamp = new Date().toISOString().slice(0, 10);
        const suffix = fromYear || toYear ? `_years_${fromYear || 'any'}-${toYear || 'any'}` : '';
        XLSX.writeFile(workbook, `publications_${dateStamp}${suffix}.xlsx`);

        this.recordAudit('publication.export', {
          count: rows.length,
          from_year: fromYear || null,
          to_year: toYear || null,
          filters: {
            year: this.filters.year || null,
            college: this.filters.college || null,
            type: this.filters.type || null,
            search: this.filters.search || null
          }
        });
      } catch (error) {
        console.error('Error exporting publications:', error);
        alert('Failed to export publications.');
      } finally {
        this.exporting = false;
      }
    },
    async recordAudit(action, metadata) {
      try {
        await axios.post(`${apiBase}/audit-logs/record`, {
          action,
          metadata
        });
      } catch (error) {
        console.error('Audit log failed:', error);
      }
    },
    closeImportResult() {
      this.resetImportState();
      this.importMeta = {
        fileName: '',
        time: ''
      };
    },
    resetImportState() {
      this.showImportResult = false;
      this.showImportDetails = false;
      this.importResult = {
        inserted: 0,
        failed: 0,
        duplicates: 0,
        skippedMissingYear: 0,
        skippedRows: [],
        failedRows: []
      };
    }
  }
}
</script>

<style scoped>
</style>
