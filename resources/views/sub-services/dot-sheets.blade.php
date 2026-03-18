<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold leading-9 text-slate-100">Dot.Sheets</h2>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-sm ring-1 ring-slate-800/70">
            <h3 class="text-xl font-semibold leading-8 text-white">Dot.Sheets Workspace</h3>
            <p class="mt-3 text-sm leading-7 text-slate-400">This page is ready for spreadsheet-style data entry, formulas, and collaborative planning workflows.</p>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
