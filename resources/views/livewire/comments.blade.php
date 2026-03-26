<div>
    <div class="flex items-start justify-center">
        <form class="w-full" wire:submit.prevent="postComment">
            <textarea name="comment" id="comment" wire:model.defer="newCommentState.body" class="mt-1 block w-full rounded-2xl border border-[#434656]/50 bg-[#131b2e] px-4 py-3 text-[#dae2fd] shadow-sm placeholder-[#434656] focus:border-[#2962ff] focus:outline-none focus:ring-2 focus:ring-[#2962ff]/20" placeholder="Leave a comment!"></textarea>
            <button type="submit" class="m-3 justify-items-start rounded-full bg-[#2962ff] px-5 py-2 text-sm font-semibold text-[#f7f5ff] shadow-[0_8px_20px_rgba(41,98,255,0.3)] transition hover:bg-[#004ee8]">Comment</button>
        </form>
        @error('newCommentState.body')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>
    <ul class="block w-11/12 my-4 mx-auto" x-data="{selected:null}">
        <li class="flex align-center flex-col">
            <hr class="my-3 border-[#434656]/30">
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

                <div class="flex justify-center text-[#b7c8e1]">
                    <a href="#" class="inline-flex items-center {{ $model->likes()->where('user_id', Auth::id())->exists() ? 'text-[#b6c4ff]' : '' }}" wire:click.prevent="storeLike">
                    <i class="fa fa-thumbs-up mx-1" aria-hidden="true"></i> {{ $model->likes()->count() }}
                </a>
                </div>

                <h4 @click="selected != 1 ? selected = 1 : selected = null" class="cursor-pointer text-[#8d90a2]">
                    <i class="fa fa-comment mt-1 mx-1" aria-hidden="true"></i> {{ $model->comments()->count() }}
                </h4>

                <div class="flex justify-center text-[#b7c8e1]">
                    @if($question)
                        @if(Auth()->user()->id === $model->user->id)
                            <a href="#" class="inline-flex items-center
                            {{ $model->status == 0 ? 'text-red-400' : 'text-green-400' }}" wire:click.prevent="markedAsSolved">
                                <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-400' : 'text-green-400' }} mt-1 mx-1" aria-hidden="true"></i>
                            </a>
                        @else
                            <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-400' : 'text-green-400' }} mt-1 mx-1" aria-hidden="true"></i>
                        @endif
                    @endif
                </div>
            </div>
            <hr class="my-3 border-[#434656]/30">
            <div x-show="selected == 1" class="px-2 py-4 text-[#c3c5d8]">
                @forelse($comments as $comment)
                    <livewire:comment :comment="$comment" :key="$comment->id"/>
                    <hr class="my-3 border-[#434656]/30">
                @empty
                    <p class="text-[#8d90a2]">No comments yet</p>
                @endforelse
            </div>

        </li>
    </ul>
</div>
