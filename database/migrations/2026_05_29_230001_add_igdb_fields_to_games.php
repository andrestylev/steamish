<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->decimal('aggregated_rating', 4, 1)->nullable();
            $table->text('storyline')->nullable();
            $table->string('status')->nullable();
            // genre and platforms are already nullable — no change needed
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Drop unique index first (SQLite requires this before dropping the column)
            $table->dropUnique(['igdb_id']);
            $table->dropColumn(['igdb_id', 'aggregated_rating', 'storyline', 'status']);
        });
    }
};
