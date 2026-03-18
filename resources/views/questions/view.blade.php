<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Question Details') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Asked by {{ $question->user->name }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">{{ $question->question }}</h1>
            <p class="mt-4 text-sm leading-6 text-slate-700">{{ $question->description }}</p>

            <div class="mt-6 border-t border-slate-200 pt-6">
                <livewire:comments :model="$question" :question="$question"/>
            </div>
        </article>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
