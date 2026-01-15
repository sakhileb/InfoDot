<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#000000">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <link rel="shortcut icon" href="{{ asset('img/icons/icon.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/icons/icon.png') }}">

        <title>{{ config('app.name', 'InfoDot') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700&display=swap" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
        
        <!-- Tagify -->
        <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Custom Styles -->
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

        <!-- Styles -->
        @livewireStyles
        
        <!-- User Data for JavaScript -->
        @if(auth()->user())
            <script>
                window.User = {
                    id: {{ optional(auth()->user())->id }},
                    avatar: '{{ optional(auth()->user())->avatar() }}'
                }
            </script>
        @endif
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100" id="app">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        
        <!-- External Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
        <script src="https://kit.fontawesome.com/8690917e6c.js" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/@yaireo/tagify"></script>
        <script src="https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
        <script src="{{ asset('js/tags.js') }}" crossorigin="anonymous"></script>
        <script src="{{ asset('js/addSteps.js') }}" crossorigin="anonymous"></script>
        
        <!-- Tab Switching Function -->
        <script type="text/javascript">
            function changeAtiveTab(event, tabID) {
                let element = event.target;
                while(element.nodeName !== "A") {
                    element = element.parentNode;
                }
                ulElement = element.parentNode.parentNode;
                aElements = ulElement.querySelectorAll("li > a");
                tabContents = document.getElementById("content-tabs-id").querySelectorAll(".tab-content > div");
                for(let i = 0; i < aElements.length; i++) {
                    tabContents[i].classList.add("hidden");
                    tabContents[i].classList.remove("block");
                }
                document.getElementById(tabID).classList.remove("hidden");
                document.getElementById(tabID).classList.add("block");
            }
        </script>
    </body>
</html>
