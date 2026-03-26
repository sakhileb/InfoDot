@props(['title' => null])

<section class="px-4 py-6 sm:px-6 lg:px-10 lg:py-8" style="font-family:'Inter',sans-serif;">
    <div class="mx-auto max-w-7xl">
        @if ($title)
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight text-[#b6c4ff]" style="font-family:'Manrope',sans-serif;">{{ $title }}</h1>
            </div>
        @endif

        {{ $slot }}
    </div>
</section>
