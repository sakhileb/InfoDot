<x-guest-layout>
    <div class="relative pt-16 pb-32 flex content-center items-center justify-center" style="max-height: 300px;">
        <div class="absolute top-0 w-full h-full bg-center bg-cover" style='background-image: url("{{ asset('img/background.jpg') }}");'>
            <span id="blackOverlay" class="w-full h-full absolute opacity-50 bg-black"></span>
        </div>
        <div class="top-auto bottom-0 left-0 right-0 w-full absolute pointer-events-none overflow-hidden" style="height: 70px; transform: translateZ(0px);">
            <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" version="1.1" viewBox="0 0 2560 100" x="0" y="0">
                <polygon class="text-gray-300 fill-current" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </div>
    
    <section class="relative block py-24 lg:pt-0 bg-gray-900">
        <div class="container mx-auto px-4 mt-32">
            <div class="flex flex-wrap justify-center lg:-mt-64 -mt-48">
                <div class="w-full lg:w-6/12 px-4">
                    <h4 class="text-2xl font-semibold text-gray-300 text-center mt-16">Want to work with us?</h4>
                    <p class="leading-relaxed mt-1 mb-4 text-gray-600 text-center">
                        Complete this form and we will get back to you in 24 hours.
                    </p>
                    
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success w-full bg-green-500 p-5">
                            <p class="lead text-green-800 text-center">Message Sent!</p>
                        </div>
                    @endif
                    
                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger w-full bg-red-500 p-5">
                            <p class="lead text-red-800 text-center">{{ $message }}</p>
                        </div>
                    @endif
                    
                    <div class="relative flex flex-col min-w-0 break-words w-full mb-6 shadow-lg rounded-lg bg-gray-300 mt-16">
                        <div class="flex-auto p-5 lg:p-10">
                            <h4 class="text-2xl font-semibold text-grey-300">Contact Us</h4>
                            <form action="{{ route('send-contact') }}" method="POST">
                                @csrf
                                <div class="relative w-full mb-3 mt-8">
                                    <label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="full-name">
                                        {{ __('Full Name') }}
                                    </label>
                                    <input type="text" class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" placeholder="Full Name" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus/>
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="relative w-full mb-3">
                                    <label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="email">
                                        {{ __('Email Address') }}
                                    </label>
                                    <input type="email" class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" placeholder="Email" name="email" value="{{ old('email') }}" required autocomplete="email"/>
                                    @error('email')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="relative w-full mb-3">
                                    <label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="message">
                                        {{ __('Message') }}
                                    </label>
                                    <textarea rows="4" name="message" cols="80" class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" placeholder="Type a message..."></textarea>
                                    @error('message')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="text-center mt-6">
                                    <button class="bg-gray-900 text-white active:bg-gray-700 text-sm font-bold uppercase px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1" type="submit">
                                        Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    @include('layouts.footer')
</x-guest-layout>
