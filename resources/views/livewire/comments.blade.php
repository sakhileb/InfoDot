<div>
    <div class="flex items-start justify-center">
        <form class="w-full" wire:submit.prevent="postComment">
            <textarea name="comment" id="comment" wire:model.defer="newCommentState.body" class="mt-1 block w-full rounded-2xl border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 shadow-sm placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="Leave a comment!"></textarea>
            <button type="submit" class="m-3 justify-items-start rounded-xl bg-sky-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-[0_12px_30px_-12px_rgba(56,189,248,0.8)] transition hover:bg-sky-400">Comment</button>
        </form>
        @error('newCommentState.body')
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <ul class="block w-11/12 my-4 mx-auto" x-data="{selected:null}">
        <li class="flex align-center flex-col">
            <hr class="my-3 border-slate-800">
            <div class="flex justify-between mx-16">
                @php
                    if (empty($question))
                    {
                        $model = $solution;
                    }
                    else
                    {
                        $model = $question;
                    }
                @endphp

                <div class="flex justify-center text-slate-300">
                    <a href="#" class="inline-flex items-center {{ $model->likes()->where('user_id', Auth::id())->exists() ? 'text-sky-400' : '' }}" wire:click.prevent="storeLike">
                    <i class="fa fa-thumbs-up mx-1" aria-hidden="true"></i> {{ $model->likes()->count() }}
                </a>
                </div>

                <h4 @click="selected != 1 ? selected = 1 : selected = null" class="cursor-pointer text-slate-400">
                    <i class="fa fa-comment mt-1 mx-1" aria-hidden="true"></i> {{ $model->comments()->count() }}
                </h4>

                <div class="flex justify-center text-slate-300">
                    @if($question)
                        @if(Auth()->user()->id === $model->user->id)
                            <a href="#" class="inline-flex items-center
                            {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }}" wire:click.prevent="markedAsSolved">
                                <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }} mt-1 mx-1" aria-hidden="true"></i>
                            </a>
                        @else
                            <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }} mt-1 mx-1" aria-hidden="true"></i>
                        @endif
                    @endif
                </div>
            </div>
            <hr class="my-3 border-slate-800">
            <div x-show="selected == 1" class="px-2 py-4 text-slate-300">
                @forelse($comments as $comment)
                    <livewire:comment :comment="$comment" :key="$comment->id"/>
                    <hr class="my-3 border-slate-800">
                @empty
                    <p class="text-slate-400">No comments yet</p>
                @endforelse
            </div>

        </li>
    </ul>
</div>
