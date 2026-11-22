<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\GarageRequest;
use App\Models\Garage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class GarageController extends Controller
{
    public function index()
    {
        $garages = auth()->user()->garages()->with('slots')->get();

        return view('owner.garages.index', compact('garages'));
    }

    public function create()
    {
        return view('owner.garages.form', ['garage' => new Garage()]);
    }

    public function store(GarageRequest $request): RedirectResponse
    {
        $garage = $request->user()->garages()->create($this->data($request));

        return redirect()->route('owner.garages.edit', $garage)->with('ok', 'Garasi tersimpan. Tambahkan slot di bawah.');
    }

    public function edit(Garage $garage)
    {
        Gate::authorize('manage', $garage);

        return view('owner.garages.form', ['garage' => $garage->load('slots')]);
    }

    public function update(GarageRequest $request, Garage $garage): RedirectResponse
    {
        Gate::authorize('manage', $garage);

        $garage->update($this->data($request) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('ok', 'Perubahan tersimpan.');
    }

    private function data(GarageRequest $request): array
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('garages', 'public');
        }

        return $data;
    }
}
