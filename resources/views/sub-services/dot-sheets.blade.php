<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">Dot.Sheets</h2>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Dot.Sheets Workspace</h3>
            <p class="mt-2 text-sm text-slate-600">This page is ready for spreadsheet-style data entry, formulas, and collaborative planning workflows.</p>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
