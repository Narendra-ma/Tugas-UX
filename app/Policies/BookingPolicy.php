<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** Pemilik garasi tempat booking itu berada. */
    public function manage(User $user, Booking $booking): bool
    {
        return $user->id === $booking->slot->garage->owner_id;
    }

    /** Penyewa yang bersangkutan atau pemilik garasinya. */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $this->manage($user, $booking);
    }
}
