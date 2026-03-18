<div class="flex items-center rounded-2xl border border-slate-800 bg-slate-950/70 p-4">
    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
        <img class="w-12 h-12 rounded-full mr-3" src="{{ asset($comment->user->avatar()) }}" alt="{{ Auth::user()->name }}" />
    @else
        <img src="https://via.placeholder.com/100x100" class="w-12 h-12 rounded-full mr-3" alt="Avatar">
    @endif
    <div>
        <p class="text-white"><strong>{{ $comment->user->name }}</strong></p>
        <p class="text-slate-300"><span>{{ $comment->body }}</span></p>
    </div>
</div>



