<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterUser()
    {
        $response = $this->postJson('/api/users/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Super Admin',
            'addresses' => [
                [
                    'address_line' => '123 Main St',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip_code' => '10001',
                    'checkpoint' => true
                ]
            ]
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com'
        ]);
        $this->assertDatabaseHas('addresses', [
            'city' => 'New York'
        ]);
    }

    public function testUpdateUser()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Super Admin',
        ]);

        $response = $this->putJson('/api/users/' . $user->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    public function testDeleteUser()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'Super Admin',
        ]);

        $response = $this->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function testUnauthorizedUserCannotUpdate()
    {
        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'role' => 'User',
        ]);

        $response = $this->actingAs($user)->putJson('/api/users/' . $user->id, [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(403); // Unauthorized
    }
}
