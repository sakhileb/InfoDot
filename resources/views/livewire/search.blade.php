<div class="min-w-full !z-50">
    <input
        type="text"
        id="search"
        placeholder="Ask: How to register a business?"
        data-typed-items=""
        autofocus
        class="bg-transparent text-gray-500 sm:w-96 w-11/12 mx-5 focus:outline-none py-3 px-5 text-md border-t border-b border-l border-r border-blue-lighter rounded-full"
        wire:model.live="query"
        wire:keydown.escape="resetFilters"
        wire:keydown.arrow-up="decrementHighlight"
        wire:keydown.arrow-down="incrementHighlight"
    />

    @if(!empty($query))
        <div class="mt-1 sm:w-96 lg:ml-6 lg:mr-4 bg-white border-b border-l border-r border-blue-lighter rounded-lg !z-50">
            @if(!empty($solutions))
                @foreach($solutions as $i => $solution)
                    <a
                        href="{{ route('solution_search_results', ['search' => $solution['solution_title']]) }}"
                        class="relative list-item-none !z-50 hover:bg-gray-800 bg-white border-b hover:text-white text-gray-500 flex items-center outline-1 py-3 px-5 w-full ml-0 md:mx-auto text-md {{ $highlightIndex === $i ? 'highlight ' : '' }}" >{{ $solution['solution_title'] }}</a>
                @endforeach
            @endif
            @if(!empty($questions))
                @foreach($questions as $i => $question)
                    <a
                        href="{{ route('solution_search_results', ['search' => $question['question']]) }}"
                        class="relative list-item-none !z-50 hover:bg-gray-800 bg-white border-b hover:text-white text-gray-500 flex items-center outline-1 py-3 px-5 w-full ml-0 md:mx-auto text-md {{ $highlightIndex === ($i + count($solutions)) ? 'highlight ' : '' }}" >{{ $question['question'] }}</a>
                @endforeach
            @endif
            @if($this->hasResults)
                <p class="text-gray-800 my-3 mx-auto list-item-none !z-50">Top {{ $this->totalResults }} results for: {{ $query }}</p>
            @else
                <div class="list-item-none !z-50 outline-none py-3 pl-3 pr-5 w-full ml-4 mr-4 md:ml-24 md:mr-16 text-md flex justify-start items-center">
                    <p class="text-gray-300">No Data Returned.</p>
                </div>
            @endif
        </div>
    @endif
    @section('js')
        @parent
        <script type="text/javascript">
            var typed4 = new Typed('#search', {
                strings: ['Some strings without', 'Some HTML', 'Chars'],
                typeSpeed: 0,
                backSpeed: 0,
                attr: 'placeholder',
                bindInputFocusEvents: true,
                loop: true
            });
        </script>
    @endsection
</div>
