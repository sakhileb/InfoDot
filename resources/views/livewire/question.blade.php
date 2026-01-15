<div class="max-w-full px-4 pt-8 mx-5 hover">
    <section class="py-4 px-4 bg-white rounded-lg">
        <blockquote class="sm:col-span-2">
            <cite class="inline-flex items-center not-italic">
                <div class="flex items-center">
                    @if ($question->user->profile_photo_path != null)
                        <img src="{{ $question->user->profile_photo_url }}" class="h-8 w-8 rounded-full object-cover" alt="{{ $question->user->name }}">
                    @else
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ $question->user->avatar() }}" alt="{{ $question->user->name }}" />
                    @endif
                    <div class="ml-4 text-sm">
                        <a href="{{ route('profile.show', ['id' => $question->user->id]) }}" class="font-medium capitalize text-gray-800"><strong>{{ $question->user->name }}</strong></a>
                        <small class="mt-1 text-gray-400">{{ $question->created_at->diffForHumans() }}.</small>
                    </div>
                </div>
            </cite>
        </blockquote>
        <hr class="my-3" />
        <div class="w-full sm:items-center">
            <div class="relative">
                <div class="aspect-w-1 aspect-h-1">
                    <a href="{{ route('questions.view', ['qid' => $question->id]) }}">
                        <p class="text-xl font-medium sm:text-2xl capitalize text-gray-800">
                            {{ $question->question }}
                        </p>
                        <p class="capitalize text-gray-800">{{ $question->description }}</p>
                    </a>
                </div>

            </div>

        </div>
        <hr class="my-3" />
        <div class="w-full grid grid-cols-3 gap-4 mx-auto">

            <div class="text-gray-800 flex justify-center">
                <a href="#" class="inline-flex items-center {{ $question->likes()->where('user_id', Auth::id())->exists() ? 'text-blue-500' : '' }}" wire:click.prevent="storeLike">
                    <i class="fa fa-thumbs-up mx-1" aria-hidden="true"></i> {{ $question->likes()->count() }}
                </a>
            </div>

            <div class="text-gray-800 flex justify-center">
                <i class="fa fa-comment mt-1 mx-1" aria-hidden="true"></i> {{ $question->comments()->count() }}
            </div>

            <div class="text-green-800 flex justify-center">
                @if(Auth()->user()->id === $model->user->id)
                    <a href="#" class="inline-flex items-center
                    {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }}" wire:click.prevent="markedAsSolved">
                        <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }} mt-1 mx-1" aria-hidden="true"></i>
                    </a>
                @else
                    <i class="fa fa-check-circle {{ $model->status == 0 ? 'text-red-500' : 'text-green-500' }} mt-1 mx-1" aria-hidden="true"></i>
                @endif
            </div>
        </div>

    </section>
</div>
