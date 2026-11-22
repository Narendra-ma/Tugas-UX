@php
    $color = [
        'menunggu_konfirmasi' => 'warning',
        'menunggu_bayar' => 'info',
        'dibayar' => 'success',
        'selesai' => 'secondary',
        'batal' => 'secondary',
        'ditolak' => 'danger',
        'diputus' => 'danger',
    ][$booking->status] ?? 'secondary';
@endphp
<span class="badge text-bg-{{ $color }}">{{ $booking->statusLabel() }}</span>
