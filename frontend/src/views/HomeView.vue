<template>
  <main class="mx-auto w-full max-w-none px-6 pb-16 pt-10 md:px-10 2xl:px-20 2xl:pb-24">
        <section class="grid items-center gap-12 lg:grid-cols-[1.15fr_0.85fr] xl:grid-cols-[1.2fr_0.8fr] 2xl:grid-cols-[1.25fr_0.75fr]">
          <div>
            <p class="inline-flex items-center rounded-full border border-slate-800 bg-slate-900/70 px-3 py-1 text-xs uppercase tracking-[0.28em] text-slate-400">
              Live research intelligence
            </p>
            <h1 class="mt-6 text-4xl font-black leading-tight text-white md:text-5xl">
              Monitor BSU research outputs with clarity, speed, and trust.
            </h1>
            <br>
            <p class="mt-4 max-w-xl text-base text-slate-300">
              A single homepage for KPIs, publication insights, and faculty performance. Built for accurate tracking,
              clean reporting, and fast decision-making.
            </p>
            <br>
            <div class="mt-8 flex flex-wrap gap-3">
              <button class="rounded-lg bg-lime-300 px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-lime-200">
                Explore dashboard
              </button>
              <button class="rounded-lg border border-slate-700 px-5 py-3 text-sm font-semibold text-slate-100 hover:border-slate-400">
                View publications
              </button>
            </div>
            <br>
            <div class="mt-8 flex flex-wrap gap-6 text-xs uppercase tracking-[0.24em] text-slate-500">
              <span>2017-2025 Coverage</span>
              <span>Real-time KPIs</span>
              <span>Export-ready</span>
            </div>
          </div>

          <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
            <div class="flex items-center justify-between text-xs uppercase tracking-[0.22em] text-slate-400">
              <span>Dashboard Snapshot</span>
              <span class="rounded-full bg-green-500/10 px-3 py-1 text-green-300">Updated 5m ago</span>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
              <div class="rounded-2xl bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Total Publications</p>
                <p class="mt-3 text-3xl font-semibold">401</p>
              </div>
              <div class="rounded-2xl bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Publications 2025</p>
                <p class="mt-3 text-3xl font-semibold">54</p>
              </div>
              <div class="rounded-2xl bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Total Faculty</p>
                <p class="mt-3 text-3xl font-semibold">59</p>
              </div>
              <div class="rounded-2xl bg-slate-950/70 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Avg H-Index</p>
                <p class="mt-3 text-3xl font-semibold">3.34</p>
              </div>
            </div>
            <br>
            <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
              <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Publications Trend</div>
              <div class="mt-3 h-24 rounded-xl bg-gradient-to-r from-green-500/20 via-lime-400/20 to-yellow-400/20"></div>
            </div>
          </div>
        </section>
        <br>

        <section id="dashboard" class="mt-16 grid gap-6 lg:grid-cols-[0.95fr_1.05fr] xl:grid-cols-[0.9fr_1.1fr] 2xl:grid-cols-[0.85fr_1.15fr]">
          <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
            <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Alert Center</div>
            <h2 class="mt-4 text-2xl font-semibold">Data quality at a glance</h2>
            <p class="mt-3 text-sm text-slate-300">
              Surface missing H-index values, low citation profiles, and inactive publication streaks.
            </p>
            <div class="mt-6 grid gap-3">
              <div class="flex items-center justify-between rounded-2xl border border-yellow-400/30 bg-yellow-500/10 p-4">
                <div>
                  <p class="text-sm font-semibold">Missing H-Index</p>
                  <p class="text-xs text-yellow-200/80">Review needed</p>
                </div>
                <span class="text-2xl font-semibold text-yellow-200">7</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl border border-red-400/30 bg-red-500/10 p-4">
                <div>
                  <p class="text-sm font-semibold">Low Citations</p>
                  <p class="text-xs text-red-200/80">Action needed</p>
                </div>
                <span class="text-2xl font-semibold text-red-200">12</span>
              </div>
              <div class="flex items-center justify-between rounded-2xl border border-lime-400/30 bg-lime-500/10 p-4">
                <div>
                  <p class="text-sm font-semibold">No 2024-2025 Entries</p>
                  <p class="text-xs text-lime-200/80">Monitor</p>
                </div>
                <span class="text-2xl font-semibold text-lime-200">5</span>
              </div>
            </div>
          </div>

          <div class="grid gap-6">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
              <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold">Recent Publications</h2>
                <button class="text-xs uppercase tracking-[0.22em] text-lime-300 hover:text-lime-200">
                  View all
                </button>
              </div>
              <div class="mt-6 divide-y divide-slate-800">
                <div class="flex items-start justify-between gap-4 py-4">
                  <div>
                    <p class="text-sm font-semibold">Meanings and Experiences of Survival Among Filipino Women Survivors of Abuse</p>
                    <p class="mt-1 text-xs text-slate-400">Jan-Jun 2025 · CPAG/OSS · Journal</p>
                  </div>
                  <span class="rounded-full bg-green-500/10 px-3 py-1 text-xs text-green-300">Peer-reviewed</span>
                </div>
                <div class="flex items-start justify-between gap-4 py-4">
                  <div>
                    <p class="text-sm font-semibold">On-Farm Evaluation of Auxin-Treated Sugarcane Bud Chip</p>
                    <p class="mt-1 text-xs text-slate-400">Jan-Jun 2025 · CA · Journal</p>
                  </div>
                  <span class="rounded-full bg-lime-500/10 px-3 py-1 text-xs text-lime-300">Co-authors</span>
                </div>
                <div class="flex items-start justify-between gap-4 py-4">
                  <div>
                    <p class="text-sm font-semibold">TeachTokerists in the Philippines: A Husserlian Phenomenology</p>
                    <p class="mt-1 text-xs text-slate-400">Jan-Jun 2023 · CAH · Journal</p>
                  </div>
                  <span class="rounded-full bg-yellow-500/10 px-3 py-1 text-xs text-yellow-200">Open access</span>
                </div>
              </div>
            </div>

            <div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
              <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Fast actions</div>
              <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
                  Import Excel
                </button>
                <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
                  Add publication
                </button>
                <button class="rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3 text-sm font-semibold hover:border-slate-600">
                  Export report
                </button>
              </div>
            </div>
          </div>
        </section>
        <br>

        <section id="publications" class="mt-16 grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3">
          <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Publications</p>
            <h3 class="mt-4 text-xl font-semibold">Smart filtering and search</h3>
            <p class="mt-2 text-sm text-slate-300">
              Slice records by year, college, category, or citation count. Find results instantly with multi-field
              search.
            </p>
          </div>
          <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Faculty</p>
            <h3 class="mt-4 text-xl font-semibold">Researcher performance</h3>
            <p class="mt-2 text-sm text-slate-300">
              Track H-index, citations, and scholar profiles. Highlight top performers and data gaps in one view.
            </p>
          </div>
          <div class="rounded-3xl border border-slate-800 bg-slate-900/60 p-6">
            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Reports</p>
            <h3 class="mt-4 text-xl font-semibold">Export-ready analytics</h3>
            <p class="mt-2 text-sm text-slate-300">
              Generate publication summaries, trend charts, and audit trails for internal reviews.
            </p>
          </div>
        </section>
        <br>

        <section id="faculty" class="mt-16 rounded-3xl border border-slate-800 bg-slate-900/60 p-8">
          <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
              <h2 class="text-3xl font-semibold">Faculty Metrics Spotlight</h2>
              <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Visualize citation growth and H-index rankings. Link directly to Google Scholar profiles.
              </p>
            </div>
            <button class="rounded-full border border-slate-700 px-5 py-2 text-sm font-semibold hover:border-slate-400">
              View faculty directory
            </button>
          </div>
          <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
              <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Top Citations</div>
              <p class="mt-3 text-2xl font-semibold">588</p>
              <p class="mt-1 text-xs text-slate-400">Cheryll C. Launio</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
              <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Highest H-Index</div>
              <p class="mt-3 text-2xl font-semibold">13</p>
              <p class="mt-1 text-xs text-slate-400">Cheryll C. Launio</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5">
              <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Most Productive</div>
              <p class="mt-3 text-2xl font-semibold">54</p>
              <p class="mt-1 text-xs text-slate-400">2025 Publications</p>
            </div>
          </div>
        </section>
        <br>

        <section id="reports" class="mt-16 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8">
          <div class="flex flex-wrap items-center justify-between gap-6">
            <div>
              <h2 class="text-3xl font-semibold">Ready to launch your REMIS portal?</h2>
              <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Use the data already collected to create a searchable, reliable, and export-ready research system.
              </p>
            </div>
            <div class="flex flex-wrap gap-3">
              <button class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-200">
                Start with Excel import
              </button>
              <button class="rounded-lg border border-slate-600 px-5 py-3 text-sm font-semibold hover:border-slate-400">
                Contact admin
              </button>
            </div>
          </div>
        </section>
  </main>
</template>
