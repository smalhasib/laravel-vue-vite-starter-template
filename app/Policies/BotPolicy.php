<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\User;

class BotPolicy
{
    public function update(User $user, Bot $bot)
    {
        return $user->id === $bot->user_id;
    }

    public function delete(User $user, Bot $bot)
    {
        return $user->id === $bot->user_id;
    }
}
