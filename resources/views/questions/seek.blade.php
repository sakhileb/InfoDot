<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <h2 class="text-xl font-semibold leading-tight text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">
                {{ __('Ask a Question') }}
            </h2>
            <a href="{{ route('questions') }}" class="inline-flex items-center rounded-full border border-[#434656]/40 bg-[#131b2e] px-5 py-2.5 text-sm font-semibold leading-5 text-[#dae2fd] shadow-sm transition hover:bg-[#1a2438] hover:text-[#b6c4ff]">
                &larr; Back to Questions
            </a>
        </div>
    </x-slot>

    <x-dashboard-shell>
        <div class="rounded-3xl border border-[#434656]/35 bg-[rgba(49,57,77,0.55)] p-7 shadow-[0_20px_44px_rgba(0,0,0,0.35)] lg:p-8" style="backdrop-filter:blur(20px);">
            <h1 class="text-2xl font-bold leading-9 text-[#dae2fd]" style="font-family:'Manrope',sans-serif;">Ask a New Question</h1>
            <p class="mt-3 text-sm leading-7 text-[#8d90a2]">Share your challenge clearly so the community can help effectively.</p>

            <div class="form mt-7 lg:w-full">
                        <form class="w-full space-y-1" method="POST" action="{{ route('questions.add') }}" onkeydown="return event.key != 'Enter';">
                            @csrf
                            <div class="mb-7 flex flex-wrap -mx-3 gap-y-6">
                                <div class="mb-1 w-full px-3 md:w-full">
                                    <label class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-[#8d90a2]" for="question">Question:</label>
                                    <input class="block w-full rounded-2xl border border-[#434656]/50 bg-[#131b2e] px-4 py-3.5 text-sm leading-6 text-[#dae2fd] placeholder-[#434656] focus:border-[#2962ff] focus:outline-none focus:ring-2 focus:ring-[#2962ff]/20" name="question" id="question" type="text" placeholder="How to......?">
                                    @if($errors->has('question'))
                                        <div class="mt-2 text-xs leading-5 text-red-400">{{ $errors->first('question') }}</div>
                                    @endif
                                </div>
                                <div class="mb-1 w-full px-3">
                                    <label for="description" class="mb-2.5 block text-xs font-bold uppercase tracking-wide text-[#8d90a2]">
                                        Description:
                                    </label>
                                    <textarea class="h-28 w-full rounded-2xl border border-[#434656]/50 bg-[#131b2e] px-4 py-3.5 text-sm leading-6 text-[#dae2fd] placeholder-[#434656] focus:border-[#2962ff] focus:outline-none focus:ring-2 focus:ring-[#2962ff]/20" name="description" placeholder="Describe the problem you are facing....."></textarea>
                                    @if($errors->has('description'))
                                        <div class="mt-2 text-xs leading-5 text-red-400">{{ $errors->first('description') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap -mx-3 mb-2 justify-start">
                                <button class="ml-3 rounded-full bg-gradient-to-r from-[#2962ff] to-[#004ee8] px-7 py-3 text-sm font-bold leading-5 text-[#f7f5ff] shadow-[0_8px_20px_rgba(41,98,255,0.3)] transition hover:shadow-[0_12px_28px_rgba(41,98,255,0.45)]" type="submit">Submit Question</button>
                            </div>
                        </form>
                    </div>
        </div>
    </x-dashboard-shell>

    @include('layouts.footer')
</x-app-layout>
