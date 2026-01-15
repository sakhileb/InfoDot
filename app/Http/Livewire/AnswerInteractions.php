<?php

namespace App\Http\Livewire;

use App\Models\Answer;
use App\Models\Like;
use App\Models\Comment;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class AnswerInteractions extends Component
{
    public Answer $answer;
    public int $likesCount = 0;
    public int $dislikesCount = 0;
    public int $commentsCount = 0;
    public bool $userLiked = false;
    public bool $userDisliked = false;
    public bool $showComments = false;
    public string $newComment = '';
    public array $comments = [];

    public function mount(Answer $answer): void
    {
        $this->answer = $answer;
        $this->loadCounts();
        $this->loadUserInteractions();
    }

    public function loadCounts(): void
    {
        $this->likesCount = $this->answer->likes()->where('like', true)->count();
        $this->dislikesCount = $this->answer->likes()->where('like', false)->count();
        $this->commentsCount = $this->answer->comments()->count();
    }

    public function loadUserInteractions(): void
    {
        if (Auth::check()) {
            $userLike = $this->answer->likes()
                ->where('user_id', Auth::id())
                ->first();
            
            if ($userLike) {
                $this->userLiked = $userLike->like === true;
                $this->userDisliked = $userLike->like === false;
            }
        }
    }

    public function toggleLike(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $existingLike = Like::where('user_id', Auth::id())
            ->where('likable_type', Answer::class)
            ->where('likable_id', $this->answer->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->like === true) {
                // Remove like
                $existingLike->delete();
                $this->userLiked = false;
            } else {
                // Change dislike to like
                $existingLike->update(['like' => true]);
                $this->userLiked = true;
                $this->userDisliked = false;
            }
        } else {
            // Create new like
            Like::create([
                'user_id' => Auth::id(),
                'likable_type' => Answer::class,
                'likable_id' => $this->answer->id,
                'like' => true
            ]);
            $this->userLiked = true;
        }

        $this->loadCounts();
        $this->dispatch('answer-liked', answerId: $this->answer->id);
    }

    public function toggleDislike(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $existingLike = Like::where('user_id', Auth::id())
            ->where('likable_type', Answer::class)
            ->where('likable_id', $this->answer->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->like === false) {
                // Remove dislike
                $existingLike->delete();
                $this->userDisliked = false;
            } else {
                // Change like to dislike
                $existingLike->update(['like' => false]);
                $this->userDisliked = true;
                $this->userLiked = false;
            }
        } else {
            // Create new dislike
            Like::create([
                'user_id' => Auth::id(),
                'likable_type' => Answer::class,
                'likable_id' => $this->answer->id,
                'like' => false
            ]);
            $this->userDisliked = true;
        }

        $this->loadCounts();
        $this->dispatch('answer-disliked', answerId: $this->answer->id);
    }

    public function toggleComments(): void
    {
        $this->showComments = !$this->showComments;
        
        if ($this->showComments) {
            $this->loadComments();
        }
    }

    public function loadComments(): void
    {
        $this->comments = $this->answer->comments()
            ->with('user')
            ->latest()
            ->get()
            ->toArray();
    }

    public function addComment(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->validate([
            'newComment' => 'required|string|min:1|max:1000'
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'commentable_type' => Answer::class,
            'commentable_id' => $this->answer->id,
            'body' => $this->newComment
        ]);

        $this->newComment = '';
        $this->loadCounts();
        $this->loadComments();
        $this->dispatch('comment-added', answerId: $this->answer->id);
    }

    public function toggleAcceptance(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->answer->load('question');
        
        // Check if user is the question author
        if ($this->answer->question->user_id !== Auth::id()) {
            session()->flash('error', 'Only the question author can accept answers');
            return;
        }

        // If accepting this answer, unaccept all other answers for this question
        if (!$this->answer->is_accepted) {
            Answer::where('question_id', $this->answer->question_id)
                ->where('id', '!=', $this->answer->id)
                ->update(['is_accepted' => false]);
        }

        // Toggle acceptance
        $this->answer->update(['is_accepted' => !$this->answer->is_accepted]);
        $this->answer->refresh();

        $action = $this->answer->is_accepted ? 'accepted' : 'unaccepted';
        session()->flash('success', "Answer {$action} successfully");
        
        $this->dispatch('answer-acceptance-toggled', answerId: $this->answer->id, accepted: $this->answer->is_accepted);
    }

    #[On('refresh-interactions')]
    public function refreshInteractions(): void
    {
        $this->loadCounts();
        $this->loadUserInteractions();
        if ($this->showComments) {
            $this->loadComments();
        }
    }

    public function render()
    {
        return view('livewire.answer-interactions');
    }
}
