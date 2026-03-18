<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Solutions') }}
            </h2>
            <a href="{{ route('add') }}" class="inline-flex items-center rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-[0_12px_30px_-12px_rgba(56,189,248,0.8)] transition hover:bg-sky-400">
                <i class="fa fa-plus mr-2" aria-hidden="true"></i> Add Solution
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="space-y-4">
            @forelse ($solutions as $solution)
                <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" class="block rounded-3xl border border-slate-800 bg-slate-900/80 p-5 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70 transition hover:-translate-y-0.5 hover:border-sky-500/40 hover:bg-slate-900 hover:shadow-[0_28px_70px_-24px_rgba(56,189,248,0.18)]">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">{{ $solution->solution_title }}</h3>
                        <span class="shrink-0 rounded-full bg-sky-500/15 px-3 py-1 text-xs font-semibold text-sky-300 ring-1 ring-sky-400/25">
                            {{ $solution->duration }} {{ $solution->duration_type }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ \Illuminate\Support\Str::limit($solution->solution_description, 180) }}
                    </p>

                    <div class="mt-4 flex items-center gap-5 text-sm text-slate-400">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $solution->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $solution->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/70 p-10 text-center text-slate-400">
                    No solutions yet. Add your first business solution.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
