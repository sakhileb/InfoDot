<x-guest-layout>
    <div class="pt-4 bg-gray-800">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-jet-authentication-card-logo />
            </div>

            <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose">
                <h1 class="title text-center">Complains</h1>
            </div>
        </div>
    </div>
    @include('layouts.footer')
</x-guest-layout>
