<div class="flex items-center rounded-2xl border border-[#434656]/30 bg-[#131b2e] p-4">
    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
        <img class="w-12 h-12 rounded-full mr-3" src="{{ asset($comment->user->avatar()) }}" alt="{{ Auth::user()->name }}" />
    @else
        <img src="https://via.placeholder.com/100x100" class="w-12 h-12 rounded-full mr-3" alt="Avatar">
    @endif
    <div>
        <p class="text-[#dae2fd]"><strong>{{ $comment->user->name }}</strong></p>
        <p class="text-[#c3c5d8]"><span>{{ $comment->body }}</span></p>
    </div>
</div>



