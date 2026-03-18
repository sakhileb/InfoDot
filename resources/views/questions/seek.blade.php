<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Ask a Question') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">Ask a New Question</h1>
            <p class="mt-2 text-sm text-slate-600">Share your challenge clearly so the community can help effectively.</p>

            <div class="form mt-6 lg:w-full">
                        <form class="w-full" method="POST" action="{{ route('questions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full md:w-full px-3 mb-6">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="question">Question:</label>
                                    <input class="appearance-none block w-full input input-bordered" name="question" id="question" type="text" placeholder="How to......?">
                                    @if($errors->has('question'))
                                        <div class="text-red-500">{{ $errors->first('question') }}</div>
                                    @endif
                                </div>
                                <div class="w-full px-3">
                                    <label for="description" class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                        Description:
                                    </label>
                                    <textarea class="textarea h-24 textarea-bordered w-full" name="description" placeholder="Describe the problem you are facing....."></textarea>
                                    @if($errors->has('description'))
                                        <div class="text-red-500">{{ $errors->first('description') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-6 justify-start">
                                <button class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" type="submit">Submit Question</button>
                            </div>
                        </form>
                    </div>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
