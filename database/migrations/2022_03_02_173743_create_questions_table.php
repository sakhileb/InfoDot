<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('question')->nullable();
            $table->longText('description')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Only add FULLTEXT index for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE questions ADD FULLTEXT fulltext_index(question, description)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
