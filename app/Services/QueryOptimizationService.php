<?php

namespace App\Services;

use App\Models\Questions;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    /**
     * Get popular questions with caching
     *
     * @param int $limit
     * @param int $ttl Cache time in seconds (default: 5 minutes)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularQuestions(int $limit = 10, int $ttl = 300)
    {
        return Cache::tags(['questions', 'popular'])->remember(
            "popular_questions:{$limit}",
            $ttl,
            function () use ($limit) {
                return Questions::query()
                    ->with(['user:id,name,profile_photo_path'])
                    ->withCount(['answers', 'likes'])
                    ->orderByDesc('answers_count')
                    ->orderByDesc('likes_count')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get recent questions with caching
     *
     * @param int $limit
     * @param int $ttl Cache time in seconds (default: 1 minute)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentQuestions(int $limit = 10, int $ttl = 60)
    {
        return Cache::tags(['questions', 'recent'])->remember(
            "recent_questions:{$limit}",
            $ttl,
            function () use ($limit) {
                return Questions::query()
                    ->with(['user:id,name,profile_photo_path'])
                    ->withCount('answers')
                    ->latest()
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get popular solutions with caching
     *
     * @param int $limit
     * @param int $ttl Cache time in seconds (default: 5 minutes)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularSolutions(int $limit = 10, int $ttl = 300)
    {
        return Cache::tags(['solutions', 'popular'])->remember(
            "popular_solutions:{$limit}",
            $ttl,
            function () use ($limit) {
                return Solutions::query()
                    ->with(['user:id,name,profile_photo_path'])
                    ->withCount(['likes', 'comments'])
                    ->orderByDesc('likes_count')
                    ->orderByDesc('comments_count')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get user profile with cached statistics
     *
     * @param int $userId
     * @param int $ttl Cache time in seconds (default: 10 minutes)
     * @return array
     */
    public function getUserProfileWithStats(int $userId, int $ttl = 600): array
    {
        return Cache::tags(['users', "user:{$userId}"])->remember(
            "user_profile_stats:{$userId}",
            $ttl,
            function () use ($userId) {
                $user = User::with([
                    'questions' => fn($q) => $q->latest()->limit(5),
                    'solutions' => fn($q) => $q->latest()->limit(5),
                    'answers' => fn($q) => $q->latest()->limit(5),
                ])->findOrFail($userId);

                return [
                    'user' => $user,
                    'stats' => [
                        'questions_count' => $user->questions()->count(),
                        'solutions_count' => $user->solutions()->count(),
                        'answers_count' => $user->answers()->count(),
                        'followers_count' => $user->followers()->count(),
                        'following_count' => $user->following()->count(),
                    ],
                ];
            }
        );
    }

    /**
     * Get trending tags with caching
     *
     * @param int $limit
     * @param int $ttl Cache time in seconds (default: 1 hour)
     * @return array
     */
    public function getTrendingTags(int $limit = 20, int $ttl = 3600): array
    {
        return Cache::tags(['tags', 'trending'])->remember(
            "trending_tags:{$limit}",
            $ttl,
            function () use ($limit) {
                // Get tags from questions
                $questionTags = DB::table('questions')
                    ->whereNotNull('tags')
                    ->where('tags', '!=', '')
                    ->pluck('tags');

                // Get tags from solutions
                $solutionTags = DB::table('solutions')
                    ->whereNotNull('tags')
                    ->where('tags', '!=', '')
                    ->pluck('tags');

                // Combine and count tags
                $allTags = $questionTags->merge($solutionTags);
                $tagCounts = [];

                foreach ($allTags as $tagString) {
                    $tags = array_map('trim', explode(',', $tagString));
                    foreach ($tags as $tag) {
                        if (!empty($tag)) {
                            $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                        }
                    }
                }

                // Sort by count and return top tags
                arsort($tagCounts);
                return array_slice($tagCounts, 0, $limit, true);
            }
        );
    }

    /**
     * Process large dataset in chunks to avoid memory issues
     *
     * @param string $model Model class name
     * @param callable $callback Processing callback
     * @param int $chunkSize Number of records per chunk
     * @return void
     */
    public function processInChunks(string $model, callable $callback, int $chunkSize = 100): void
    {
        $model::query()->chunk($chunkSize, function ($records) use ($callback) {
            foreach ($records as $record) {
                $callback($record);
            }
        });
    }

    /**
     * Clear all cached queries
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        Cache::tags(['questions', 'solutions', 'users', 'popular', 'recent', 'trending', 'tags'])->flush();
    }

    /**
     * Clear cache for specific model type
     *
     * @param string $modelType
     * @return void
     */
    public function clearModelCache(string $modelType): void
    {
        Cache::tags([$modelType])->flush();
    }
}
