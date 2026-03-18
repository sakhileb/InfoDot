<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Solution Details') }}
            </h2>
            <a href="{{ route('solutions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm transition hover:bg-slate-800">
                Back to Solutions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        @php
            $steps = \App\Models\Steps::where('solution_id', $solution->id)->get() ?? '';
        @endphp

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="rounded-3xl border border-slate-800 bg-slate-900/85 p-6 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70">
                <p class="text-sm text-slate-400">Contributed by {{ $solution->user->name }}</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">{{ $solution->solution_title }}</h1>
                <p class="mt-4 text-sm leading-6 text-slate-300">{{ $solution->solution_description }}</p>

                <div class="mt-6 border-t border-slate-800 pt-6">
                    <livewire:comments :model="$solution" :solution="$solution" />
                </div>
            </article>

            <section class="space-y-4">
                @foreach($steps as $step)
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/85 p-5 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Step {{ $loop->iteration }}</p>
                        <p class="mt-2 text-base font-semibold text-white">{{ $step->solution_heading }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">{{ $step->solution_body }}</p>
                    </div>
                @endforeach
            </section>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
