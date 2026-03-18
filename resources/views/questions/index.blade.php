<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Questions') }}
            </h2>
            <a href="{{ route('seek') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold leading-5 text-white shadow-sm transition hover:bg-blue-500">
                <i class="fa fa-plus mr-2" aria-hidden="true"></i> Ask Question
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="space-y-5">
            @forelse ($questions as $question)
                @php
                    $isSolved = (int) $question->status === 1 || (int) $question->comments_count > 0;
                @endphp
                <a href="{{ route('questions.view', ['qid' => $question->id]) }}" class="block rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-sm ring-1 ring-slate-800/70 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-slate-900/60 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold leading-7 text-white">{{ $question->question }}</h3>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $isSolved ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' }}">
                            {{ $isSolved ? 'Solved' : 'Unsolved' }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        {{ \Illuminate\Support\Str::limit($question->description, 180) }}
                    </p>

                    <div class="mt-5 flex items-center gap-6 text-sm text-slate-400">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $question->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $question->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 p-12 text-center text-slate-400">
                    No questions yet. Start by asking one.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
