<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-start items-center">
                {{ __('Read Question') }}
            </h2>
            <div>
                <a href="{{ route('seek') }}" class="justify-items-end btn rounded-full">
                    <i class="fa fa-search mr-1" aria-hidden="true"></i> Seek
                </a>
                <a href="{{ route('add') }}" class="justify-items-end btn rounded-full">
                    <i class="fa fa-plus mr-1" aria-hidden="true"></i> Add
                </a>
            </div>
        </div>
    </x-slot>
    <div class="flex">
        @include('partials.aside-left')

        <main class="w-full">
            <div class="px-4 overflow-y-scroll">
                <h1 class="text-gray-900 m-3 text-2xl flex w-full justify-between">
                    <span class="justify-start">
                        Asked By: {{ $question->user->name }}
                    </span>
                    @if(Auth::id() == $question->user->id)
                        <livewire:question-crud :model="$question" :question="$question"/>
                    @endif
                </h1>
                <hr class="my-3 text-gray-900">
                <h3 class="text-lg m-3 font-medium text-gray-900 capitalize">
                    Title: {{ $question->question }}
                </h3>
                <p class="m-3 text-sm text-gray-600">
                    Description: {{ $question->description }}
                </p>
                <livewire:comments :model="$question" :question="$question"/>
                
                <!-- Answers Section -->
                <div class="answers-section mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Answers ({{ $question->answers->count() }})
                        </h3>
                        @auth
                            <button class="btn btn-primary btn-sm" onclick="toggleAnswerForm()">
                                <i class="fa fa-plus mr-1"></i> Add Answer
                            </button>
                        @endauth
                    </div>

                    <!-- Add Answer Form -->
                    @auth
                        <div id="answer-form" class="mb-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                            <form action="{{ route('answers.store', $question) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="content" class="form-label">Your Answer</label>
                                    <textarea 
                                        name="content" 
                                        id="content" 
                                        class="form-control" 
                                        rows="5" 
                                        placeholder="Write your answer here..."
                                        required
                                        minlength="10"
                                        maxlength="5000"
                                    ></textarea>
                                    <small class="text-muted">Minimum 10 characters, maximum 5000 characters.</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-paper-plane mr-1"></i> Submit Answer
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleAnswerForm()">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg text-center">
                            <p class="text-gray-600">
                                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a> 
                                to add an answer to this question.
                            </p>
                        </div>
                    @endauth

                    <!-- Answers List -->
                    @if($question->answers->count() > 0)
                        <div class="answers-list space-y-4">
                            @foreach($question->answers->sortByDesc('is_accepted')->sortByDesc('created_at') as $answer)
                                <div class="answer-item p-4 bg-white rounded-lg border {{ $answer->is_accepted ? 'border-green-300 bg-green-50' : 'border-gray-200' }}">
                                    @if($answer->is_accepted)
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Accepted Answer
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="answer-header mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $answer->user->name }}</strong>
                                                <small class="text-muted ms-2">
                                                    {{ $answer->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            @if(auth()->check() && auth()->id() === $answer->user_id)
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('answers.destroy', $answer) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" 
                                                                        onclick="return confirm('Are you sure you want to delete this answer?')">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="answer-content mb-3">
                                        <p class="text-gray-700">{{ $answer->content }}</p>
                                    </div>
                                    
                                    <!-- Answer Interactions -->
                                    <livewire:answer-interactions :answer="$answer" :key="'answer-'.$answer->id" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-comments fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No answers yet. Be the first to answer this question!</p>
                        </div>
                    @endif
                </div>
                
                <div class="px-4 sm:px-0"></div>
            </div>
        </main>

        @include('partials.aside-right')
    </div>
    @include('layouts.footer')
    
    <script>
        function toggleAnswerForm() {
            const form = document.getElementById('answer-form');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                document.getElementById('content').focus();
            } else {
                form.style.display = 'none';
                document.getElementById('content').value = '';
            }
        }
    </script>
</x-app-layout>
