<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Questions') }}
            </h2>
            <a href="{{ route('seek') }}" class="inline-flex items-center rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-[0_12px_30px_-12px_rgba(56,189,248,0.8)] transition hover:bg-sky-400">
                <i class="fa fa-plus mr-2" aria-hidden="true"></i> Ask Question
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="space-y-4">
            @forelse ($questions as $question)
                @php
                    $isSolved = (int) $question->status === 1 || (int) $question->comments_count > 0;
                @endphp
                <a href="{{ route('questions.view', ['qid' => $question->id]) }}" class="block rounded-3xl border border-slate-800 bg-slate-900/80 p-5 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70 transition hover:-translate-y-0.5 hover:border-sky-500/40 hover:bg-slate-900 hover:shadow-[0_28px_70px_-24px_rgba(56,189,248,0.18)]">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold text-white">{{ $question->question }}</h3>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $isSolved ? 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/25' : 'bg-amber-500/15 text-amber-300 ring-1 ring-amber-400/25' }}">
                            {{ $isSolved ? 'Solved' : 'Unsolved' }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-300">
                        {{ \Illuminate\Support\Str::limit($question->description, 180) }}
                    </p>

                    <div class="mt-4 flex items-center gap-5 text-sm text-slate-400">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $question->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $question->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/70 p-10 text-center text-slate-400">
                    No questions yet. Start by asking one.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
