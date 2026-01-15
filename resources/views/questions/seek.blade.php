<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Questions') }}
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
                <h1 class="text-gray-900 m-3 text-2xl">Ask A Question</h1>
                <hr class="my-3 text-gray-900">
                <div class="form lg:w-full">
                    <form class="w-full" method="POST" action="{{ route('questions.add') }}" onkeydown="return event.key != 'Enter';">
                        @csrf
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-full px-3 mb-6">
                                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="question">Question:</label>
                                <input class="appearance-none block w-full input input-bordered" name="question" id="question" type="text" placeholder="How to......?">
                                @error('question')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full px-3">
                                <label for="description" class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                                    Description:
                                </label>
                                <textarea class="textarea h-24 textarea-bordered w-full" name="description" placeholder="Describe the problem you are facing....."></textarea>
                                @error('description')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap -mx-3 mb-6 justify-start">
                            <button class="btn m-3" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
</x-app-layout>
