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

    <div class="px-1">
        <livewire:dot-switcher :inline="true" />
    </div>
</aside>