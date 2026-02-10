<template>
  <header class="mx-auto flex w-full max-w-none items-center justify-between px-6 py-6 md:px-10 2xl:px-20">
    <div class="flex items-center gap-3">
      <div class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-900">
        <span class="text-lg font-black">R</span>
      </div>
      <div>
        <div class="text-xs uppercase tracking-[0.32em] text-slate-400">BSU REMIS</div>
        <div class="text-lg font-semibold">Research Monitoring System</div>
      </div>
    </div>
    <nav class="hidden items-center gap-6 text-sm text-slate-300 md:flex">
      <a class="hover:text-white" href="#dashboard">Dashboard</a>
      <a class="hover:text-white" href="#publications">Publications</a>
      <a class="hover:text-white" href="#faculty">Faculty</a>
      <a class="hover:text-white" href="#reports">Reports</a>
    </nav>
    <button class="rounded-full border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-100 hover:border-slate-400">
      Sign in
    </button>
    <div class="min-h-screen bg-slate-950 text-slate-100">
    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute -top-40 left-10 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
      <div class="pointer-events-none absolute right-0 top-20 h-80 w-80 rounded-full bg-amber-500/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-32 left-1/3 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
    </div>
    </div>
  </header>
</template>
