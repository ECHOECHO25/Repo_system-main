<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
    <div class="flex w-full max-w-5xl max-h-[90vh] flex-col rounded-3xl border border-slate-800 bg-slate-900/90 text-slate-100 shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
        <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
        <button class="text-slate-400 hover:text-white" @click="handleClose">✕</button>
      </div>

      <div class="flex-1 overflow-y-auto px-6 py-6">
        <form @submit.prevent="save">
          <div class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Year</label>
              <input v-model="form.year" type="number" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" required />
            </div>
            <div class="md:col-span-3">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">College/Institute</label>
              <input v-model="form.college_institute" type="text" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" required />
            </div>
            <div class="md:col-span-1">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Type</label>
              <input v-model="form.publication_type" type="text" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" required />
            </div>
            <div class="md:col-span-6">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Title</label>
              <textarea v-model="form.title" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" rows="2" :disabled="isView" required></textarea>
            </div>
            <div class="md:col-span-6">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Authors</label>
              <textarea v-model="form.authors" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" rows="2" :disabled="isView" required></textarea>
            </div>
            <div class="md:col-span-6">
              <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Author Matches</p>
                    <p class="mt-2 text-xs text-slate-400">
                      Match authors to faculty records. Unmatched names can be added as new faculty.
                    </p>
                  </div>
                  <span v-if="!publication?.id" class="text-xs text-slate-400">Save publication to manage matches.</span>
                </div>

                <div v-if="publication?.id" class="mt-4 space-y-3">
                  <div
                    v-for="(match, index) in authorMatches"
                    :key="`${match.authorName}-${index}`"
                    class="rounded-xl border border-slate-800 bg-slate-900/70 p-3"
                  >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <p class="text-sm font-semibold text-slate-100">{{ match.authorName }}</p>
                      <span
                        class="rounded-full px-3 py-1 text-[10px] uppercase tracking-[0.22em]"
                        :class="match.status === 'confirmed'
                          ? 'bg-emerald-500/10 text-emerald-200'
                          : 'bg-amber-500/10 text-amber-200'"
                      >
                        {{ match.status === 'confirmed' ? 'Matched' : 'Unmatched' }}
                      </span>
                    </div>

                    <div v-if="match.status === 'confirmed'" class="mt-3 text-xs text-slate-300">
                      Linked faculty: <span class="font-semibold text-emerald-200">{{ match.facultyName || 'Unknown' }}</span>
                    </div>

                    <div v-else class="mt-3 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-end">
                      <div class="relative">
                        <label class="text-[10px] uppercase tracking-[0.2em] text-slate-500">Search faculty</label>
                        <input
                          v-model="match.query"
                          type="text"
                          class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100"
                          :disabled="isView"
                          @input="debounceMatchSearch(index)"
                          @focus="openMatchOptions(index)"
                        />
                        <div
                          v-if="match.optionsOpen && match.options.length"
                          class="absolute z-20 mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 p-2 text-sm shadow-2xl"
                        >
                          <button
                            v-for="option in match.options"
                            :key="option.id"
                            type="button"
                            class="block w-full rounded-lg px-2 py-2 text-left text-slate-200 hover:bg-slate-800"
                            @click="selectMatchFaculty(index, option)"
                          >
                            {{ option.name }}
                          </button>
                        </div>
                        <div v-if="match.loading" class="mt-2 text-xs text-slate-400">Searching...</div>
                        <p v-if="match.error" class="mt-2 text-xs text-rose-300">{{ match.error }}</p>
                      </div>

                      <button
                        type="button"
                        class="rounded-full border border-emerald-400/40 px-4 py-2 text-xs uppercase tracking-[0.22em] text-emerald-200 hover:border-emerald-300 disabled:opacity-50"
                        :disabled="isView || !match.facultyId"
                        @click="confirmMatch(index)"
                      >
                        Assign
                      </button>

                      <button
                        type="button"
                        class="rounded-full border border-slate-700 px-4 py-2 text-xs uppercase tracking-[0.22em] text-slate-200 hover:border-slate-500 disabled:opacity-50"
                        :disabled="isView"
                        @click="addFacultyFromAuthor(index)"
                      >
                        Add faculty
                      </button>
                    </div>
                  </div>

                  <div v-if="!authorMatches.length" class="rounded-xl border border-slate-800 bg-slate-900/60 p-3 text-xs text-slate-400">
                    No authors found for this publication.
                  </div>
                </div>
              </div>
            </div>
            <div class="md:col-span-3">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Journal/Book</label>
              <input v-model="form.journal_book" type="text" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" />
            </div>
            <div class="md:col-span-3">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Category</label>
              <input v-model="form.category" type="text" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" />
            </div>
            <div class="md:col-span-6">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Keywords</label>
              <input v-model="form.keywords" type="text" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" />
            </div>
            <div class="md:col-span-4">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">URL</label>
              <input v-model="form.url" type="url" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" />
            </div>
            <div class="md:col-span-2">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Citations</label>
              <input v-model.number="form.citations" type="number" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" :disabled="isView" />
            </div>
            <div class="md:col-span-6">
              <label class="text-xs uppercase tracking-[0.22em] text-slate-500">Remarks</label>
              <textarea v-model="form.remarks" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2 text-sm" rows="2" :disabled="isView"></textarea>
            </div>
          </div>
        </form>
      </div>

      <div class="flex items-center justify-end gap-3 border-t border-slate-800 px-6 py-4">
        <button class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-slate-200 hover:border-slate-500" @click="handleClose">
          Close
        </button>
        <button
          v-if="!isView"
          type="button"
          class="rounded-lg bg-lime-300 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-200 disabled:opacity-60"
          @click="save"
          :disabled="saving"
        >
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';

