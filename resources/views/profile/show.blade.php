<x-app-layout>
<main class="profile-page">
    <section class="relative block" style="height: 350px;">
        <div class="absolute top-0 w-full h-full bg-center bg-cover" style='background-image: url("https://source.unsplash.com/random/?productivity,business?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=crop&amp;w=2710&amp;q=80");'>
            <span id="blackOverlay" class="w-full h-full absolute opacity-50 bg-black"></span>
        </div>
        <div class="top-auto bottom-0 left-0 right-0 w-full absolute pointer-events-none overflow-hidden" style="height: 70px;">
            <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" version="1.1" viewBox="0 0 2560 100" x="0" y="0" >
                <polygon class="text-gray-300 fill-current" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </section>
    <section class="relative py-16 bg-gray-300">
        <div class="container mx-auto px-4">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-xl rounded-lg -mt-64">
                <div class="px-6">
                    <div class="flex flex-wrap justify-center">
                        <div class="w-full lg:w-3/12 px-4 lg:order-2 flex justify-center text-center mx-auto">
                            <div class="relative">
                                <img alt="profile-photo" src="{{ (!$user->profile_photo_url) ? $user->avatar() : $user->profile_photo_url }}" class="shadow-xl rounded-full h-auto align-middle border-none absolute -m-10 lg:-ml-16" style="max-width: 100px; min-width: 100px;"/>
                            </div>
                        </div>
                        <div class="w-full lg:w-4/12 px-4 lg:order-3 lg:text-right lg:self-center">
                            <div class="py-6 px-3 mt-32 sm:mt-0 text-center lg:text-right">
                                @if(Auth::id() !== $user->id)

                                    <livewire:associates :model="$user" :user="$user" :key="$user->id">
                                @else
                                    <a href="{{ route('profile.edit') }}" class="justify-items-end btn rounded-full uppercase text-white font-bold hover:shadow-md shadow text-xs px-4 py-2 rounded outline-none focus:outline-none sm:mr-2 mb-1" style="transition: all 0.15s ease 0s;">
                                        Edit Profile
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="w-full lg:w-4/12 px-4 lg:order-1">
                            <ul class="flex justify-center py-4 lg:pt-4 pt-8" id="tabs-id">
                                <li class="mr-4 p-3 text-center">
                                    <span class="text-xl font-bold block uppercase tracking-wide text-gray-700">
                                        {{ $user->associates->count() }}

                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Associates
                                    </span>
                                </li>
                                <li class="mr-4 p-3 text-center cursor-pointer">
                                    <a onclick="changeAtiveTab(event,'questions')">
                                        <span class="text-xl font-bold block uppercase tracking-wide text-gray-700">
                                            {{ $user->questions->count() }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            Questions
                                        </span>
                                    </a>
                                </li>
                                <li class="lg:mr-4 p-3 text-center cursor-pointer">
                                    <a onclick="changeAtiveTab(event,'solutions')">
                                        <span class="text-xl font-bold block uppercase tracking-wide text-gray-700">
                                            {{ $user->solutions->count() }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            Solutions
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="text-center mt-12">
                        <h3 class="text-4xl font-semibold leading-normal mb-2 text-gray-800 mb-2">
                            {{ $user->name }}
                        </h3>
                    <div class="text-sm leading-normal mt-0 mb-2 text-gray-500 font-bold uppercase">
                        <i class="fas fa-map-marker-alt mr-2 text-lg text-gray-500"></i>
                        Los Angeles, California
                    </div>
                    <div class="mb-2 text-gray-700 mt-10">
                        <i class="fas fa-briefcase mr-2 text-lg text-gray-500"></i>
                        Solution Manager - Creative Tim Officer
                    </div>
                    <div class="mb-2 text-gray-700">
                        <i class="fas fa-university mr-2 text-lg text-gray-500"></i>
                        University of Computer Science
                    </div>
                    </div>
                    <div class="mt-10 py-10 border-t border-gray-300 text-center">
                        <div class="flex flex-wrap justify-center">
                            <div class="w-full lg:w-9/12 px-4">
                                <div class="flex flex-wrap">
                                    <div class="w-full">
                                        <div class="tab-content tab-space" id="content-tabs-id">
                                            <div class="block" id="questions">
                                                <div class="mx-2 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
                                                    @if(!empty($user->questions))
                                                        @foreach($user->questions as $question)
                                                            <div aria-label="group of cards" tabindex="0" class="focus:outline-none py-2 w-full">
                                                                <div tabindex="0" aria-label="card 1" class="mb-7 bg-white p-6 shadow rounded">
                                                                    <div class="flex items-center border-b border-gray-200 pb-4 mb-2">
                                                                        <div class="flex items-start justify-between w-full">
                                                                            <div class="pl-3 w-full text-left line-clamp-1">
                                                                                <a href="{{ route('questions.view', ['qid' => $question->id]) }}" tabindex="0" class="focus:outline-none text-xl font-medium leading-5 text-gray-800">
                                                                                    {{ $question->question }}
                                                                                </a>
                                                                                <p tabindex="0" class="focus:outline-none text-sm leading-normal pt-2 text-gray-500">
                                                                                    {{ $question->created_at->diffForHumans() }}
                                                                                </p>
                                                                            </div>
                                                                            <div role="img" aria-label="bookmark">
                                                                                <i class="fa fa-check-circle {{ $question->status == 0 ? 'text-red-500' : 'text-green-500' }} mt-1 mx-1" aria-hidden="true"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="px-2 text-left">
                                                                        <a href="{{ route('questions.view', ['qid' => $question->id]) }}" tabindex="0" class="focus:outline-none text-sm leading-5 py-4 text-gray-600 mt-3 pt-3 line-clamp-3">
                                                                            {{ $question->description }}
                                                                        </a>

                                                                    </div>
                                                                    <div class="flex items-center justify-center border-t border-gray-200 pt-4 mt-3">
                                                                        <a href="{{ route('questions.view', ['qid' => $question->id]) }}" class="bg-white text-gray-800 active:bg-gray-100 text-xs font-bold uppercase px-4 py-2 rounded shadow hover:shadow-md outline-none focus:outline-none lg:mr-1 lg:mb-0 ml-3 mb-3" type="button" style="transition: all 0.15s ease 0s;">
                                                                            View Question
                                                                            </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="hidden" id="solutions">
                                                <div class="mx-2 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
                                                    @if(!empty($user->solutions))
                                                        @foreach($user->solutions as $solution)
                                                            <div aria-label="group of cards" tabindex="0" class="focus:outline-none py-2 w-full">
                                                                <div tabindex="0" aria-label="card 1" class="mb-7 bg-white p-6 shadow rounded">
                                                                    <div class="flex items-center border-b border-gray-200 pb-4 mb-2">
                                                                        {{-- <img src="https://cdn.tuk.dev/assets/components/misc/doge-coin.png" alt="coin avatar" class="w-12 h-12 rounded-full" /> --}}
                                                                        <div class="flex items-start justify-between w-full">
                                                                            <div class="pl-3 w-full text-left">
                                                                                <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" tabindex="0" class="focus:outline-none text-xl font-medium leading-5 text-gray-800 line-clamp-3">
                                                                                    {{ $solution->solution_title }}
                                                                                </a>
                                                                                <p tabindex="0" class="focus:outline-none text-sm leading-normal pt-2 text-gray-500">
                                                                                    {{ $solution->created_at->diffForHumans() }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="px-2 text-left">
                                                                        <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" tabindex="0" class="focus:outline-none text-sm leading-5 py-4 text-gray-600 mt-3 pt-3 line-clamp-3">
                                                                            {{ $solution->solution_description }}
                                                                        </a>
                                                                        @if(!empty($solution->tags))
                                                                            <div tabindex="0" class="focus:outline-none flex">
                                                                                @php
                                                                                    $tags = explode(',', $solution->tags) ?? '';
                                                                                @endphp
                                                                                @foreach($tags as $tag)
                                                                                    <div class="py-2 mx-1 my-3 px-4 text-xs leading-3 text-indigo-700 rounded-full bg-indigo-100">
                                                                                        {{ $tag }}
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center justify-center border-t border-gray-200 pt-4 mt-3">
                                                                        <a href="{{ route('solutions.view', ['id' => $solution->id]) }}" class="bg-white text-gray-800 active:bg-gray-100 text-xs font-bold uppercase px-4 py-2 rounded shadow hover:shadow-md outline-none focus:outline-none lg:mr-1 lg:mb-0 ml-3 mb-3" type="button" style="transition: all 0.15s ease 0s;">
                                                                            View Solution
                                                                            </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@include('layouts.footer')
</x-app-layout>
