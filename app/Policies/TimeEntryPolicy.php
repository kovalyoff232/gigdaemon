<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeEntryPolicy
{
    private function isOwner(User $user, TimeEntry $timeEntry): bool
    {
        return $user->id === $timeEntry->user_id;
    }

    public function view(User $user, TimeEntry $timeEntry): bool
    {
        return $this->isOwner($user, $timeEntry);
    }

    public function update(User $user, TimeEntry $timeEntry): bool
    {
        return $this->isOwner($user, $timeEntry);
    }

    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        return $this->isOwner($user, $timeEntry);
    }
}