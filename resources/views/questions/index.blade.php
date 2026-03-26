<x-app-layout>
    <x-slot name="header">
        <div class="w-full">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Questions') }}
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[#0b1326] text-[#dae2fd]">
        <style>
            .questions-shell {
                background-color: #0b1326;
                color: #dae2fd;
                font-family: 'Inter', sans-serif;
            }
            .questions-shell .font-headline {
                font-family: 'Manrope', sans-serif;
            }
            .questions-glass {
                background: rgba(49, 57, 77, 0.6);
                backdrop-filter: blur(20px);
            }
        </style>

        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />

        <main class="questions-shell px-4 pb-12 pt-8 sm:px-6 lg:px-10">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mb-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="font-headline text-4xl font-extrabold tracking-tight text-[#dae2fd]">Questions</h2>
                        <p class="mt-2 font-medium tracking-tight text-[#b7c8e1] opacity-80">Clarify blockers, get guided answers, and keep progress moving.</p>
                    </div>
                    <a href="{{ route('seek') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-[#2962ff] to-[#004ee8] px-7 py-3 font-headline text-sm font-bold tracking-tight text-[#f7f5ff] shadow-[0_8px_24px_rgba(41,98,255,0.25)] transition hover:shadow-[0_12px_32px_rgba(41,98,255,0.4)]">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        Ask Question
                    </a>
                </div>

                @forelse ($questions as $question)
                    @php
                        $isSolved = (int) $question->status === 1 || (int) $question->comments_count > 0;
                    @endphp
                    <a href="{{ route('questions.view', ['qid' => $question->id]) }}" class="questions-glass mb-6 block rounded-3xl border border-[#434656]/40 p-6 shadow-[0_20px_44px_rgba(0,0,0,0.32)] transition hover:-translate-y-0.5 hover:border-[#8d90a2]/60">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="font-headline text-xl font-bold tracking-tight text-[#dae2fd]">{{ $question->question }}</h3>
                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $isSolved ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' }}">
                                {{ $isSolved ? 'Solved' : 'Unsolved' }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm leading-7 text-[#c3c5d8]">
                            {{ \Illuminate\Support\Str::limit($question->description, 180) }}
                        </p>

                        <div class="mt-5 flex flex-wrap items-center gap-6 text-sm text-[#b7c8e1] opacity-90">
                            <span><i class="fa fa-heart mr-1" aria-hidden="true"></i>{{ $question->likes_count }} likes</span>
                            <span><i class="fa fa-comment mr-1" aria-hidden="true"></i>{{ $question->comments_count }} comments</span>
                        </div>
                    </a>
                @empty
                    <section class="relative flex min-h-[420px] flex-col items-center justify-center">
                        <div class="pointer-events-none absolute left-1/2 top-1/2 h-[440px] w-[440px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#b6c4ff]/10 blur-[120px]"></div>
                        <div class="questions-glass relative w-full max-w-2xl rounded-[2rem] border border-[#434656]/30 p-12 text-center shadow-[0_32px_64px_rgba(0,0,0,0.4)]">
                            <h3 class="font-headline text-2xl font-bold tracking-tight text-[#dae2fd]">No questions yet.</h3>
                            <p class="mx-auto mt-4 max-w-sm text-[#b7c8e1]">Start your first thread and let the workspace guide you to faster solutions.</p>
                            <a href="{{ route('seek') }}" class="mt-8 inline-flex items-center rounded-full bg-[#2962ff] px-8 py-3 text-sm font-semibold tracking-wide text-[#f7f5ff] transition hover:bg-[#004ee8]">Create First Question</a>
                        </div>
                    </section>
                @endforelse
            </div>
        </main>
    </div>

    @include('layouts.footer')
</x-app-layout>
