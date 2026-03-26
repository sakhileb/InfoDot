<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-9 text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">Dot.Forms</h2>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-[#434656]/35 bg-[rgba(49,57,77,0.55)] p-7 shadow-[0_20px_44px_rgba(0,0,0,0.35)]" style="backdrop-filter:blur(20px);">
            <h3 class="text-xl font-bold leading-8 text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">Dot.Forms Workspace</h3>
            <p class="mt-3 text-sm leading-7 text-[#8d90a2]">This page is ready for form builders, response collection, and workflow automation.</p>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
