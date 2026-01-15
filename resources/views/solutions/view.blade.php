<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Read Solution') }}
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
            <div class="px-4 overflow-y-scroll">
                @php
                    $steps = \App\Models\Steps::where('solution_id', $solution->id)->get() ?? '';
                @endphp
                <h1 class="text-gray-900 m-3 text-2xl flex w-full justify-between">
                    <span class="justify-start">
                        Solution By {{ $solution->user->name }}
                    </span>
                    @if(Auth::id() == $solution->user->id)
                        <livewire:solution-crud :model="$solution" :solution="$solution"/>
                    @endif
                </h1>
                <hr class="my-3 text-gray-900">
                <div class="md:grid md:grid-cols-2 md:gap-2">
                    <div class="md:col-span-1 w-full">
                        <div class="px-4 sm:px-0">
                            <h3 class="text-lg m-3 font-medium text-gray-900 capitalize">
                                Title: {{ $solution->solution_title }}
                            </h3>
                            <p class="m-3 text-sm text-gray-600">
                                Description: {{ $solution->solution_description }}
                            </p>
                            <livewire:comments :model="$solution" :solution="$solution" />
                        </div>
                        <div class="px-4 sm:px-0"></div>
                    </div>
                    <div class="md:mt-0 md:col-span-1">
                        @foreach($steps as $step)
                            <div class="m-3 px-4 py-5 sm:p-6 bg-white shadow sm:rounded-lg">
                                <div class="max-w-xl text-sm text-gray-600">
                                    <h2 class="text-sm text-gray-600">Step {{ $loop->iteration }}</h2>
                                    <p class="font-semibold">{{ $step->solution_heading }}</p>
                                    {{ $step->solution_body }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
</x-app-layout>
