<aside class="w-full rounded-2xl border border-slate-800 bg-slate-950/95 p-5 text-slate-200 shadow-sm ring-1 ring-slate-800/60">
    <div class="mb-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-600">Dashboard</p>
        <h3 class="mt-2 text-xl font-semibold leading-tight text-white">Workspace</h3>
    </div>

    <nav class="space-y-2">
        <a
            href="{{ route('questions') }}"
            class="block rounded-xl px-4 py-3 text-sm font-semibold leading-5 transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'bg-blue-600 text-white shadow-[0_10px_24px_-12px_rgba(37,99,235,0.9)]' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}"
        >
            Questions
        </a>

        <a
            href="{{ route('solutions') }}"
            class="block rounded-xl px-4 py-3 text-sm font-semibold leading-5 transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'bg-blue-600 text-white shadow-[0_10px_24px_-12px_rgba(37,99,235,0.9)]' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}"
        >
            Solutions
        </a>
    </nav>

    <div class="my-6 border-t border-slate-800"></div>

    <div x-data="{ subServicesOpen: true }">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-semibold leading-5 text-slate-200 transition hover:bg-slate-800"
            @click="subServicesOpen = !subServicesOpen"
        >
            <span>Sub Services</span>
            <span class="text-xs text-slate-400" x-text="subServicesOpen ? 'Hide' : 'Show'"></span>
        </button>

        <div class="mt-2 space-y-1.5" x-show="subServicesOpen" x-cloak>
            <a href="{{ route('subservices.files') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.files') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Files</a>
            <a href="{{ route('subservices.docs') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.docs') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Docs</a>
            <a href="{{ route('subservices.sheets') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.sheets') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Sheets</a>
            <a href="{{ route('subservices.press') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.press') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Press</a>
            <a href="{{ route('subservices.forms') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.forms') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Forms</a>
            <a href="{{ route('subservices.engage') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.engage') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-blue-300' }}">Dot.Engage</a>
        </div>
    </div>
</aside>