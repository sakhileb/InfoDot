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
        Schema::create('solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('solution_title')->nullable();
            $table->longText('solution_description')->nullable();
            $table->string('tags')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('duration_type', ['hours', 'days', 'weeks', 'months', 'years', 'infinite'])->nullable();
            $table->integer('steps')->nullable();
            $table->timestamps();
        });

        // Only add FULLTEXT index for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE solutions ADD FULLTEXT fulltext_index(solution_title, solution_description, tags)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solutions');
    }
};
