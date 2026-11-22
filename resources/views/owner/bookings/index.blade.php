@extends('layouts.app')
@section('title', 'Pengajuan')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h1 class="h3 mb-0">Pengajuan &amp; sewa</h1>
    <form method="GET" class="d-flex gap-2">
        <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (\App\Models\Booking::STATUS_LABELS as $key => $name)
                <option value="{{ $key }}" @selected($status === $key)>{{ $name }}</option>
            @endforeach
        </select>
    </form>
</div>

@forelse ($bookings as $booking)
    @php
        $flagged = in_array($booking->plate, $flags['plates']) || in_array($booking->user->phone, $flags['phones']);
    @endphp
    <div class="card mb-3 {{ $flagged ? 'border-danger' : '' }}">
        <div class="card-body">
            @if ($flagged)
                <div class="alert alert-danger py-2">
                    Perhatian: plat atau nomor HP ini ada di catatan khusus (pernah diputus sepihak atau menunggak).
                </div>
            @endif

            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="h5 mb-1">{{ $booking->user->name }} &middot; Slot {{ $booking->slot->code }}</h2>
                    <div class="text-body-secondary">
                        {{ $booking->slot->garage->name }} &middot;
                        {{ $booking->start_date->translatedFormat('d M Y') }} - {{ $booking->end_date->translatedFormat('d M Y') }}
                    </div>
                    <div>{{ $booking->plate }} ({{ $booking->car_type }}) &middot; HP {{ $booking->user->phone }}</div>
                    <div>Total: Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
                </div>
                <div>@include('partials.status-badge', ['booking' => $booking])</div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($booking->status === 'menunggu_konfirmasi')
                    <form method="POST" action="{{ route('owner.bookings.accept', $booking) }}">
                        @csrf
                        <button class="btn btn-success">Terima</button>
                    </form>
                    <form class="d-flex flex-wrap gap-2" method="POST" action="{{ route('owner.bookings.reject', $booking) }}"
                          onsubmit="return confirm('Yakin tolak pengajuan ini?')">
                        @csrf
                        <select class="form-select w-auto" name="reason" required>
                            <option value="">Alasan penolakan</option>
                            <option>Mohon maaf, ukuran mobil tidak sesuai dengan kapasitas slot.</option>
                            <option>Mohon maaf, slot sedang tidak tersedia di tanggal tersebut.</option>
                            <option>Mohon maaf, saat ini kami belum bisa menerima pengajuan ini.</option>
                        </select>
                        <button class="btn btn-outline-danger">Tolak</button>
                    </form>
                @endif

                @if ($booking->status === 'menunggu_bayar')
                    @if ($booking->payment_proof_path)
                        <a class="btn btn-outline-primary" target="_blank" href="{{ route('files.proof', $booking) }}">Lihat bukti bayar</a>
                    @else
                        <span class="align-self-center text-body-secondary">Belum ada bukti bayar.</span>
                    @endif
                    <form method="POST" action="{{ route('owner.bookings.pay', $booking) }}"
                          onsubmit="return confirm('Tandai sudah dibayar?')">
                        @csrf
                        <button class="btn btn-success">Tandai sudah dibayar</button>
                    </form>
                @endif

                @if ($booking->status === 'dibayar')
                    <form method="POST" action="{{ route('owner.bookings.finish', $booking) }}"
                          onsubmit="return confirm('Tandai sewa selesai? Slot akan kembali kosong.')">
                        @csrf
                        <button class="btn btn-outline-secondary">Sewa selesai</button>
                    </form>
                    <form class="d-flex flex-wrap gap-2" method="POST" action="{{ route('owner.bookings.terminate', $booking) }}"
                          onsubmit="return confirm('Akhiri sewa sepihak? Penyewa akan masuk catatan khusus.')">
                        @csrf
                        <input class="form-control w-auto" name="reason" placeholder="Alasan" required>
                        <input class="form-control w-auto" type="number" min="0" name="arrears_amount" placeholder="Tunggakan (Rp)" value="0" required>
                        <button class="btn btn-outline-danger">Akhiri sepihak</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@empty
    <p class="text-body-secondary">Belum ada pengajuan.</p>
@endforelse

{{ $bookings->links('pagination::bootstrap-5') }}
@endsection
