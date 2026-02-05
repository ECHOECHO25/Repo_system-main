<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4">
    <div class="w-full max-w-5xl rounded-3xl border border-slate-800 bg-slate-900/90 text-slate-100 shadow-2xl">
      <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
        <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
        <button class="text-slate-400 hover:text-white" @click="handleClose">✕</button>
      </div>

      <div class="px-6 py-6">
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
      form: this.emptyForm()
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
      }
    },
    publication() {
      this.hydrateForm();
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
