<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlotRequest;
use App\Models\Garage;
use App\Models\Slot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SlotController extends Controller
{
    public function store(SlotRequest $request, Garage $garage): RedirectResponse
    {
        Gate::authorize('manage', $garage);

        $garage->slots()->create($request->validated());

        return back()->with('ok', 'Slot ditambahkan.');
    }

    // Slot tidak dihapus supaya riwayat booking tetap utuh, cukup dinonaktifkan.
    public function toggle(Slot $slot): RedirectResponse
    {
        Gate::authorize('manage', $slot->garage);

        $slot->update(['is_active' => ! $slot->is_active]);

        return back()->with('ok', 'Status slot diubah.');
    }
}
