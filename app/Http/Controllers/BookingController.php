<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UploadProofRequest;
use App\Models\Booking;
use App\Models\Garage;
use App\Models\Slot;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index()
    {
        $bookings = auth()->user()->bookings()
            ->with('slot.garage')
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function store(StoreBookingRequest $request, Garage $garage): RedirectResponse
    {
        $slot = Slot::where('garage_id', $garage->id)->findOrFail($request->slot_id);

        $this->bookings->create($request->user(), $slot, $request->validated());

        return redirect()->route('bookings.index')
            ->with('ok', 'Pengajuan terkirim. Pemilik punya waktu 1x24 jam untuk menjawab.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        $this->bookings->cancel($booking);

        return back()->with('ok', 'Pengajuan dibatalkan.');
    }

    public function uploadProof(UploadProofRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $this->bookings->uploadProof($booking, $request->file('proof'));

        return back()->with('ok', 'Bukti bayar terkirim, tunggu verifikasi pemilik.');
    }

    public function proof(Booking $booking)
    {
        Gate::authorize('view', $booking);
        abort_unless($booking->payment_proof_path, 404);

        return Storage::response($booking->payment_proof_path);
    }
}
