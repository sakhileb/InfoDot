<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-100">Dot.Engage</h2>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-slate-800 bg-slate-900/85 p-6 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70">
            <h3 class="text-lg font-semibold text-white">Dot.Engage Workspace</h3>
            <p class="mt-2 text-sm text-slate-400">This page is ready for engagement tools such as campaigns, interactions, and audience activity tracking.</p>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
