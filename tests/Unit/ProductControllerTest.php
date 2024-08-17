<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateProduct()
    {
        $user = User::factory()->create([
            'role' => 'Super Admin'
        ]);

        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'prices' => ['USD' => 99.99, 'EUR' => 89.99],
            'stock_quantity' => 10
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'stock_quantity' => 10
        ]);
    }

    public function testUpdateProduct()
    {
        $user = User::factory()->create([
            'role' => 'Super Admin'
        ]);

        $product = Product::factory()->create([
            'name' => 'Old Product',
            'description' => 'Old description',
            'prices' => json_encode(['USD' => 99.99]),
            'stock_quantity' => 5
        ]);

        $response = $this->actingAs($user)->putJson('/api/products/' . $product->id, [
            'name' => 'New Product',
            'description' => 'New description',
            'prices' => ['USD' => 79.99],
            'stock_quantity' => 15
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'description' => 'New description',
            'stock_quantity' => 15
        ]);
    }

    public function testDeleteProduct()
    {
        $user = User::factory()->create([
            'role' => 'Super Admin'
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product'
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', [
            'name' => 'Test Product'
        ]);
    }

    public function testUnauthorizedUserCannotCreateProduct()
    {
        $user = User::factory()->create([
            'role' => 'User'
        ]);

        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Unauthorized Product',
            'description' => 'This should not be created',
            'prices' => ['USD' => 50.00],
            'stock_quantity' => 10
        ]);

        $response->assertStatus(403); // Unauthorized
        $this->assertDatabaseMissing('products', [
            'name' => 'Unauthorized Product'
        ]);
    }
}

