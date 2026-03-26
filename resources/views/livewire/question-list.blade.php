<div class="container flex flex-col w-full items-center justify-center">
    <div class="overflow-x-auto w-full pb-8">

        @foreach($questions as $question)
            <livewire:question :question="$question" :key="$question->id" :model="$question" />
        @endforeach

        <div x-data="{
            observe () {
                let observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            @this.call('loadMore')
                        }
                    })
                }, {
                    root: null
                })

                observer.observe(this.$el)
            }
        }"
        x-init="observe"></div>
        <div class="w-full">
            @if($questions->hasMorePages())
                <button wire:click.prevent="loadMore" class="mt-4 px-6 py-2.5 rounded-full border border-[#434656]/40 bg-[#131b2e] text-[#b7c8e1] text-sm font-semibold flex justify-center items-center hover:bg-[#1a2438] hover:text-[#b6c4ff] transition">Load More</button>
            @endif
        </div>
    </div>
</div>




