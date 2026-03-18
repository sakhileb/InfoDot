<aside class="w-full rounded-3xl border border-slate-800 bg-slate-950/90 p-4 text-slate-100 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.9)] ring-1 ring-slate-800/80 backdrop-blur">
    <div class="mb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-300/80">Dashboard</p>
        <h3 class="mt-2 text-lg font-semibold text-white">Workspace</h3>
    </div>

    <nav class="space-y-1">
        <a
            href="{{ route('questions') }}"
            class="block rounded-2xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}"
        >
            Questions
        </a>

        <a
            href="{{ route('solutions') }}"
            class="block rounded-2xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30 shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}"
        >
            Solutions
        </a>
    </nav>

    <div class="my-5 border-t border-slate-800"></div>

    <div x-data="{ subServicesOpen: true }">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-sm font-semibold text-slate-100 hover:bg-slate-900"
            @click="subServicesOpen = !subServicesOpen"
        >
            <span>Sub Services</span>
            <span class="text-xs text-slate-400" x-text="subServicesOpen ? 'Hide' : 'Show'"></span>
        </button>

        <div class="mt-1 space-y-1" x-show="subServicesOpen" x-cloak>
            <a href="{{ route('subservices.files') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.files') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Files</a>
            <a href="{{ route('subservices.docs') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.docs') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Docs</a>
            <a href="{{ route('subservices.sheets') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.sheets') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Sheets</a>
            <a href="{{ route('subservices.press') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.press') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Press</a>
            <a href="{{ route('subservices.forms') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.forms') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Forms</a>
            <a href="{{ route('subservices.engage') }}" class="block rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs('subservices.engage') ? 'bg-sky-500/20 text-sky-200 ring-1 ring-sky-400/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">Dot.Engage</a>
        </div>
    </div>
</aside>