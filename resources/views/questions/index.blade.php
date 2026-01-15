<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Questions Asked') }}
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

        <main class="bg-cover bg-base-200 w-full">
            <livewire:question-list />
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
</x-app-layout>
