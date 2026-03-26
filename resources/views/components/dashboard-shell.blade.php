<section class="relative py-5 lg:py-7" x-data="{ mobileSidebarOpen: false }">
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-5 lg:hidden">
            <button
                type="button"
                class="inline-flex items-center rounded-xl border border-[#434656]/40 bg-[#131b2e] px-4 py-2.5 text-sm font-semibold tracking-wide text-[#dae2fd] shadow-sm transition hover:border-[#2962ff]/40 hover:bg-[#1a2438]"
                @click="mobileSidebarOpen = true"
            >
                <i class="fa fa-bars mr-2" aria-hidden="true"></i> Menu
            </button>
        </div>

        <div class="lg:h-[calc(100vh-6.5rem)] lg:overflow-hidden">
            <aside class="hidden lg:fixed lg:left-0 lg:top-16 lg:block lg:h-[calc(100vh-4rem)] lg:w-72 lg:overflow-hidden lg:border-r lg:border-[#434656]/20 lg:bg-[#0b1326]/95 lg:px-5 lg:py-6 lg:shadow-[8px_0_28px_-20px_rgba(6,14,32,0.9)]">
                @include('partials.dashboard-sidebar')
            </aside>

            <main class="min-w-0 lg:ml-72 lg:h-full lg:overflow-y-auto">
                <div class="space-y-6 pb-8 lg:pl-8 lg:pt-1">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div x-show="mobileSidebarOpen" class="fixed inset-0 z-50 bg-[#060e20]/80 p-4 backdrop-blur-sm lg:hidden" x-cloak>
        <div class="ml-auto h-full w-full max-w-xs overflow-y-auto rounded-2xl border border-[#434656]/25 bg-[#0b1326] p-5 shadow-2xl">
            <div class="mb-4 flex justify-end">
                <button
                    type="button"
                    class="rounded-lg border border-[#434656]/40 bg-[#131b2e] px-3 py-1.5 text-sm font-medium text-[#dae2fd] hover:bg-[#1a2438]"
                    @click="mobileSidebarOpen = false"
                >
                    Close
                </button>
            </div>
            @include('partials.dashboard-sidebar')
        </div>
    </div>
</section>