<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add indexes to improve query performance for frequently accessed columns
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Index for filtering by status
            $table->index('status');
            // Index for sorting by created_at (most recent questions)
            $table->index('created_at');
            // Composite index for user's questions
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('answers', function (Blueprint $table) {
            // Index for finding accepted answers
            $table->index('is_accepted');
            // Composite index for question's answers sorted by date
            $table->index(['question_id', 'created_at']);
            // Index for user's answers
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('solutions', function (Blueprint $table) {
            // Index for sorting by created_at
            $table->index('created_at');
            // Index for filtering by duration_type
            $table->index('duration_type');
            // Composite index for user's solutions
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            // Composite index for polymorphic relationship queries
            $table->index(['commentable_type', 'commentable_id', 'created_at']);
            // Index for user's comments
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('likes', function (Blueprint $table) {
            // Note: morphs() already creates index on ['likable_type', 'likable_id']
            // Unique index to prevent duplicate likes
            $table->unique(['user_id', 'likable_type', 'likable_id'], 'unique_user_like');
        });

        Schema::table('steps', function (Blueprint $table) {
            // Composite index for solution's steps
            $table->index(['solution_id', 'created_at']);
        });

        Schema::table('associates', function (Blueprint $table) {
            // Index for finding user's associates
            $table->index('user_id');
            $table->index('associate_id');
            // Unique constraint to prevent duplicate associations
            $table->unique(['user_id', 'associate_id']);
        });

        Schema::table('followers', function (Blueprint $table) {
            // Indexes for follower relationships
            $table->index('user_id');
            $table->index('following_id');
            // Unique constraint to prevent duplicate follows
            $table->unique(['user_id', 'following_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropIndex(['is_accepted']);
            $table->dropIndex(['question_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('solutions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['duration_type']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('likes', function (Blueprint $table) {
            // Note: morphs() already creates the index, so we only drop the unique constraint
            $table->dropUnique('unique_user_like');
        });

        Schema::table('steps', function (Blueprint $table) {
            $table->dropIndex(['solution_id', 'created_at']);
        });

        Schema::table('associates', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['associate_id']);
            $table->dropUnique(['user_id', 'associate_id']);
        });

        Schema::table('followers', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['following_id']);
            $table->dropUnique(['user_id', 'following_id']);
        });
    }
};
