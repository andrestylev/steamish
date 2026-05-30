<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizedModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_belongs_to_many_games(): void
    {
        $genre = Genre::factory()->create();
        $game = Game::factory()->create();
        $genre->games()->attach($game);

        $this->assertTrue($genre->games->contains($game));
        $this->assertCount(1, $genre->games);
    }

    public function test_genre_can_have_multiple_games(): void
    {
        $genre = Genre::factory()->create();
        $gameA = Game::factory()->create();
        $gameB = Game::factory()->create();
        $genre->games()->attach([$gameA->id, $gameB->id]);

        $this->assertCount(2, $genre->games);
    }

    public function test_platform_belongs_to_many_games(): void
    {
        $platform = Platform::factory()->create();
        $game = Game::factory()->create();
        $platform->games()->attach($game);

        $this->assertTrue($platform->games->contains($game));
        $this->assertCount(1, $platform->games);
    }

    public function test_tag_belongs_to_many_games(): void
    {
        $tag = Tag::factory()->create();
        $game = Game::factory()->create();
        $tag->games()->attach($game);

        $this->assertTrue($tag->games->contains($game));
        $this->assertCount(1, $tag->games);
    }

    public function test_company_belongs_to_many_games_with_role(): void
    {
        $company = Company::factory()->create();
        $game = Game::factory()->create();
        $company->games()->attach($game, ['role' => 'developer']);

        $this->assertTrue($company->games->contains($game));
        $this->assertCount(1, $company->games);
        $this->assertEquals('developer', $company->games->first()->pivot->role);
    }

    public function test_company_role_can_be_publisher(): void
    {
        $company = Company::factory()->create();
        $game = Game::factory()->create();
        $company->games()->attach($game, ['role' => 'publisher']);

        $this->assertEquals('publisher', $company->games->first()->pivot->role);
    }

    public function test_company_pivot_defaults_to_developer(): void
    {
        $company = Company::factory()->create();
        $game = Game::factory()->create();
        $company->games()->attach($game);

        $this->assertEquals('developer', $company->games->first()->pivot->role);
    }

    public function test_genre_has_name_and_slug(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'Action',
            'slug' => 'action',
        ]);

        $this->assertEquals('Action', $genre->name);
        $this->assertEquals('action', $genre->slug);
    }

    public function test_platform_has_abbreviation(): void
    {
        $platform = Platform::factory()->create([
            'name' => 'Windows',
            'slug' => 'windows',
            'abbreviation' => 'WIN',
        ]);

        $this->assertEquals('WIN', $platform->abbreviation);
    }
}
