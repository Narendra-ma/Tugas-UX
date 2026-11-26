<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Booking;
use App\Models\Slot;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $ownerId = auth()->id();
        $with = ['user', 'slot.garage'];

        $totalSlots = Slot::where('is_active', true)
            ->whereHas('garage', fn ($g) => $g->where('owner_id', $ownerId))
            ->count();

        $busySlots = Booking::forOwner($ownerId)->blocking()
            ->overlapping(today()->toDateString(), today()->toDateString())
            ->distinct()->count('slot_id');

        $today = today()->toDateString();
        $slots = Slot::where('is_active', true)
            ->whereHas('garage', fn ($g) => $g->where('owner_id', $ownerId))
            ->with(['bookings' => fn ($b) => $b->blocking()->overlapping($today, $today)->with('user')])
            ->orderBy('code')->get();

        return view('owner.dashboard', [
            'slots' => $slots,
            'activeCount' => Booking::forOwner($ownerId)->active()->count(),
            'pendingCount' => Booking::forOwner($ownerId)->where('status', 'menunggu_konfirmasi')->count(),
            'totalSlots' => $totalSlots,
            'freeSlots' => max($totalSlots - $busySlots, 0),
            'revenue' => Booking::forOwner($ownerId)
                ->whereIn('status', ['dibayar', 'selesai'])
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_price'),
            'dueSoon' => Booking::forOwner($ownerId)->dueSoon()->with($with)->orderBy('end_date')->get(),
            'overdue' => Booking::forOwner($ownerId)->overdue()->with($with)->orderBy('end_date')->get(),
            'blacklist' => Blacklist::where('owner_id', $ownerId)->latest()->limit(5)->get(),
        ]);
    }
}
