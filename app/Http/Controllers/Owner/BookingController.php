<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectBookingRequest;
use App\Http\Requests\TerminateBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings)
    {
    }

    public function index(Request $request)
    {
        $ownerId = $request->user()->id;
        $status = $request->query('status');

        $list = Booking::forOwner($ownerId)
            ->with(['user', 'slot.garage'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('owner.bookings.index', [
            'bookings' => $list,
            'status' => $status,
            'flags' => $this->bookings->flagsFor($ownerId),
        ]);
    }

    public function accept(Booking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);
        $this->bookings->accept($booking);

        return back()->with('ok', 'Pengajuan diterima. Penyewa punya 1x24 jam untuk membayar.');
    }

    public function reject(RejectBookingRequest $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);
        $this->bookings->reject($booking, $request->reason);

        return back()->with('ok', 'Pengajuan ditolak dan alasan akan terlihat oleh penyewa.');
    }

    public function pay(Booking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);
        $this->bookings->markPaid($booking);

        return back()->with('ok', 'Pembayaran dicatat. Slot sekarang terisi.');
    }

    public function finish(Booking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);
        $this->bookings->finish($booking);

        return back()->with('ok', 'Sewa selesai, slot kembali kosong.');
    }

    public function terminate(TerminateBookingRequest $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('manage', $booking);
        $this->bookings->terminate($booking, $request->reason, (int) $request->arrears_amount);

        return back()->with('ok', 'Sewa diakhiri dan penyewa masuk catatan khusus.');
    }
}
