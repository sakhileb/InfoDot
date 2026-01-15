<div class="container flex flex-col w-full items-center justify-center rounded-lg shadow">
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
                <button wire:click.prevent="loadMore" class="px-4 py-2 btn btn-xs rounded-full flex justify-center items-center">Load More</button>
            @endif
        </div>
    </div>
</div>
