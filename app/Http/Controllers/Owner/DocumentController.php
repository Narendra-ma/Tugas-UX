<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Booking;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->id;
        $q = $request->query('q');
        $category = $request->query('category');

        $documents = Document::query()
            ->whereHas('booking', fn ($b) => $b->forOwner($ownerId))
            ->with('booking.user', 'booking.slot')
            ->when($category, fn ($d) => $d->where('category', $category))
            ->when($q, fn ($d) => $d->whereHas('booking', fn ($b) => $b->where(fn ($w) => $w
                ->where('plate', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                ->orWhereHas('slot', fn ($s) => $s->where('code', 'like', "%{$q}%")))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $bookings = Booking::forOwner($ownerId)->with('user', 'slot')
            ->whereIn('status', ['dibayar', 'menunggu_bayar', 'selesai'])
            ->latest()->limit(100)->get();

        return view('owner.documents.index', compact('documents', 'bookings', 'q', 'category'));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        // cari lewat forOwner supaya pemilik tidak bisa menempel surat ke booking orang lain
        $booking = Booking::forOwner($request->user()->id)->findOrFail($request->booking_id);

        $booking->documents()->create([
            'category' => $request->category,
            'path' => $request->file('file')->store('documents'),
            'original_name' => $request->file('file')->getClientOriginalName(),
        ]);

        return back()->with('ok', 'Surat tersimpan.');
    }
}
