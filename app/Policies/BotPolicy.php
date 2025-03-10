<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BotPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Bot $bot)
    {
        return $user->id === $bot->user_id || $bot->is_public;
    }

    public function update(User $user, Bot $bot)
    {
        return $user->id === $bot->user_id;
    }

    public function delete(User $user, Bot $bot)
    {
        return $user->id === $bot->user_id;
    }
}
