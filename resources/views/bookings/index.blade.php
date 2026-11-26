@extends('layouts.app')
@section('title', 'Pengajuan Saya')
@section('content')
<h1 class="h3 mb-3">Pengajuan saya</h1>

@forelse ($bookings as $booking)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="h5 mb-1">{{ $booking->slot->garage->name }} &middot; Slot {{ $booking->slot->code }}</h2>
                    <div class="text-body-secondary">
                        {{ $booking->start_date->translatedFormat('d M Y') }} - {{ $booking->end_date->translatedFormat('d M Y') }}
                        &middot; {{ $booking->plate }} ({{ $booking->car_type }})
                    </div>
                    <div>Total: Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                </div>
                <div>@include('partials.status-badge', ['booking' => $booking])</div>
            </div>

            @if ($booking->expires_at && in_array($booking->status, ['menunggu_konfirmasi', 'menunggu_bayar']))
                <p class="small text-warning-emphasis mt-2 mb-0">
                    Batas waktu: {{ $booking->expires_at->translatedFormat('d M Y H:i') }}. Lewat dari itu pengajuan batal otomatis.
                </p>
            @endif

            @if ($booking->status === 'ditolak')
                <p class="mt-2 mb-0">Alasan penolakan: {{ $booking->reject_reason }}</p>
            @endif

            @if ($booking->status === 'menunggu_konfirmasi')
                <form class="mt-3" method="POST" action="{{ route('bookings.cancel', $booking) }}"
                      onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf
                    <button class="btn btn-outline-danger">Batalkan pengajuan</button>
                </form>
            @endif

            @if ($booking->status === 'menunggu_bayar')
                <p class="mt-3 mb-2">Pemilik sudah menerima. Transfer lalu unggah bukti bayar (JPG, PNG, atau PDF, maks. 2 MB).</p>
                <form class="d-flex flex-wrap gap-2" method="POST" action="{{ route('bookings.proof', $booking) }}" enctype="multipart/form-data">
                    @csrf
                    <input class="form-control w-auto" type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                    <button class="btn btn-primary">Unggah bukti</button>
                </form>
                @if ($booking->payment_proof_path)
                    <p class="small mt-2 mb-0">Bukti sudah terkirim, menunggu verifikasi.
                        <a href="{{ route('files.proof', $booking) }}" target="_blank">Lihat bukti</a></p>
                @endif
            @endif
        </div>
    </div>
@empty
    <p class="text-body-secondary">Belum ada pengajuan. <a href="{{ route('garages.index') }}">Lihat slot</a></p>
@endforelse

{{ $bookings->links('pagination::bootstrap-5') }}
@endsection
