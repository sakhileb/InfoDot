<?php

namespace App\Http\Controllers\Traits;

use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait EagerLoadingOptimizer
{
    /**
     * Get optimized question query with eager loading
     */
    protected function getOptimizedQuestionsQuery(): Builder
    {
        return Questions::with(['user', 'answers.user'])
            ->withCount([
                'answers as answers_count',
                'likes as likes_count' => function ($query) {
                    $query->where('like', true);
                },
                'comments as comments_count'
            ]);
    }

    /**
     * Get optimized answer query with eager loading
     */
    protected function getOptimizedAnswersQuery(): Builder
    {
        return Answer::with(['user', 'question'])
            ->withCount([
                'likes as likes_count' => function ($query) {
                    $query->where('like', true);
                },
                'likes as dislikes_count' => function ($query) {
                    $query->where('like', false);
                },
                'comments as comments_count'
            ]);
    }

    /**
     * Get optimized solution query with eager loading
     */
    protected function getOptimizedSolutionsQuery(): Builder
    {
        return Solutions::with(['user', 'steps'])
            ->withCount([
                'steps as steps_count',
                'comments as comments_count',
                'likes as likes_count' => function ($query) {
                    $query->where('like', true);
                }
            ]);
    }

    /**
     * Get optimized user query with eager loading
     */
    protected function getOptimizedUsersQuery(): Builder
    {
        return User::with(['questions', 'solutions', 'associates'])
            ->withCount([
                'questions as questions_count',
                'solutions as solutions_count',
                'followers as followers_count',
                'following as following_count'
            ]);
    }

    /**
     * Get optimized comments query with eager loading
     */
    protected function getOptimizedCommentsQuery(): Builder
    {
        return Comment::with(['user']);
    }

    /**
     * Load relationships efficiently for a model instance
     */
    protected function loadRelationshipsEfficiently(Model $model, array $relationships = []): Model
    {
        if (empty($relationships)) {
            // Default relationships based on model type
            $modelClass = get_class($model);
            
            $relationships = match ($modelClass) {
                Questions::class => ['user', 'answers.user'],
                Answer::class => ['user', 'question'],
                Solutions::class => ['user', 'steps'],
                User::class => ['questions', 'solutions', 'associates'],
                default => [],
            };
        }

        if (!empty($relationships)) {
            $model->load($relationships);
        }

        return $model;
    }

    /**
     * Apply standard eager loading for collections
     */
    protected function applyStandardEagerLoading(Builder $query, string $modelType): Builder
    {
        return match ($modelType) {
            'questions' => $query->with(['user', 'answers.user'])
                ->withCount(['answers', 'likes', 'comments']),
            
            'answers' => $query->with(['user', 'question'])
                ->withCount(['likes', 'comments']),
            
            'solutions' => $query->with(['user', 'steps'])
                ->withCount(['steps', 'likes', 'comments']),
            
            'users' => $query->with(['questions', 'solutions'])
                ->withCount(['questions', 'solutions', 'followers', 'following']),
            
            default => $query,
        };
    }
}
