<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Мы не можем просто создать пользователя и клиента.
            // Проект должен принадлежать клиенту, который принадлежит пользователю.
            // Поэтому мы определим их в самих тестах для ясности.
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'title' => $this->faker->bs(),
            'description' => $this->faker->paragraph(),
            'status' => 'active',
        ];
    }
}