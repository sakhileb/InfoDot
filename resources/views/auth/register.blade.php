<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        <x-jet-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="name" value="{{ __('Name') }}" />
                <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="email" value="{{ __('Email') }}" />
                <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="email" type="email" name="email" :value="old('email')" required />
            </div>

            <div class="mt-4">
                <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="password" value="{{ __('Password') }}" />
                <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="password" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-jet-label class="block uppercase text-gray-700 text-xs font-bold mb-2" for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-jet-input class="px-3 py-3 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline w-full" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-jet-label for="terms">
                        <div class="flex items-center">
                            <x-jet-checkbox name="terms" id="terms"/>

                            <div class="ml-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-jet-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <x-jet-button  class="text-center bg-gray-900 text-white active:bg-gray-700 text-sm font-bold uppercase px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 w-full">
                    {{ __('Sign Up') }}
                </x-jet-button>
            </div>
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
        </form>
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
                                <a href="{{ route('login') }}" class="text-white hover:text-gray-400 text-sm font-semibold block py-1 px-3">Sign In</a>
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
</x-guest-layout>
