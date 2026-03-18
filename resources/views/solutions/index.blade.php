<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Solutions') }}
            </h2>
            <a href="{{ route('add') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold leading-5 text-white shadow-sm transition hover:bg-blue-500">
                <i class="fa fa-plus mr-2" aria-hidden="true"></i> Add Solution
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="space-y-5">
            @forelse ($solutions as $solution)
                <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" class="block rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-sm ring-1 ring-slate-800/70 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-slate-900/60 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold leading-7 text-white">{{ $solution->solution_title }}</h3>
                        <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">
                            {{ $solution->duration }} {{ $solution->duration_type }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        {{ \Illuminate\Support\Str::limit($solution->solution_description, 180) }}
                    </p>

                    <div class="mt-5 flex items-center gap-6 text-sm text-slate-400">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $solution->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $solution->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 p-12 text-center text-slate-400">
                    No solutions yet. Add your first business solution.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
