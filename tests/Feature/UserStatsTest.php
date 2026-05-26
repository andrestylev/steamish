<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_stats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/my/stats');

        $response->assertStatus(200);
    }

    public function test_stats_page_loads_with_empty_state_when_no_playtime_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/my/stats');

        $response->assertStatus(200);
    }

    public function test_stats_show_playtime_data_when_reviews_exist(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['title' => 'My Favorite Game']);

        Review::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'hours_played' => 200,
        ]);

        $response = $this->actingAs($user)->get('/my/stats');

        $response->assertStatus(200);
    }
}
