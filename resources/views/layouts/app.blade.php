<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#0b1326" />
    <link rel="shortcut icon" href="{{ asset('img/icons/icon.png') }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/icons/icon.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>InfoDot</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <!-- Tailwind v3 Play CDN — supports all arbitrary values like bg-[#hex] -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    fontFamily: {
                        headline: ['Manrope', 'sans-serif'],
                        body:     ['Inter',   'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: #0b1326; color: #dae2fd; font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; line-height: 1; }
        [x-cloak] { display: none !important; }
        .glass-card { background: rgba(49,57,77,0.6); backdrop-filter: blur(20px); border: 1px solid rgba(67,70,86,0.35); border-radius: 1rem; }
        .sidebar-link { display:flex;align-items:center;gap:0.75rem;padding:0.7rem 1rem;border-radius:0.5rem;font-family:'Manrope',sans-serif;font-size:0.875rem;font-weight:600;text-decoration:none;color:#b7c8e1;opacity:0.75;transition:all 0.2s; }
        .sidebar-link:hover { background:rgba(26,36,56,0.9);opacity:1;color:#b6c4ff; }
        .sidebar-link.active { border-left:4px solid #2962ff;background:rgba(41,98,255,0.1);color:#b6c4ff;opacity:1; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    </style>

    @livewireStyles
    <script defer src="https://unpkg.com/alpinejs@3.10.2/dist/cdn.min.js"></script>

    @if(auth()->user())
        <script>
            window.User = {
                id: {{ optional(auth()->user())->id }},
                avatar: '{{ optional(auth()->user())->avatar() }}'
            }
        </script>
    @endif
</head>
<body>
    <x-jet-banner />

    {{-- ═══════════════════════════════════════════
         FIXED LEFT SIDEBAR
    ═══════════════════════════════════════════ --}}
    <aside style="position:fixed;left:0;top:0;height:100vh;width:288px;display:flex;flex-direction:column;background:#131b2e;border-right:1px solid rgba(67,70,86,0.2);box-shadow:8px 0 32px rgba(6,14,32,0.5);z-index:50;overflow-y:auto;padding:2rem 1rem;">

        {{-- Brand --}}
        <div style="margin-bottom:2rem;padding:0 0.75rem;">
            <a href="{{ route('solutions') }}" style="display:flex;align-items:center;gap:0.75rem;text-decoration:none;">
                <img src="{{ asset('img/icons/icon.png') }}" style="height:32px;width:32px;border-radius:6px;" />
                <div>
                    <div style="font-family:'Manrope',sans-serif;font-size:1.05rem;font-weight:800;color:#b6c4ff;letter-spacing:-0.02em;">InfoDot</div>
                    <div style="font-size:0.6rem;font-weight:600;color:#b7c8e1;opacity:0.55;letter-spacing:0.18em;text-transform:uppercase;margin-top:2px;">Premium Console</div>
                </div>
            </a>
        </div>

        {{-- New Request CTA --}}
        <div style="margin:0 0.5rem 2rem;">
            <a href="{{ route('seek') }}" style="display:flex;align-items:center;justify-content:center;gap:0.5rem;border-radius:9999px;background:linear-gradient(135deg,#2962ff,#004ee8);padding:0.75rem 1.25rem;font-family:'Manrope',sans-serif;font-size:0.8rem;font-weight:700;color:#f7f5ff;text-decoration:none;box-shadow:0 8px 20px rgba(41,98,255,0.3);transition:box-shadow 0.25s,transform 0.15s;" onmouseover="this.style.boxShadow='0 12px 28px rgba(41,98,255,0.45)'" onmouseout="this.style.boxShadow='0 8px 20px rgba(41,98,255,0.3)'">
                <span class="material-symbols-outlined" style="font-size:19px;">add_circle</span>
                New Request
            </a>
        </div>

        {{-- Navigation --}}
        <nav style="flex:1;display:flex;flex-direction:column;gap:0.2rem;">

            <a href="{{ route('questions') }}" class="sidebar-link {{ request()->routeIs('questions', 'questions.view', 'seek') ? 'active' : '' }}">
                <span class="material-symbols-outlined" style="font-size:21px;">help_outline</span>
                <span>Questions</span>
            </a>

            <a href="{{ route('solutions') }}" class="sidebar-link {{ request()->routeIs('solutions', 'solutions.view', 'add') ? 'active' : '' }}">
                <span class="material-symbols-outlined" style="font-size:21px;">lightbulb</span>
                <span>Solutions</span>
            </a>

            <a href="{{ route('analytics.dashboard') }}" class="sidebar-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined" style="font-size:21px;">insights</span>
                <span>Analytics</span>
            </a>

            {{-- Sub Services --}}
            <div x-data="{ open: {{ request()->routeIs('subservices.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-link {{ request()->routeIs('subservices.*') ? 'active' : '' }}" style="width:100%;border:none;cursor:pointer;background:none;">
                    <span class="material-symbols-outlined" style="font-size:21px;">layers</span>
                    <span style="flex:1;text-align:left;">Sub Services</span>
                    <span class="material-symbols-outlined" style="font-size:16px;transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg)' : ''">expand_more</span>
                </button>
                <div x-show="open" x-cloak style="margin-left:2.25rem;display:flex;flex-direction:column;gap:0.15rem;margin-top:0.2rem;">
                    <a href="{{ route('subservices.files') }}"   style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.files')   ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Files</a>
                    <a href="{{ route('subservices.docs') }}"    style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.docs')    ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Docs</a>
                    <a href="{{ route('subservices.sheets') }}"  style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.sheets')  ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Sheets</a>
                    <a href="{{ route('subservices.press') }}"   style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.press')   ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Press</a>
                    <a href="{{ route('subservices.forms') }}"   style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.forms')   ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Forms</a>
                    <a href="{{ route('subservices.engage') }}"  style="display:block;padding:0.45rem 1rem;border-radius:0.4rem;font-size:0.82rem;text-decoration:none;{{ request()->routeIs('subservices.engage')  ? 'color:#b6c4ff;background:rgba(41,98,255,0.1);' : 'color:#b7c8e1;opacity:0.65;' }}">Dot.Engage</a>
                </div>
            </div>

        </nav>

        {{-- Bottom links + user --}}
        <div style="margin-top:auto;padding-top:1.5rem;border-top:1px solid rgba(67,70,86,0.2);display:flex;flex-direction:column;gap:0.2rem;">
            <a href="#" class="sidebar-link">
                <span class="material-symbols-outlined" style="font-size:21px;">settings</span>
                <span>Settings</span>
            </a>
            <a href="#" class="sidebar-link">
                <span class="material-symbols-outlined" style="font-size:21px;">contact_support</span>
                <span>Support</span>
            </a>

            @auth
            <div style="margin-top:1.25rem;padding:0 0.75rem;display:flex;align-items:center;gap:0.75rem;">
                <img src="{{ Auth::user()->profile_photo_path ? Auth::user()->profile_photo_url : Auth::user()->avatar() }}"
                     style="height:38px;width:38px;border-radius:9999px;object-fit:cover;border:2px solid rgba(41,98,255,0.25);flex-shrink:0;"
                     alt="{{ Auth::user()->name }}" />
                <div style="display:flex;flex-direction:column;min-width:0;">
                    <span style="font-size:0.75rem;font-weight:700;color:#dae2fd;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</span>
                    <span style="font-size:0.6rem;color:#8d90a2;text-transform:uppercase;letter-spacing:0.06em;margin-top:1px;">Pro Workspace</span>
                </div>
            </div>
            @endauth
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════
         FIXED TOP BAR (right of sidebar)
    ═══════════════════════════════════════════ --}}
    @livewire('navigation-menu')

    {{-- ═══════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════ --}}
    <div style="margin-left:288px;padding-top:80px;min-height:100vh;background:#0b1326;" id="app">
        @if (isset($header))
            <div style="padding:2rem 2.5rem 0;">
                {{ $header }}
            </div>
        @endif
        <main>
            {{ $slot }}
        </main>
    </div>

    {{-- Floating status badge --}}
    <div style="position:fixed;bottom:1.75rem;right:1.75rem;display:flex;align-items:center;gap:0.6rem;padding:0.45rem 1rem;background:rgba(49,57,77,0.65);backdrop-filter:blur(20px);border-radius:9999px;border:1px solid rgba(67,70,86,0.2);z-index:50;">
        <div style="width:7px;height:7px;border-radius:9999px;background:#22c55e;animation:pulse 2s infinite;"></div>
        <span style="font-size:0.6rem;font-weight:700;color:rgba(218,226,253,0.55);text-transform:uppercase;letter-spacing:0.18em;">Workspace Online</span>
    </div>

    @stack('modals')
    @livewireScripts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://kit.fontawesome.com/8690917e6c.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@yaireo/tagify"></script>
    <script src="https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <script src="{{ asset('js/tags.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('js/addSteps.js') }}" crossorigin="anonymous"></script>
    <script>
        function changeAtiveTab(event,tabID){
            let element = event.target;
            while(element.nodeName !== "A"){ element = element.parentNode; }
            ulElement = element.parentNode.parentNode;
            aElements = ulElement.querySelectorAll("li > a");
            tabContents = document.getElementById("content-tabs-id").querySelectorAll(".tab-content > div");
            for(let i = 0; i < aElements.length; i++){
                tabContents[i].classList.add("hidden");
                tabContents[i].classList.remove("block");
            }
            document.getElementById(tabID).classList.remove("hidden");
            document.getElementById(tabID).classList.add("block");
        }
    </script>
</body>
</html>
