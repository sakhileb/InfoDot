<div>
    <div class="flex items-start justify-center">
        <form class="w-full" wire:submit.prevent="postComment">
            <textarea name="comment" id="comment" wire:model.defer="newCommentState.body" class="text-gray-900 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" placeholder="Leave a comment!"></textarea>
            <button type="submit" class="justify-items-start btn rounded-full m-3">Comment</button>
        </form>
        @error('newCommentState.body')
            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <ul class="block w-11/12 my-4 mx-auto" x-data="{selected:null}">
        <li class="flex align-center flex-col">
            <hr class="my-3 text-gray-900">
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

                <div class="text-gray-800 flex justify-center">
                    <a href="#" class="inline-flex items-center {{ $model->likes()->where('user_id', Auth::id())->exists() ? 'text-blue-500' : '' }}" wire:click.prevent="storeLike">
                    <i class="fa fa-thumbs-up mx-1" aria-hidden="true"></i> {{ $model->likes()->count() }}
                </a>
                </div>

                <h4 @click="selected != 1 ? selected = 1 : selected = null" class="text-gray-600 cursor-pointer">
                    <i class="fa fa-comment mt-1 mx-1" aria-hidden="true"></i> {{ $model->comments()->count() }}
                </h4>

                <div class="text-gray-800 flex justify-center">
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
            <hr class="my-3 text-gray-900">
            <div x-show="selected == 1" class="py-4 px-2 text-gray-600">
                @forelse($comments as $comment)
                    <livewire:comment :comment="$comment" :key="$comment->id"/>
                    <hr class="my-3 text-gray-900">
                @empty
                    <p class="text-gray-900">No comments yet</p>
                @endforelse
            </div>

        </li>
    </ul>
</div>