export default {
  name: 'PublicationModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    publication: {
      type: Object,
      default: null
    },
    mode: {
      type: String,
      default: 'view'
    }
  },
  data() {
    return {
      saving: false,
      form: this.emptyForm(),
      authorMatches: [],
      matchSearchTimers: {}
    };
  },
  computed: {
    isView() {
      return this.mode === 'view';
    },
    modalTitle() {
      if (this.mode === 'add') return 'Add Publication';
      if (this.mode === 'edit') return 'Edit Publication';
      return 'View Publication';
    }
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.hydrateForm();
        this.loadAuthorMatches();
      }
    },
    publication() {
      this.hydrateForm();
      this.loadAuthorMatches();
    }
  },
  methods: {
    emptyForm() {
      return {
        year: '',
        college_institute: '',
        title: '',
        authors: '',
        publication_type: '',
        journal_book: '',
        category: '',
        keywords: '',
        url: '',
        citations: null,
        remarks: ''
      };
    },
    hydrateForm() {
      if (this.publication) {
        this.form = {
          year: this.publication.year ?? '',
          college_institute: this.publication.college_institute ?? '',
          title: this.publication.title ?? '',
          authors: this.formatAuthors(this.publication.authors),
          publication_type: this.publication.publication_type ?? '',
          journal_book: this.publication.journal_book ?? '',
          category: this.publication.category ?? '',
          keywords: this.publication.keywords ?? '',
          url: this.publication.url ?? '',
          citations: this.publication.citations ?? null,
          remarks: this.publication.remarks ?? ''
        };
      } else {
        this.form = this.emptyForm();
      }
    },
    normalizeAuthorName(value) {
      return String(value || '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
    },
    buildInitialMatches(authors) {
      this.authorMatches = authors.map((authorName) => ({
        authorName,
        linkId: null,
        status: 'pending',
        facultyId: null,
        facultyName: '',
        query: '',
        options: [],
        optionsOpen: false,
        loading: false,
        error: ''
      }));
    },
    async loadAuthorMatches() {
      if (!this.publication?.id) {
        this.authorMatches = [];
        return;
      }

      const authors = this.splitAuthors(this.form.authors);
      this.buildInitialMatches(authors);

      if (!authors.length) return;

      try {
        const response = await axios.get(`${apiBase}/publication-author-links/publication/${this.publication.id}`);
        const links = response.data?.data || [];
        const linkMap = new Map(
          links.map((link) => [this.normalizeAuthorName(link.author_name), link])
        );

        this.authorMatches = this.authorMatches.map((match) => {
          const link = linkMap.get(this.normalizeAuthorName(match.authorName));
          if (!link) return match;
          return {
            ...match,
            linkId: link.id,
            status: link.status || 'pending',
            facultyId: link.faculty_id || null,
            facultyName: link.faculty_name || '',
            query: link.faculty_name || ''
          };
        });
      } catch (error) {
        console.error('Error loading author matches:', error);
      }
    },
    openMatchOptions(index) {
      const match = this.authorMatches[index];
      if (match?.options?.length) {
        match.optionsOpen = true;
      }
    },
    debounceMatchSearch(index) {
      if (!this.authorMatches[index]) return;
      if (this.matchSearchTimers[index]) {
        clearTimeout(this.matchSearchTimers[index]);
      }
      this.matchSearchTimers[index] = setTimeout(() => {
        this.searchFaculty(index);
      }, 300);
    },
    async searchFaculty(index) {
      const match = this.authorMatches[index];
      if (!match) return;
      const query = match.query.trim();
      if (!query) {
        match.options = [];
        match.optionsOpen = false;
        return;
      }
      match.loading = true;
      match.error = '';
      try {
        const response = await axios.get(`${apiBase}/faculty`, {
          params: {
            search: query,
            per_page: 8
          }
        });
        match.options = (response.data?.data || []).map((faculty) => ({
          id: faculty.id,
          name: faculty.name
        }));
        match.optionsOpen = true;
      } catch (error) {
        console.error('Error searching faculty:', error);
        match.error = 'Failed to search faculty.';
      } finally {
        match.loading = false;
      }
    },
    selectMatchFaculty(index, option) {
      const match = this.authorMatches[index];
      if (!match) return;
      match.facultyId = option.id;
      match.facultyName = option.name;
      match.query = option.name;
      match.optionsOpen = false;
      match.error = '';
    },
    async confirmMatch(index) {
      const match = this.authorMatches[index];
      if (!match || !match.facultyId || !this.publication?.id) return;
      match.error = '';
      try {
        if (match.linkId) {
          await axios.put(`${apiBase}/publication-author-links/${match.linkId}`, {
            status: 'confirmed',
            faculty_id: match.facultyId
          });
        } else {
          await axios.post(`${apiBase}/publication-author-links`, {
            publication_id: this.publication.id,
            author_name: match.authorName,
            faculty_id: match.facultyId,
            status: 'confirmed'
          });
        }
        await this.loadAuthorMatches();
      } catch (error) {
        console.error('Error confirming match:', error);
        match.error = 'Failed to assign faculty.';
      }
    },
    async addFacultyFromAuthor(index) {
      const match = this.authorMatches[index];
      if (!match || !this.publication?.id) return;
      match.error = '';
      try {
        const response = await axios.post(`${apiBase}/faculty`, {
          name: match.authorName,
          status: 'active'
        });
        const facultyId = response.data?.data?.id;
        if (!facultyId) {
          match.error = 'Faculty created, but no ID returned.';
          return;
        }
        match.facultyId = facultyId;
        match.facultyName = response.data?.data?.name || match.authorName;
        await this.confirmMatch(index);
      } catch (error) {
        if (error?.response?.status === 409) {
          match.error = 'Faculty already exists. Search and select it.';
        } else {
          match.error = 'Failed to create faculty.';
        }
      }
    },
    handleClose() {
      this.$emit('close');
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
    async save() {
      if (this.isView) return;
      this.saving = true;
      try {
        const payload = {
          ...this.form,
          authors: this.splitAuthors(this.form.authors)
        };
        if (this.mode === 'edit' && this.publication?.id) {
          await axios.put(`${apiBase}/publications/${this.publication.id}`, payload);
        } else {
          await axios.post(`${apiBase}/publications`, payload);
        }
        this.$emit('saved');
      } catch (error) {
        console.error('Error saving publication:', error);
        alert('Failed to save publication');
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>

<style scoped>
</style>
