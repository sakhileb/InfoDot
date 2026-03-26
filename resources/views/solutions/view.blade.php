<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">
                {{ __('Solution Details') }}
            </h2>
            <a href="{{ route('solutions') }}" class="inline-flex items-center rounded-full border border-[#434656]/40 bg-[#131b2e] px-5 py-2.5 text-sm font-semibold leading-5 text-[#dae2fd] shadow-sm transition hover:bg-[#1a2438] hover:text-[#b6c4ff]">
                &larr; Back to Solutions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        @php
            $steps = \App\Models\Steps::where('solution_id', $solution->id)->get() ?? '';
        @endphp

        <div class="grid gap-7 lg:grid-cols-2">
            <article class="rounded-3xl border border-[#434656]/35 bg-[rgba(49,57,77,0.55)] p-7 shadow-[0_20px_44px_rgba(0,0,0,0.35)]" style="backdrop-filter:blur(20px);">
                <p class="text-sm leading-6 text-[#8d90a2]">Contributed by {{ $solution->user->name }}</p>
                <h1 class="mt-3 text-2xl font-bold leading-9 text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">{{ $solution->solution_title }}</h1>
                <p class="mt-5 text-sm leading-7 text-[#c3c5d8]">{{ $solution->solution_description }}</p>

                <div class="mt-7 border-t border-[#434656]/30 pt-7">
                    <livewire:comments :model="$solution" :solution="$solution" />
                </div>
            </article>

            <section class="space-y-5">
                @foreach($steps as $step)
                    <div class="rounded-3xl border border-[#434656]/35 bg-[rgba(49,57,77,0.55)] p-6 shadow-[0_12px_32px_rgba(0,0,0,0.28)]" style="backdrop-filter:blur(20px);">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#b6c4ff]">Step {{ $loop->iteration }}</p>
                        <p class="mt-2.5 text-base font-semibold leading-7 text-[#dae2fd]">{{ $step->solution_heading }}</p>
                        <p class="mt-3 text-sm leading-7 text-[#c3c5d8]">{{ $step->solution_body }}</p>
                    </div>
                @endforeach
            </section>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
