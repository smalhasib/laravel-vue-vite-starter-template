<?php

namespace App\Policies;

use App\Models\Source;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the source.
     */
    public function view(User $user, Source $source): bool
    {
        return $user->id === $source->user_id;
    }

    /**
     * Determine whether the user can update the source.
     */
    public function update(User $user, Source $source): bool
    {
        return $user->id === $source->user_id;
    }

    /**
     * Determine whether the user can delete the source.
     */
    public function delete(User $user, Source $source): bool
    {
        return $user->id === $source->user_id;
    }
}
