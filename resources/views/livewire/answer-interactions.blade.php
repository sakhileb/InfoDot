<div class="answer-interactions mt-3">
    <div class="d-flex align-items-center gap-3">
        <!-- Like/Dislike Buttons -->
        <div class="d-flex align-items-center gap-2">
            <button 
                wire:click="toggleLike" 
                class="btn btn-sm {{ $userLiked ? 'btn-success' : 'btn-outline-success' }}"
                title="Like this answer"
            >
                <i class="fas fa-thumbs-up"></i>
                <span class="ms-1">{{ $likesCount }}</span>
            </button>
            
            <button 
                wire:click="toggleDislike" 
                class="btn btn-sm {{ $userDisliked ? 'btn-danger' : 'btn-outline-danger' }}"
                title="Dislike this answer"
            >
                <i class="fas fa-thumbs-down"></i>
                <span class="ms-1">{{ $dislikesCount }}</span>
            </button>
        </div>

        <!-- Comments Button -->
        <button 
            wire:click="toggleComments" 
            class="btn btn-sm btn-outline-primary"
            title="View comments"
        >
            <i class="fas fa-comment"></i>
            <span class="ms-1">{{ $commentsCount }} Comments</span>
        </button>

        <!-- Accept Answer Button (only for question author) -->
        @if(auth()->check() && auth()->id() === $answer->question->user_id)
            <button 
                wire:click="toggleAcceptance" 
                class="btn btn-sm {{ $answer->is_accepted ? 'btn-warning' : 'btn-outline-warning' }}"
                title="{{ $answer->is_accepted ? 'Unaccept this answer' : 'Accept this answer' }}"
            >
                <i class="fas fa-check"></i>
                {{ $answer->is_accepted ? 'Accepted' : 'Accept' }}
            </button>
        @elseif($answer->is_accepted)
            <span class="badge bg-success">
                <i class="fas fa-check"></i> Accepted Answer
            </span>
        @endif
    </div>

    <!-- Comments Section -->
    @if($showComments)
        <div class="comments-section mt-3 p-3 bg-light rounded">
            <h6>Comments</h6>
            
            <!-- Add Comment Form -->
            @auth
                <div class="mb-3">
                    <div class="input-group">
                        <input 
                            type="text" 
                            wire:model="newComment" 
                            class="form-control" 
                            placeholder="Add a comment..."
                            maxlength="1000"
                        >
                        <button 
                            wire:click="addComment" 
                            class="btn btn-primary"
                            {{ empty($newComment) ? 'disabled' : '' }}
                        >
                            Post
                        </button>
                    </div>
                    @error('newComment') 
                        <small class="text-danger">{{ $message }}</small> 
                    @enderror
                </div>
            @else
                <p class="text-muted mb-3">
                    <a href="{{ route('login') }}">Login</a> to add a comment.
                </p>
            @endauth

            <!-- Comments List -->
            @if(count($comments) > 0)
                <div class="comments-list">
                    @foreach($comments as $comment)
                        <div class="comment mb-2 p-2 bg-white rounded border">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $comment['user']['name'] ?? 'Anonymous' }}</strong>
                                    <small class="text-muted ms-2">
                                        {{ \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            <p class="mb-0 mt-1">{{ $comment['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No comments yet. Be the first to comment!</p>
            @endif
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>
