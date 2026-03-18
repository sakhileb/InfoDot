<section class="relative py-5 lg:py-7" x-data="{ mobileSidebarOpen: false }">
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-5 lg:hidden">
            <button
                type="button"
                class="inline-flex items-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold tracking-wide text-slate-100 shadow-sm transition hover:border-blue-500/60 hover:bg-slate-800"
                @click="mobileSidebarOpen = true"
            >
                <i class="fa fa-bars mr-2" aria-hidden="true"></i> Menu
            </button>
        </div>

        <div class="lg:h-[calc(100vh-6.5rem)] lg:overflow-hidden">
            <aside class="hidden lg:fixed lg:left-0 lg:top-16 lg:block lg:h-[calc(100vh-4rem)] lg:w-72 lg:overflow-hidden lg:border-r lg:border-slate-800 lg:bg-slate-950/95 lg:px-5 lg:py-6 lg:shadow-[8px_0_28px_-20px_rgba(2,6,23,0.85)]">
                @include('partials.dashboard-sidebar')
            </aside>

            <main class="min-w-0 lg:ml-72 lg:h-full lg:overflow-y-auto">
                <div class="space-y-6 pb-8 lg:pl-8 lg:pt-1">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 bg-slate-950/75 p-4 backdrop-blur-sm lg:hidden" x-cloak>
        <div class="ml-auto h-full w-full max-w-xs overflow-y-auto rounded-2xl border border-slate-800 bg-slate-950 p-5 shadow-2xl">
            <div class="mb-4 flex justify-end">
                <button
                    type="button"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm font-medium text-slate-100 hover:bg-slate-800"
                    @click="mobileSidebarOpen = false"
                >
                    Close
                </button>
            </div>
            @include('partials.dashboard-sidebar')
        </div>
    </div>
</section>