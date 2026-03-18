<aside class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Dashboard</p>
    </div>

    <nav class="space-y-1">
        <a
            href="{{ route('questions') }}"
            class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
        >
            Questions
        </a>

        <a
            href="{{ route('solutions') }}"
            class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}"
        >
            Solutions
        </a>
    </nav>

    <div class="my-5 border-t border-slate-200"></div>

    <div x-data="{ subServicesOpen: true }">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-slate-100"
            @click="subServicesOpen = !subServicesOpen"
        >
            <span>Sub Services</span>
            <span class="text-xs text-slate-500" x-text="subServicesOpen ? 'Hide' : 'Show'"></span>
        </button>

        <div class="mt-1 space-y-1" x-show="subServicesOpen" x-cloak>
            <a href="{{ route('subservices.files') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.files') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Files</a>
            <a href="{{ route('subservices.docs') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.docs') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Docs</a>
            <a href="{{ route('subservices.sheets') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.sheets') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Sheets</a>
            <a href="{{ route('subservices.press') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.press') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Press</a>
            <a href="{{ route('subservices.forms') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.forms') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Forms</a>
            <a href="{{ route('subservices.engage') }}" class="block rounded-xl px-3 py-2 text-sm transition {{ request()->routeIs('subservices.engage') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Dot.Engage</a>
        </div>
    </div>
</aside>