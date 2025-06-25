<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Может ли пользователь просматривать конкретного клиента.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * Может ли пользователь обновлять клиента.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * Может ли пользователь удалять клиента.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    // Остальные методы (create, restore, forceDelete) нам пока не нужны,
    // можешь их оставить или удалить.
}