<x-guest-layout>
    <div class="pt-4 bg-gray-800">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white mx-10 shadow-md overflow-hidden sm:rounded-lg prose text-center">
                <h1 class="title text-center text-bold">Version 1.0.0</h1>
                <br>
                <p class="lead text-center">InfoDot is an open community. It was built to empower entrepreneurs & people who are in business. We connect them to strategies, solutions & service providers that enable problem solving, productivity, growth, and discovery. We help you get answers to your toughest business questions, By providing & organizing the various approaches to business & making them universally accessible to you.</p>
                <br>
                <a href="https://infodot.co.za">
                    <i class="fa fa-home fa-large text-blue-500"></i>
                </a>
            </div>
        </div>
    </div>
    @include('layouts.footer')
</x-guest-layout>
