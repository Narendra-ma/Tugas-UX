<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use Illuminate\Http\Request;

class GarageController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_with:start_date'],
        ]);

        $garages = Garage::active()
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', 'like', "%{$city}%"))
            ->when($filters['min_price'] ?? null, fn ($q, $min) => $q->where('price_monthly', '>=', $min))
            ->when($filters['max_price'] ?? null, fn ($q, $max) => $q->where('price_monthly', '<=', $max))
            ->when(
                ($filters['start_date'] ?? null) && ($filters['end_date'] ?? null),
                fn ($q) => $q->freeBetween($filters['start_date'], $filters['end_date'])
            )
            ->withCount(['slots as active_slots_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('price_monthly')
            ->paginate(9)
            ->withQueryString();

        return view('garages.index', compact('garages'));
    }

    public function show(Request $request, Garage $garage)
    {
        abort_unless($garage->is_active, 404);

        $start = $request->query('start_date', today()->toDateString());
        $end = $request->query('end_date', $start);

        // eager load booking yang bentrok saja, dipakai displayStatus() di view
        $garage->load(['slots' => fn ($q) => $q->where('is_active', true)->orderBy('code')
            ->with(['bookings' => fn ($b) => $b->blocking()->overlapping($start, $end)])]);

        return view('garages.show', compact('garage', 'start', 'end'));
    }
}
