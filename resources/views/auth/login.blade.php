<x-guest-layout>
    <nav class="top-0 absolute z-50 w-full flex flex-wrap items-center justify-between px-2 py-3 navbar-expand-lg">
        @if (Route::has('login'))
            <div class="w-full px-4 flex flex-wrap items-center justify-between">
                <div class="w-full relative flex justify-between lg:w-auto lg:static lg:block lg:justify-start">
                    @auth
                        <a href="{{ url('/questions') }}" class="text-sm font-bold leading-relaxed inline-block mr-4 py-2 whitespace-no-wrap uppercase text-white">
                            InfoDot
                        </a>
                    @else
                        <button class="cursor-pointer text-xl leading-none px-3 py-1 border border-solid border-transparent  rounded bg-transparent block lg:hidden outline-none focus:outline-none" type="button" onclick="toggleNavbar('example-collapse-navbar')" >
                            <i class="text-white fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="lg:flex flex-grow items-center bg-white lg:bg-transparent lg:shadow-none hidden" id="example-collapse-navbar">
                        <ul class="flex flex-col lg:flex-row list-none lg:ml-auto">
                            <li class="flex items-center">
                              <a
                                class="lg:text-white lg:hover:text-gray-300 text-gray-800 px-3 py-4 lg:py-2 flex items-center text-xs uppercase font-bold"
                                href="https://web.facebook.com/infodotbusinesses"
                                target="_blank"
                                ><i
                                  class="lg:text-gray-300 text-gray-500 fab fa-facebook text-lg leading-lg "
                                ></i
                                ><span class="lg:hidden inline-block ml-2">Share</span></a
                              >
                            </li>
                            <li class="flex items-center">
                              <a
                                class="lg:text-white lg:hover:text-gray-300 text-gray-800 px-3 py-4 lg:py-2 flex items-center text-xs uppercase font-bold"
                                href="https://twitter.com/InfoDot3"
                                target="_blank"
                                ><i
                                  class="lg:text-gray-300 text-gray-500 fab fa-twitter text-lg leading-lg "
                                ></i
                                ><span class="lg:hidden inline-block ml-2">Tweet</span></a
                              >
                            </li>
                            <li class="flex items-center">
                              <a
                                class="lg:text-white lg:hover:text-gray-300 text-gray-800 px-3 py-4 lg:py-2 flex items-center text-xs uppercase font-bold"
                                href="https://www.instagram.com/infodot_businesses_official/"
                                target="_blank"
                                ><i
                                  class="lg:text-gray-300 text-gray-500 fab fa-instagram text-lg leading-lg "
                                ></i
                                ><span class="lg:hidden inline-block ml-2">Follow</span></a
                              >
                            </li>
                            <li class="flex items-center">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-white text-gray-800 active:bg-gray-100 text-xs font-bold uppercase px-4 py-2 rounded shadow hover:shadow-md outline-none focus:outline-none lg:mr-1 lg:mb-0 ml-3 mb-3" type="button" style="transition: all 0.15s ease 0s;">Sign up
                                    </a>
                                @endif
                                @endauth
                            </li>
                        </ul>
                    </div>
                </div>
        @endif
    </nav>
    <main >
        <x-jet-authentication-card>
            <x-slot name="logo">
                <x-jet-authentication-card-logo />
            </x-slot>
            <div class="text-gray-500 text-center mb-3 font-bold">
                <small>Sign in with credentials</small>
            </div>
            <x-jet-validation-errors class="mb-4" />

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="relative w-full mb-3">
                    <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="email" value="{{ __('Email') }}" />
                    <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="email" type="email" name="email" :value="old('email')" required autofocus />
                </div>

                <div class="relative w-full mb-3">
                    <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="password" value="{{ __('Password') }}" />
                    <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="password" type="password" name="password" required autocomplete="current-password" />
                </div>

                <div>
                    <label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="remember_me" class="flex items-center">
                        <x-jet-checkbox id="remember_me" name="remember" />
                        <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="text-center mt-6">
                    <x-jet-button class="text-center bg-gray-900 text-white active:bg-gray-700 text-sm font-bold uppercase px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 w-full">
                        {{ __('Sign in') }}
                    </x-jet-button>
                </div>
            </form>
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </x-jet-authentication-card>
        <footer class="block w-full bottom-0 bg-gray-900 pb-6">
            <div class="mx-auto px-4">
                <hr class="mb-6 border-b-1 border-gray-700" />
                <div class="flex flex-wrap items-center md:justify-between justify-center">
                    <div class="w-full md:w-4/12 px-4">
                        <div class="text-sm text-white font-semibold py-1">
                            Copyright © 2019
                            <a href="https://www.creative-tim.com" class="text-white hover:text-gray-400 text-sm font-semibold py-1">InfoDot.</a>
                        </div>
                    </div>
                    <div class="w-full md:w-8/12 px-4">
                        <ul class="flex flex-wrap list-none md:justify-end  justify-center">
                            <li>
                                <a href="{{ url('/') }}" class="text-white hover:text-gray-400 text-sm font-semibold block py-1 px-3">Home</a>
                            </li>
                            <li>
                                <a href="https://www.creative-tim.com/presentation" class="text-white hover:text-gray-400 text-sm font-semibold block py-1 px-3">About Us</a>
                            </li>
                            <li>
                                <a href="{{ route('register') }}" class="text-white hover:text-gray-400 text-sm font-semibold block py-1 px-3">Sign Up</a>
                            </li>
                            <li>
                                <a href="https://github.com/creativetimofficial/argon-design-system/blob/master/LICENSE.md"
                              class="text-white hover:text-gray-400 text-sm font-semibold block py-1 px-3">License</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </main>
</x-guest-layout>
