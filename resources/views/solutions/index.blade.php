<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Business Solutions') }}
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
    <div class="flex z-0">
        @include('partials.aside-left')

        <main class="bg-cover bg-base-200 w-full z-0">
            <div class="col-span-2 px-4 overflow-y-scroll">
                <livewire:solution-list />
            </div>
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
</x-app-layout>
