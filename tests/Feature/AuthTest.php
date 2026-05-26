<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_registration_form(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_guest_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'name' => 'Test User',
        ]);

        $this->assertAuthenticated();
    }

    public function test_guest_can_view_login_form(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_guest_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_guest_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_authenticated_routes(): void
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'username' => 'original_user',
        ]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'username' => 'updated_user',
            'bio' => 'Hello, I am updated!',
            'timezone' => 'America/New_York',
        ]);

        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated_user', $user->username);
        $this->assertEquals('Hello, I am updated!', $user->bio);
    }

    public function test_authenticated_user_receives_validation_errors_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'name' => '',
            'username' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'username']);
    }
}
