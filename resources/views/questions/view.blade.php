<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">
                {{ __('Question Details') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-full border border-[#434656]/40 bg-[#131b2e] px-5 py-2.5 text-sm font-semibold leading-5 text-[#dae2fd] shadow-sm transition hover:bg-[#1a2438] hover:text-[#b6c4ff]">
                &larr; Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <article class="rounded-3xl border border-[#434656]/35 bg-[rgba(49,57,77,0.55)] p-7 shadow-[0_20px_44px_rgba(0,0,0,0.35)]" style="backdrop-filter:blur(20px);">
            <p class="text-sm leading-6 text-[#8d90a2]">Asked by {{ $question->user->name }}</p>
            <h1 class="mt-3 text-2xl font-bold leading-9 text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">{{ $question->question }}</h1>
            <p class="mt-5 text-sm leading-7 text-[#c3c5d8]">{{ $question->description }}</p>

            <div class="mt-7 border-t border-[#434656]/30 pt-7">
                <livewire:comments :model="$question" :question="$question"/>
            </div>
        </article>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
