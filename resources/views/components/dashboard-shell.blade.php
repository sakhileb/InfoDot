<section class="py-8" x-data="{ mobileSidebarOpen: false }">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 lg:hidden">
            <button type="button" class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-slate-100 shadow-lg shadow-slate-950/30" @click="mobileSidebarOpen = true">
                Open Menu
            </button>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <div class="hidden lg:block lg:sticky lg:top-24 lg:self-start">
                @include('partials.dashboard-sidebar')
            </div>

            <main class="min-w-0 text-slate-100">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 bg-slate-950/80 p-4 backdrop-blur-sm lg:hidden" x-cloak>
        <div class="mx-auto max-w-sm">
            <div class="mb-2 flex justify-end">
                <button type="button" class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-1 text-sm text-slate-100" @click="mobileSidebarOpen = false">Close</button>
            </div>
            @include('partials.dashboard-sidebar')
        </div>
    </div>
</section>