<section class="bg-slate-50 py-6" x-data="{ mobileSidebarOpen: false }">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 lg:hidden">
            <button type="button" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700" @click="mobileSidebarOpen = true">
                Open Menu
            </button>
        </div>

        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <div class="hidden lg:block">
                @include('partials.dashboard-sidebar')
            </div>

            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 bg-slate-900/40 p-4 lg:hidden" x-cloak>
        <div class="mx-auto max-w-sm">
            <div class="mb-2 flex justify-end">
                <button type="button" class="rounded-lg bg-white px-3 py-1 text-sm" @click="mobileSidebarOpen = false">Close</button>
            </div>
            @include('partials.dashboard-sidebar')
        </div>
    </div>
</section>