<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Questions;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Controllers\Traits\ApiResourceHandler;
use App\Http\Controllers\Traits\EagerLoadingOptimizer;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\CommentResource;

class AnswerController extends Controller
{
    use ApiResourceHandler, EagerLoadingOptimizer;

    /**
     * Display a listing of answers.
     */
    public function index(): JsonResponse
    {
        // Use optimized query with eager loading
        $answers = $this->getOptimizedAnswersQuery()
            ->latest()
            ->paginate(15);
        
        return $this->respondWithCollection(AnswerResource::collection($answers));
    }

    /**
     * Store a newly created answer in storage.
     */
    public function store(StoreAnswerRequest $request): JsonResponse
    {
        $answer = Answer::create([
            'user_id' => auth()->id(),
            'question_id' => $request->question_id,
            'content' => $request->content,
            'is_accepted' => $request->has('is_accepted') ? $request->is_accepted : false,
        ]);

        // Load relationships
        $answer->load(['user', 'question']);

        return $this->respondWithResource(new AnswerResource($answer), 'Answer created successfully', 201);
    }

    /**
     * Display the specified answer.
     */
    public function show(Answer $answer): JsonResponse
    {
        $answer->load(['user', 'question'])
            ->loadCount([
                'likes as likes_count' => fn($query) => $query->where('like', true),
                'likes as dislikes_count' => fn($query) => $query->where('like', false),
                'comments as comments_count',
            ]);
        
        return $this->respondWithResource(new AnswerResource($answer));
    }

    /**
     * Update the specified answer in storage.
     */
    public function update(UpdateAnswerRequest $request, Answer $answer): JsonResponse
    {
        // Check if user owns the answer
        if ($answer->user_id !== auth()->id()) {
            return $this->respondWithError('Unauthorized', 403);
        }

        $answer->update($request->only(['content', 'is_accepted']));
        $answer->load(['user', 'question']);

        return $this->respondWithResource(new AnswerResource($answer), 'Answer updated successfully');
    }

    /**
     * Remove the specified answer from storage.
     */
    public function destroy(Answer $answer): JsonResponse
    {
        // Check if user owns the answer
        if ($answer->user_id !== auth()->id()) {
            return $this->respondWithError('Unauthorized', 403);
        }

        $answer->delete();

        return $this->respondWithSuccess('Answer deleted successfully');
    }

    /**
     * Get answers for a specific question.
     */
    public function byQuestion(Questions $question): JsonResponse
    {
        // Use optimized query with eager loading
        $answers = $question->answers()
            ->with(['user'])
            ->withCount([
                'likes as likes_count' => fn($query) => $query->where('like', true),
                'likes as dislikes_count' => fn($query) => $query->where('like', false),
                'comments as comments_count',
            ])
            ->latest()
            ->paginate(15);

        return $this->respondWithCollection(AnswerResource::collection($answers));
    }

    /**
     * Like or unlike an answer.
     */
    public function toggleLike(Request $request, Answer $answer): JsonResponse
    {
        $request->validate([
            'like' => 'required|boolean',
        ]);

        $userId = auth()->id();
        $likeValue = $request->like;

        // Check if user already has a like/dislike for this answer
        $existingLike = Like::where('user_id', $userId)
            ->where('likable_type', Answer::class)
            ->where('likable_id', $answer->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->like == $likeValue) {
                // Same action, remove the like/dislike
                $existingLike->delete();
                $action = $likeValue ? 'unliked' : 'undisliked';
            } else {
                // Different action, update the like/dislike
                $existingLike->update(['like' => $likeValue]);
                $action = $likeValue ? 'liked' : 'disliked';
            }
        } else {
            // Create new like/dislike
            Like::create([
                'user_id' => $userId,
                'likable_type' => Answer::class,
                'likable_id' => $answer->id,
                'like' => $likeValue,
            ]);
            $action = $likeValue ? 'liked' : 'disliked';
        }

        // Reload counts
        $answer->loadCount([
            'likes as likes_count' => fn($query) => $query->where('like', true),
            'likes as dislikes_count' => fn($query) => $query->where('like', false),
        ]);

        return $this->respondWithSuccess("Answer {$action} successfully", [
            'action' => $action,
            'likes_count' => $answer->likes_count,
            'dislikes_count' => $answer->dislikes_count,
        ]);
    }

    /**
     * Add a comment to an answer.
     */
    public function addComment(Request $request, Answer $answer): JsonResponse
    {
        $request->validate([
            'body' => 'required|string|min:1|max:1000',
        ], [
            'body.required' => 'Comment body is required.',
            'body.min' => 'Comment must be at least 1 character long.',
            'body.max' => 'Comment cannot exceed 1000 characters.',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => $request->body,
        ]);

        $comment->load('user');

        return $this->respondWithResource(new CommentResource($comment), 'Comment added successfully', 201);
    }

    /**
     * Get comments for an answer.
     */
    public function getComments(Answer $answer): JsonResponse
    {
        $comments = $answer->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        return $this->respondWithCollection(CommentResource::collection($comments));
    }

    /**
     * Accept or unaccept an answer (only question author can do this).
     */
    public function toggleAcceptance(Request $request, Answer $answer): JsonResponse
    {
        $answer->load('question');
        
        // Check if user is the question author
        if ($answer->question->user_id !== auth()->id()) {
            return $this->respondWithError('Only the question author can accept answers', 403);
        }

        // If accepting this answer, unaccept all other answers for this question
        if (!$answer->is_accepted) {
            Answer::where('question_id', $answer->question_id)
                ->where('id', '!=', $answer->id)
                ->update(['is_accepted' => false]);
        }

        // Toggle acceptance
        $answer->update(['is_accepted' => !$answer->is_accepted]);
        $answer->refresh();

        $action = $answer->is_accepted ? 'accepted' : 'unaccepted';

        return $this->respondWithSuccess("Answer {$action} successfully", [
            'is_accepted' => $answer->is_accepted,
        ]);
    }
}
