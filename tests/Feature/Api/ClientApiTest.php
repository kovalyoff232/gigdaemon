<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test; // <-- Добавили это
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Наш первый тест. Имя должно говорить само за себя.
     */
    #[Test] // <-- Заменили /** @test */ на это
    public function an_authenticated_user_can_create_a_client(): void
    {
        // --- Подготовка (Arrange) ---
        $user = User::factory()->create();
        $clientData = [
            'name' => 'ООО "Тестовые Рога и Копыта"',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'default_rate' => 1500,
        ];

        // --- Действие (Act) ---
        $response = $this->actingAs($user)->postJson('/api/clients', $clientData);

        // --- Проверки (Assert) ---
        $response->assertStatus(201);
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'ООО "Тестовые Рога и Копыта"',
            'email' => 'test@example.com'
        ]);
        $response->assertJsonFragment(['name' => 'ООО "Тестовые Рога и Копыта"']);
    }
}