<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Solution Details') }}
            </h2>
            <a href="{{ route('solutions') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Back to Solutions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        @php
            $steps = \App\Models\Steps::where('solution_id', $solution->id)->get() ?? '';
        @endphp

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Contributed by {{ $solution->user->name }}</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $solution->solution_title }}</h1>
                <p class="mt-4 text-sm leading-6 text-slate-700">{{ $solution->solution_description }}</p>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <livewire:comments :model="$solution" :solution="$solution" />
                </div>
            </article>

            <section class="space-y-4">
                @foreach($steps as $step)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step {{ $loop->iteration }}</p>
                        <p class="mt-2 text-base font-semibold text-slate-900">{{ $step->solution_heading }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $step->solution_body }}</p>
                    </div>
                @endforeach
            </section>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
