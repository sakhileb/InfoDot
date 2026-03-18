<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">
                {{ __('Add Solution') }}
            </h2>
            <a href="{{ route('solutions') }}" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold leading-5 text-slate-100 shadow-sm transition hover:bg-slate-900/60">
                Back to Solutions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-sm ring-1 ring-slate-800/70 lg:p-8">
            <h1 class="text-2xl font-semibold leading-9 text-white">Solution Contribution</h1>
            <p class="mt-3 text-sm leading-7 text-slate-400">Create a practical, step-by-step solution the community can apply immediately.</p>

            <div class="mt-7 grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-2">
                    <div class="form">
                        <form class="w-full space-y-1" method="POST" action="{{ route('solutions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="mb-7 flex flex-wrap -mx-3 gap-y-6">
                                <div class="mb-1 w-full px-3 md:w-full">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="solution_title">Solution Title:</label>
                                    <input class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="solution_title" id="solution_title" type="text" placeholder="How to......?">
                                    @if($errors->has('solution_title'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('solution_title') }}</div>
                                    @endif
                                </div>
                                <div class="mb-1 w-full px-3">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300">
                                        Solution Description:
                                    </label>
                                    <textarea class="h-28 w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="solution_description" placeholder="This solution will help you accomplish 1..2..3..."></textarea>
                                    @if($errors->has('solution_description'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('solution_description') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-7 flex flex-wrap -mx-3">
                                <div class="w-full px-3">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="grid-password">Tags: <span class="text-red-400">(Note: Do not remove the first tag)</span></label>
                                    <div class="tags-input block w-full appearance-none rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100" data-name="tags-input"></div>
                                    @if($errors->has('tags'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('tags') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-7 flex flex-wrap -mx-3 gap-y-6">
                                <div class="mb-1 w-full px-3 md:mb-0 md:w-1/3">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="grid-city">Duration:</label>
                                    <input class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="duration" id="grid-city" type="number" placeholder="1">
                                    @if($errors->has('duration'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('duration') }}</div>
                                    @endif
                                </div>
                                <div class="mb-1 w-full px-3 md:mb-0 md:w-1/3">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="grid-state">Duration Type:</label>
                                    <div class="relative">
                                        <select class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="duration_type" id="grid-state">
                                            <option value="hours">Hours</option>
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                            <option value="infinite">Unknown</option>
                                        </select>
                                        @if($errors->has('duration_type'))
                                            <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('duration_type') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-1 w-full px-3 md:mb-0 md:w-1/3">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="grid-zip">Estimated Steps:</label>
                                    <input class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="steps" id="grid-zip" type="number" placeholder="12">
                                    @if($errors->has('steps'))
                                        <div class="mt-2 text-xs leading-5 text-red-500">{{ $errors->first('steps') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div id="steps">
                                <hr class="my-4 border-slate-800">
                                    <h4 class="m-3 text-2xl leading-9 text-white">Step 1:</h4>
                                <hr class="my-4 border-slate-800">
                                <div class="mb-7 flex flex-wrap -mx-3 gap-y-6">
                                    <div class="mb-1 w-full px-3 md:w-full">
                                        <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300" for="solution_title">Heading:</label>
                                        <input class="block w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="solution_heading[]" id="solution_heading" type="text" placeholder="How to......?">
                                    </div>
                                    <div class="mb-1 w-full px-3">
                                        <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-slate-300">
                                            Body:
                                        </label>
                                        <textarea class="h-28 w-full rounded-2xl border border-slate-700 bg-slate-900 px-4 py-3.5 text-sm leading-6 text-slate-100 placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" name="solution_body[]" placeholder="This solution will help you accomplish 1..2..3..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 flex flex-wrap -mx-3 justify-end gap-2 px-3">
                                <input type="button" id="more_fields" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold leading-5 text-slate-100 shadow-sm transition hover:bg-slate-900/60" onclick="add_steps();" value="Add Step">
                                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold leading-5 text-white shadow-sm transition hover:bg-blue-500" type="submit">Done</button>
                            </div>
                        </form>
                    </div>
                    <div class="rules hidden sm:block">
                        <h1 class="flex justify-center text-xl font-semibold leading-8 text-white">Write A Winning Solution:</h1>
                        <hr class="my-4 border-slate-800">
                        <ul class="mx-5 list-none space-y-2 text-sm leading-7 text-slate-300">
                            <li class="uppercase">
                                Your title should be short &amp; straight to the point
                            </li>
                            <li>
                                <span class="text-red-500">Bad Title:</span>
                                "How to start a business?"
                            </li>
                            <li>
                                <span class="text-green-500">Good Title:</span>
                                "How to register a business on the cipc website?" (Specific)</li>
                            <li>
                                <hr class="my-4 border-slate-800">
                            </li>
                            <li class="uppercase">
                                Your description should outline the outcome of the solution
                            </li>
                            <li>
                                <span class="text-red-500">Bad Description:</span>
                                "This solution will show you how to start a business and run it like a professional...."
                            </li>
                            <li>
                                <span class="text-green-500">Good Description:</span>
                                "This solution will inform you step by step on how to get a business registered on the CIPC website and advice you on which documents are required to accomplish this task." (Specific)
                            </li>
                            <li>
                                <hr class="my-4 border-slate-800">
                            </li>
                            <li class="uppercase">
                                Your tags should make is easy for people to find your solution
                            </li>
                            <li>
                                <span class="text-red-500">Bad Tags:</span>
                                "Business, Finance, Accounting...."
                            </li>
                            <li>
                                <span class="text-green-500">Good Tags:</span>
                                "Busines registration, How to, Business plan..." (Specific)
                            </li>
                            <li>
                                <hr class="my-4 border-slate-800">
                            </li>
                            <li class="uppercase">
                                Your duration should be realistic
                            </li>
                            <li>
                                <span class="text-red-500">Bad Duration:</span>
                                "12 months" - to register a business....
                            </li>
                            <li>
                                <span class="text-green-500">Good Duration:</span>
                                "1 week" - to register a business (Realistic)
                            </li>
                            <li>
                                <hr class="my-4 border-slate-800">
                            </li>
                            <li class="uppercase">
                                Your Steps should lead on to each other leaving no step in between
                            </li>
                            <li>
                                <span class="text-green-500">Short &amp; Descriptive:</span>
                                Try to keep your steps brief but fully understandable.
                            </li>
                        </ul>
                    </div>
            </div>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
    @push('js')

    </script>
@endpush
</x-app-layout>
