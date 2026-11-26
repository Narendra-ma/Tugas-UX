<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\Request;

class GarageController extends Controller
{
    public function index(Request $request)
    {
        $dates = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $start = $dates['start_date'] ?? today()->toDateString();
        $end = $dates['end_date'] ?? $start;

        $garage = Garage::active()->with('owner')->oldest('id')->firstOrFail();
        $garage->load(['slots' => fn ($q) => $q->where('is_active', true)->orderBy('code')
            ->with(['bookings' => fn ($b) => $b->blocking()->overlapping($start, $end)])]);

        return view('garages.landing', compact('garage', 'start', 'end'));
    }
}
