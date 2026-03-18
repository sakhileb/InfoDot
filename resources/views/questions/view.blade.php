<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Question Details') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm transition hover:bg-slate-800">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <article class="rounded-3xl border border-slate-800 bg-slate-900/85 p-6 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70">
            <p class="text-sm text-slate-400">Asked by {{ $question->user->name }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-white">{{ $question->question }}</h1>
            <p class="mt-4 text-sm leading-6 text-slate-300">{{ $question->description }}</p>

            <div class="mt-6 border-t border-slate-800 pt-6">
                <livewire:comments :model="$question" :question="$question"/>
            </div>
        </article>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
