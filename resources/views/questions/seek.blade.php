<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Ask a Question') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold leading-5 text-slate-100 shadow-sm transition hover:bg-slate-900/60">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-sm ring-1 ring-slate-800/70">
            <h1 class="text-2xl font-semibold leading-9 text-white">Ask a New Question</h1>
            <p class="mt-3 text-sm leading-7 text-slate-400">Share your challenge clearly so the community can help effectively.</p>

            <div class="form mt-7 lg:w-full">
                        <form class="w-full space-y-1" method="POST" action="{{ route('questions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="mb-7 flex flex-wrap -mx-3 gap-y-6">
                                <div class="mb-1 w-full px-3 md:w-full">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="question">Question:</label>
                                    <input class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="question" id="question" type="text" placeholder="How to......?">
                                    @if($errors->has('question'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('question') }}</div>
                                    @endif
                                </div>
                                <div class="mb-1 w-full px-3">
                                    <label for="description" class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300">
                                        Description:
                                    </label>
                                    <textarea class="h-28 w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="description" placeholder="Describe the problem you are facing....."></textarea>
                                    @if($errors->has('description'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('description') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-2 justify-start">
                                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold leading-5 text-white shadow-sm transition hover:bg-blue-500" type="submit">Submit Question</button>
                            </div>
                        </form>
                    </div>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
