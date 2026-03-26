<x-app-layout>
    <x-slot name="header">
        <div class="w-full">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Solutions') }}
            </h2>
        </div>
    </x-slot>

    <main class="px-4 pb-12 pt-8 sm:px-6 lg:px-10" style="font-family:'Inter',sans-serif;">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-4xl font-extrabold tracking-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">Solutions</h2>
                    <p class="mt-2 font-medium tracking-tight text-[#b7c8e1] opacity-80">Architecting your business logic with precision components.</p>
                </div>
                <a href="{{ route('add') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#2962ff] to-[#004ee8] px-7 py-3 text-sm font-bold tracking-tight text-[#f7f5ff] shadow-[0_8px_24px_rgba(41,98,255,0.25)] transition hover:shadow-[0_12px_32px_rgba(41,98,255,0.4)]" style="font-family:'Manrope',sans-serif;">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    Add Solution
                </a>
            </div>

            @forelse ($solutions as $solution)
                <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" class="mb-6 block rounded-3xl border border-[#434656]/40 p-6 shadow-[0_20px_44px_rgba(0,0,0,0.32)] transition hover:-translate-y-0.5 hover:border-[#8d90a2]/60" style="background:rgba(49,57,77,0.6);backdrop-filter:blur(20px);">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-xl font-bold tracking-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">{{ $solution->solution_title }}</h3>
                        <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">
                            {{ $solution->duration }} {{ $solution->duration_type }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[#c3c5d8]">
                        {{ \Illuminate\Support\Str::limit($solution->solution_description, 180) }}
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-6 text-sm text-[#b7c8e1] opacity-90">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $solution->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $solution->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <section class="relative flex min-h-[500px] flex-col items-center justify-center">
                    <div class="pointer-events-none absolute left-1/2 top-1/2 h-[560px] w-[560px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#b6c4ff]/10 blur-[120px]"></div>
                    <div class="relative w-full max-w-2xl rounded-[2rem] border border-[#434656]/30 p-12 text-center shadow-[0_32px_64px_rgba(0,0,0,0.4)]" style="background:rgba(49,57,77,0.6);backdrop-filter:blur(20px);">
                        <h3 class="text-2xl font-bold tracking-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">No solutions yet.</h3>
                        <p class="mx-auto mt-4 max-w-sm text-[#b7c8e1]">Add your first business solution to begin optimizing your workspace workflows and automation sequences.</p>
                        <a href="{{ route('add') }}" class="mt-8 inline-flex items-center rounded-full bg-[#2962ff] px-8 py-3 text-sm font-semibold tracking-wide text-[#f7f5ff] transition hover:bg-[#004ee8]">Create from Scratch</a>
                    </div>
                </section>
            @endforelse
        </div>
    </main>

    @include('layouts.footer')
</x-app-layout>
