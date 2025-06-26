<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    // Этот трейт — магия. Он будет полностью очищать и пересоздавать базу данных
    // перед каждым тестом. Это гарантирует, что тесты не влияют друг на друга.
    use RefreshDatabase;

    /**
     * Наш первый тест. Имя должно говорить само за себя.
     * @test
     */
    public function an_authenticated_user_can_create_a_client(): void
    {
        // --- Подготовка (Arrange) ---

        // 1. Мы создаем в тестовой базе данных одного пользователя.
        $user = User::factory()->create();

        // 2. Мы готовим данные, которые якобы пришли из формы.
        $clientData = [
            'name' => 'ООО "Тестовые Рога и Копыта"',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'default_rate' => 1500,
        ];

        // --- Действие (Act) ---

        // 3. Мы "входим в систему" под этим пользователем и отправляем POST-запрос
        // на наш API-эндпоинт, как будто это делает Vue-компонент.
        $response = $this->actingAs($user)->postJson('/api/clients', $clientData);

        // --- Проверки (Assert) ---

        // 4. Мы утверждаем, что сервер должен был ответить статусом 201 (Created).
        // Если он ответит чем-то другим, тест провалится.
        $response->assertStatus(201);

        // 5. Мы утверждаем, что в базе данных, в таблице 'clients',
        // теперь должна быть запись, содержащая те данные, которые мы отправили.
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'ООО "Тестовые Рога и Копыта"',
            'email' => 'test@example.com'
        ]);
        
        // 6. Мы утверждаем, что в JSON-ответе, который прислал сервер,
        // должен быть фрагмент с именем нашего нового клиента.
        $response->assertJsonFragment([
            'name' => 'ООО "Тестовые Рога и Копыта"',
        ]);
    }
}