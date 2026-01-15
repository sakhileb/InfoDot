<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Add Solution') }}
            </h2>
            <div>
                <a href="{{ route('seek') }}" class="justify-items-end btn rounded-full">
                    <i class="fa fa-search mr-1" aria-hidden="true"></i> Seek
                </a>
                <a href="{{ route('add') }}" class="justify-items-end btn rounded-full">
                    <i class="fa fa-plus mr-1" aria-hidden="true"></i> Add
                </a>
            </div>
        </div>
    </x-slot>
    <div class="flex">
        @include('partials.aside-left')

        <main class="w-full">
            <div class="col-span-2 px-4 overflow-y-scroll">
                <h1 class="text-gray-900 m-3 text-2xl">Solution Contribution</h1>
                <hr class="my-3 text-gray-900">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    <div class="form">
                        <form class="w-full" method="POST" action="{{ route('solutions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full md:w-full px-3 mb-6">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="solution_title">Solution Title:</label>
                                    <input class="appearance-none block w-full input input-bordered" name="solution_title" id="solution_title" type="text" placeholder="How to......?">
                                    @error('solution_title')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="w-full px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                        Solution Description:
                                    </label>
                                    <textarea class="textarea h-24 textarea-bordered w-full" name="solution_description" placeholder="This solution will help you accomplish 1..2..3..."></textarea>
                                    @error('solution_description')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full px-3">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-password">Tags: <span class="text-red-500">(Note: Do not remove the first tag)</span></label>
                                    <div class="appearance-none block w-full input input-bordered tags-input" data-name="tags"></div>
                                    @error('tags')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex flex-wrap -mx-3 mb-6">
                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-city">Duration:</label>
                                    <input class="appearance-none block w-full input input-bordered" name="duration" id="grid-city" type="number" placeholder="1">
                                    @error('duration')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">Duration Type:</label>
                                    <div class="relative">
                                        <select class="appearance-none block w-full input input-bordered" name="duration_type" id="grid-state">
                                            <option value="hours">Hours</option>
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                            <option value="years">Years</option>
                                            <option value="infinite">Unknown</option>
                                        </select>
                                        @error('duration_type')
                                            <div class="text-red-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">Estimated Steps:</label>
                                    <input class="appearance-none block w-full input input-bordered" name="steps" id="grid-zip" type="number" placeholder="12">
                                    @error('steps')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div id="steps">
                                <hr class="my-3 text-gray-900">
                                <h4 class="text-gray-900 m-3 text-2xl">Step 1:</h4>
                                <hr class="my-3 text-gray-900">
                                <div class="flex flex-wrap -mx-3 mb-6">
                                    <div class="w-full md:w-full px-3 mb-6">
                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="solution_title">Heading:</label>
                                        <input class="appearance-none block w-full input input-bordered" name="solution_heading[]" id="solution_heading" type="text" placeholder="How to......?">
                                    </div>
                                    <div class="w-full px-3">
                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                            Body:
                                        </label>
                                        <textarea class="textarea h-24 textarea-bordered w-full" name="solution_body[]" placeholder="This solution will help you accomplish 1..2..3..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-6 justify-end">
                                <input type="button" id="more_fields" class="btn m-3" onclick="add_steps();" value="Add Step">
                                <button class="btn m-3" type="submit">Done</button>
                            </div>
                        </form>
                    </div>
                    <div class="rules hidden sm:block">
                        <h1 class="text-gray-900 flex justify-center">Write A Winning Solution:</h1>
                        <hr class="my-3 text-gray-900">
                        <ul class="list-none text-gray-900 mx-5">
                            <li class="uppercase">
                                Your title should be short &amp; straight to the point
                            </li>
                            <li>
                                <span class="text-red-500">Bad Title:</span>
                                "How to start a business?"
                            </li>
                            <li>
                                <span class="text-green-500">Good Title:</span>
                                "How to register a business on the cipc website?" (Specific)
                            </li>
                            <li>
                                <hr class="my-3 text-gray-900">
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
                                <hr class="my-3 text-gray-900">
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
                                <hr class="my-3 text-gray-900">
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
                                <hr class="my-3 text-gray-900">
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
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
</x-app-layout>
