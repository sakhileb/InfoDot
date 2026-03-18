<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Question Details') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold leading-5 text-slate-100 shadow-sm transition hover:bg-slate-900/60">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <article class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-sm ring-1 ring-slate-800/70">
            <p class="text-sm leading-6 text-slate-400">Asked by {{ $question->user->name }}</p>
            <h1 class="mt-3 text-2xl font-semibold leading-9 text-white">{{ $question->question }}</h1>
            <p class="mt-5 text-sm leading-7 text-slate-300">{{ $question->description }}</p>

            <div class="mt-7 border-t border-slate-800 pt-7">
                <livewire:comments :model="$question" :question="$question"/>
            </div>
        </article>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
