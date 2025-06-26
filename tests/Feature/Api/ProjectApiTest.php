<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        // Создаем одного пользователя и одного его клиента
        // перед каждым тестом в этом классе.
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function an_authenticated_user_can_create_a_project_for_their_client(): void
    {
        $projectData = [
            'title' => 'Разработка нового логотипа',
            'client_id' => $this->client->id,
            'description' => 'Нужно сделать красиво.',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Разработка нового логотипа']);

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'title' => 'Разработка нового логотипа',
        ]);
    }

    /** @test */
    public function an_authenticated_user_can_update_their_own_project(): void
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id
        ]);

        $updateData = ['title' => 'Сверхсекретная новая задача'];

        $response = $this->actingAs($this->user)->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('projects', $updateData);
    }

    /** @test */
    public function a_user_cannot_update_another_users_project(): void
    {
        // Создаем другого пользователя и его проект
        $otherUser = User::factory()->create();
        $otherUsersProject = Project::factory()->create(['user_id' => $otherUser->id]);

        $updateData = ['title' => 'Попытка взлома'];

        // Пытаемся обновить чужой проект от имени нашего пользователя
        $response = $this->actingAs($this->user)->putJson("/api/projects/{$otherUsersProject->id}", $updateData);

        // Мы ожидаем отказ в доступе!
        $response->assertStatus(403);
    }

    /** @test */
    public function an_authenticated_user_can_delete_their_project(): void
    {
        $project = Project::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/projects/{$project->id}");

        // Успешный ответ без тела
        $response->assertStatus(204);

        // Проверяем, что проект действительно исчез из базы
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /** @test */
    public function a_user_cannot_create_a_project_for_another_users_client(): void
    {
        // Создаем другого пользователя и его клиента
        $otherUser = User::factory()->create();
        $otherUsersClient = Client::factory()->create(['user_id' => $otherUser->id]);

        $projectData = [
            'title' => 'Проект для чужого клиента',
            'client_id' => $otherUsersClient->id, // Пытаемся привязать к чужому клиенту
        ];

        // Отправляем запрос от имени нашего пользователя
        $response = $this->actingAs($this->user)->postJson('/api/projects', $projectData);

        // Мы ожидаем ошибку валидации (422), так как client_id не пройдет проверку в StoreProjectRequest
        $response->assertStatus(422)
                 ->assertJsonValidationErrors('client_id');
    }
}