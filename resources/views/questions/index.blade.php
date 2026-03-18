<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Questions') }}
            </h2>
            <a href="{{ route('seek') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
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
                <a href="{{ route('questions.view', ['qid' => $question->id]) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-semibold text-slate-900">{{ $question->question }}</h3>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $isSolved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $isSolved ? 'Solved' : 'Unsolved' }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ \Illuminate\Support\Str::limit($question->description, 180) }}
                    </p>

                    <div class="mt-4 flex items-center gap-5 text-sm text-slate-500">
                        <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $question->likes_count }} likes</span>
                        <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $question->comments_count }} comments</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    No questions yet. Start by asking one.
                </div>
            @endforelse
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
