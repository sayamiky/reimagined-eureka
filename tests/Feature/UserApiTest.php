<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'created_at', 'updated_at']
                ]
            ]);
    }

    #[Test]
    public function it_can_register_a_new_user()
    {
        // fail
        $invalidPayload = [
            'name'                  => 'Invalid User',
            'email'                 => 'invalid@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/register', $invalidPayload);

        $response->assertStatus(422) // Laravel validation error
                 ->assertJsonValidationErrors(['password']);
                 
        // success
        $payload = [
            'name'     => 'Test User',
            'email'    => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com'
        ]);
    }
}
