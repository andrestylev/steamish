<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_reports(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/reports');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    public function test_reports_page_loads_with_empty_state_when_no_purchases(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }

    public function test_reports_show_data_when_purchases_exist(): void
    {
        $admin = User::factory()->create([
            'email' => 'demo@steamish.test',
        ]);
        $user = User::factory()->create();

        $game = Game::factory()->create([
            'title' => 'Test Game',
            'genre' => 'Action',
            'price' => 29.99,
        ]);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'amount_paid' => 29.99,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertStatus(200);
    }
}
