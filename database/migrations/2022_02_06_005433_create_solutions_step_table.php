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
        Schema::create('solutions_step', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('solution_id')->constrained()->onDelete('cascade');
            $table->string('solution_heading');
            $table->longText('solution_body');
            $table->timestamps();
        });

        // Only add FULLTEXT index for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE solutions_step ADD FULLTEXT fulltext_index(solution_heading, solution_body)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solutions_step');
    }
};
