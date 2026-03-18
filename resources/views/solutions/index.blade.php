<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Solutions') }}
            </h2>
            <a href="{{ route('add') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                <i class="fa fa-plus mr-2" aria-hidden="true"></i> Add Solution
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="space-y-4">
            @forelse ($solutions as $solution)
                <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $solution->solution_title }}</h3>
                        <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $solution->duration }} {{ $solution->duration_type }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ \Illuminate\Support\Str::limit($solution->solution_description, 180) }}
                    </p>

                    <div class="mt-4 flex items-center gap-5 text-sm text-slate-500">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $solution->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $solution->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    No solutions yet. Add your first business solution.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
