<?php

namespace App\Policies;

use App\Models\Garage;
use App\Models\User;

class GaragePolicy
{
    public function manage(User $user, Garage $garage): bool
    {
        return $user->id === $garage->owner_id;
    }
}
