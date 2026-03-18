<div>
    @foreach($solutions as $solution)
        <div class="mx-auto sm:px-3 lg:px-4 my-6">
            <div class="w-full px-4" >
                <div class="card glass lg:card-side text-neutral-content">
                    <figure class="p-6">
                        <img src="https://source.unsplash.com/random/300x200?productivity,business" class="rounded-lg shadow-lg">
                        <div class="grid grid-cols-2">
                            <span class="flex justify-center m-4">
                                <i class="fas fa-clock p-1"></i>
                                {{ $solution->duration }} {{ $solution->duration_type }}
                            </span>
                            <span class="flex justify-center m-4">
                                <i class="fas fa-shoe-prints p-1"></i> {{ $solution->steps }}
                            </span>
                        </div>
                    </figure>
                    <div class="max-w-md card-body">
                        <h2 class="card-title capitalize">
                            {{$solution->solution_title ?? ''}}
                        </h2>
                        <p class="line-clamp-3">
                            {{ $solution->solution_description ?? '' }}
                        </p>
                        <div class="grid grid-cols-3">
                            <span class="flex justify-start pt-3">
                                <i class="fas fa-eye p-1" aria-hidden="true"></i> 3
                            </span>
                            <span class="flex justify-start pt-3">
                                <i class="fa fa-comment p-1" aria-hidden="true"></i> {{ $solution->comments()->count() }}
                            </span>
                            <span class="flex justify-start pt-3">
                                <i class="fa fa-bar-chart p-1" aria-hidden="true"></i> 10
                            </span>
                        </div>
                        <div class="card-actions">
                            <a class="btn glass rounded-full" href="{{ route('solutions.view', ['id' => $solution->id]) }}">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            @if($solutions->hasMorePages())
                <button wire:click.prevent="loadMore" class="px-4 py-2 btn btn-xs rounded-full flex justify-center items-center">Load More</button>
            @endif
        </div>
</div>
