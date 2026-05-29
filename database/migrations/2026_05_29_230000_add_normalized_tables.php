<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalized entity tables
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('abbreviation')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('country')->nullable();
            $table->timestamps();
        });

        // Pivot tables with cascade delete
        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'genre_id']);
        });

        Schema::create('game_platform', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'platform_id']);
        });

        Schema::create('game_tag', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'tag_id']);
        });

        Schema::create('game_company', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('developer');
            $table->primary(['game_id', 'company_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_company');
        Schema::dropIfExists('game_tag');
        Schema::dropIfExists('game_platform');
        Schema::dropIfExists('game_genre');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('genres');
    }
};
