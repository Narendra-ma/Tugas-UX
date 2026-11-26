<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\Request;

// Situs ini hanya melayani satu garasi: Garasi Surya.
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

        // eager load booking yang bentrok saja, dipakai displayStatus() di view
        $garage = Garage::active()->with('owner')->orderBy('id')->firstOrFail();
        $garage->load(['slots' => fn ($q) => $q->where('is_active', true)->orderBy('code')
            ->with(['bookings' => fn ($b) => $b->blocking()->overlapping($start, $end)])]);

        return view('garages.landing', compact('garage', 'start', 'end'));
    }
}
