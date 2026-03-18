<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Ask a Question') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-100 shadow-sm transition hover:bg-slate-800">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-slate-800 bg-slate-900/85 p-6 shadow-[0_24px_60px_-24px_rgba(8,15,32,0.95)] ring-1 ring-slate-800/70">
            <h1 class="text-2xl font-semibold text-white">Ask a New Question</h1>
            <p class="mt-2 text-sm text-slate-400">Share your challenge clearly so the community can help effectively.</p>

            <div class="form mt-6 lg:w-full">
                        <form class="w-full" method="POST" action="{{ route('questions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full md:w-full px-3 mb-6">
                                    <label class="block uppercase tracking-wide text-slate-300 text-xs font-bold mb-2" for="question">Question:</label>
                                    <input class="block w-full rounded-2xl border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" name="question" id="question" type="text" placeholder="How to......?">
                                    @if($errors->has('question'))
                                        <div class="text-red-500">{{ $errors->first('question') }}</div>
                                    @endif
                                </div>
                                <div class="w-full px-3">
                                    <label for="description" class="block uppercase tracking-wide text-slate-300 text-xs font-bold mb-2">
                                        Description:
                                    </label>
                                    <textarea class="h-24 w-full rounded-2xl border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" name="description" placeholder="Describe the problem you are facing....."></textarea>
                                    @if($errors->has('description'))
                                        <div class="text-red-500">{{ $errors->first('description') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-6 justify-start">
                                <button class="rounded-xl bg-sky-500 px-5 py-2 text-sm font-semibold text-slate-950 shadow-[0_12px_30px_-12px_rgba(56,189,248,0.8)] transition hover:bg-sky-400" type="submit">Submit Question</button>
                            </div>
                        </form>
                    </div>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
