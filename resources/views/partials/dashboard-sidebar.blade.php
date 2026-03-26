<aside class="w-full rounded-2xl border border-[#434656]/25 bg-[#131b2e] p-5 text-[#dae2fd] shadow-sm" style="font-family:'Inter',sans-serif;">
    <div class="mb-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#b6c4ff]">Dashboard</p>
        <h3 class="mt-2 text-xl font-semibold leading-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">Workspace</h3>
    </div>

    <nav class="space-y-2">
        <a
            href="{{ route('questions') }}"
            class="block rounded-xl px-4 py-3 text-sm font-semibold leading-5 transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'border-l-4 border-[#2962ff] bg-[#2962ff]/10 text-[#b6c4ff]' : 'text-[#b7c8e1] opacity-80 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}"
        >
            Questions
        </a>

        <a
            href="{{ route('solutions') }}"
            class="block rounded-xl px-4 py-3 text-sm font-semibold leading-5 transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'border-l-4 border-[#2962ff] bg-[#2962ff]/10 text-[#b6c4ff]' : 'text-[#b7c8e1] opacity-80 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}"
        >
            Solutions
        </a>
    </nav>

    <div class="my-6 border-t border-[#434656]/30"></div>

    <div x-data="{ subServicesOpen: true }">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-sm font-semibold leading-5 text-[#b7c8e1] transition hover:bg-[#1a2438] hover:text-[#b6c4ff]"
            @click="subServicesOpen = !subServicesOpen"
        >
            <span>Sub Services</span>
            <span class="text-xs text-[#8d90a2]" x-text="subServicesOpen ? 'Hide' : 'Show'"></span>
        </button>

        <div class="mt-2 space-y-1.5" x-show="subServicesOpen" x-cloak>
            <a href="{{ route('subservices.files') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.files') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Files</a>
            <a href="{{ route('subservices.docs') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.docs') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Docs</a>
            <a href="{{ route('subservices.sheets') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.sheets') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Sheets</a>
            <a href="{{ route('subservices.press') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.press') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Press</a>
            <a href="{{ route('subservices.forms') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.forms') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Forms</a>
            <a href="{{ route('subservices.engage') }}" class="block rounded-xl px-4 py-2.5 text-sm leading-5 transition {{ request()->routeIs('subservices.engage') ? 'bg-[#2962ff]/10 text-[#b6c4ff] border-l-4 border-[#2962ff]' : 'text-[#b7c8e1] opacity-70 hover:bg-[#1a2438] hover:opacity-100 hover:text-[#b6c4ff]' }}">Dot.Engage</a>
        </div>
    </div>
</aside>