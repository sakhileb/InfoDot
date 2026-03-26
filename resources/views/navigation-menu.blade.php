<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-[#434656]/25 bg-[#0b1326]/90 text-[#dae2fd] backdrop-blur-xl" style="font-family:'Inter',sans-serif;">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('solutions') }}">
                        <img src="{{ asset('img/icons/icon.png') }}" class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center space-x-2 sm:ml-8 sm:flex">
                    <a href="{{ route('questions') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold leading-5 transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'bg-[#2962ff] text-[#f7f5ff] shadow-[0_8px_20px_rgba(41,98,255,0.35)]' : 'text-[#b7c8e1] hover:bg-[#1a2438] hover:text-[#b6c4ff]' }}">
                        {{ __('Questions') }}
                    </a>
                    <a href="{{ route('solutions') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold leading-5 transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'bg-[#2962ff] text-[#f7f5ff] shadow-[0_8px_20px_rgba(41,98,255,0.35)]' : 'text-[#b7c8e1] hover:bg-[#1a2438] hover:text-[#b6c4ff]' }}">
                        {{ __('Business Solutions') }}
                    </a>
                </div>
            </div>
            <div class="my-2 hidden w-96 lg:block">
                <livewire:search/>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-jet-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center rounded-md border border-[#434656]/50 bg-[#131b2e] px-3 py-2.5 text-sm font-medium leading-5 text-[#dae2fd] shadow-sm transition hover:border-[#2962ff]/50 hover:bg-[#1a2438] hover:text-[#b6c4ff] focus:outline-none focus:border-[#2962ff]/50 focus:bg-[#1a2438] active:bg-[#1a2438]">
                                        {{ __('Manage Team')}}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2.5 text-xs tracking-wide text-[#8d90a2] uppercase">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-jet-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-jet-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-jet-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-jet-dropdown-link>
                                    @endcan

                                    <div class="border-t border-[#434656]/40"></div>

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2.5 text-xs tracking-wide text-[#8d90a2] uppercase">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-jet-switchable-team :team="$team" />
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-jet-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-jet-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Auth::user()->profile_photo_path != null)
                                    <button class="flex rounded-full border-2 border-transparent text-sm transition focus:outline-none focus:border-blue-500">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-transparent px-3 py-2.5 text-sm font-medium leading-5 text-slate-200 transition hover:text-blue-300 focus:outline-none">
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->avatar() }}" alt="{{ Auth::user()->name }}" />
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                                <div class="block px-4 py-2.5 text-xs tracking-wide text-[#8d90a2] uppercase">
                            </div>

                            <x-jet-dropdown-link href="{{ route('profile.show', ['id' => Auth::id()]) }}">
                                {{ __('Profile') }}
                            </x-jet-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-jet-dropdown-link>
                            @endif

                            <div class="border-t border-[#434656]/40"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-jet-dropdown-link href="{{ route('logout') }}"
                                         onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-jet-dropdown-link>
                            </form>
                        </x-slot>
                    </x-jet-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-[#8d90a2] transition hover:bg-[#1a2438] hover:text-[#b6c4ff] focus:outline-none focus:bg-[#1a2438] focus:text-[#b6c4ff]">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-2 bg-[#0b1326] px-4 pt-3 pb-4">
            <a href="{{ route('questions') }}" class="block rounded-xl px-4 py-2.5 text-sm font-semibold leading-5 transition {{ request()->routeIs('questions') || request()->routeIs('questions.view') || request()->routeIs('seek') ? 'bg-[#2962ff] text-[#f7f5ff]' : 'text-[#b7c8e1] hover:bg-[#1a2438] hover:text-[#b6c4ff]' }}">
                {{ __('Questions') }}
            </a>
            <a href="{{ route('solutions') }}" class="block rounded-xl px-4 py-2.5 text-sm font-semibold leading-5 transition {{ request()->routeIs('solutions') || request()->routeIs('solutions.view') || request()->routeIs('add') ? 'bg-[#2962ff] text-[#f7f5ff]' : 'text-[#b7c8e1] hover:bg-[#1a2438] hover:text-[#b6c4ff]' }}">
                {{ __('Business Solutions') }}
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-[#434656]/25 bg-[#0b1326] pt-4 pb-1">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @else
                    <div class="shrink-0 mr-3">
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->avatar() }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="text-base font-medium text-[#dae2fd]">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-[#8d90a2]">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-2">
                <!-- Account Management -->
                <x-jet-responsive-nav-link href="{{ route('profile.show', ['id' => Auth::id()]) }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-jet-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-jet-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-jet-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-jet-responsive-nav-link href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-jet-responsive-nav-link>
                </form>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-[#434656]/40"></div>

                    <div class="block px-4 py-2.5 text-xs tracking-wide text-[#8d90a2] uppercase">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-jet-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-jet-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-jet-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-jet-responsive-nav-link>
                    @endcan

                    <div class="border-t border-[#434656]/40"></div>

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2.5 text-xs tracking-wide text-slate-400">
                        {{ __('Switch Teams') }}
                    </div>

                    @foreach (Auth::user()->allTeams() as $team)
                        <x-jet-switchable-team :team="$team" component="jet-responsive-nav-link" />
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</nav>
