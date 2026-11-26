@extends('layouts.app')
@section('title', 'Ringkasan')
@section('content')
<h1 class="h3 mb-3">Ringkasan</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-body-secondary">Sewa aktif</div><div class="h3 mb-0">{{ $activeCount }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-body-secondary">Slot kosong hari ini</div><div class="h3 mb-0">{{ $freeSlots }} / {{ $totalSlots }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-body-secondary">Pendapatan bulan ini</div><div class="h3 mb-0">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-body-secondary">Menunggu jawaban Anda</div>
        <div class="h3 mb-0"><a href="{{ route('owner.bookings.index', ['status' => 'menunggu_konfirmasi']) }}">{{ $pendingCount }}</a></div>
    </div></div></div>
</div>

<h2 class="h5">Slot hari ini</h2>
<div class="row g-2 mb-4">
    @foreach ($slots as $slot)
        @php
            $b = $slot->bookings->first();
            $state = ! $b ? ['Kosong', 'success'] : ($b->status === 'dibayar' ? ['Terisi', 'danger'] : ['Menunggu', 'warning']);
        @endphp
        <div class="col-6 col-md-4 col-lg-2">
            <div class="border rounded p-2 text-center h-100">
                <div class="h5 mb-1">{{ $slot->code }}</div>
                <span class="badge text-bg-{{ $state[1] }}">{{ $state[0] }}</span>
                @if ($b)<div class="small mt-1">{{ $b->user->name }}<br>{{ $b->plate }}</div>@endif
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-primary" href="{{ route('owner.bookings.index') }}">Lihat pengajuan</a>
    <a class="btn btn-outline-primary" href="{{ route('owner.documents.index') }}">Simpan surat perjanjian</a>
    <a class="btn btn-outline-primary" href="{{ route('owner.garages.index') }}">Atur tarif dan slot</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <h2 class="h5 text-danger">Terlambat ({{ $overdue->count() }})</h2>
        @forelse ($overdue as $b)
            <div class="border rounded p-2 mb-2">
                <strong>{{ $b->user->name }}</strong> &middot; Slot {{ $b->slot->code }} &middot; {{ $b->plate }}<br>
                <span class="small">Jatuh tempo {{ $b->end_date->translatedFormat('d M Y') }}, HP {{ $b->user->phone }}</span>
            </div>
        @empty
            <p class="text-body-secondary">Tidak ada.</p>
        @endforelse

        <h2 class="h5 mt-4">Segera jatuh tempo (3 hari)</h2>
        @forelse ($dueSoon as $b)
            <div class="border rounded p-2 mb-2">
                <strong>{{ $b->user->name }}</strong> &middot; Slot {{ $b->slot->code }} &middot; {{ $b->plate }}<br>
                <span class="small">Jatuh tempo {{ $b->end_date->translatedFormat('d M Y') }}, HP {{ $b->user->phone }}</span>
            </div>
        @empty
            <p class="text-body-secondary">Tidak ada.</p>
        @endforelse
    </div>

    <div class="col-lg-6">
        <h2 class="h5">Catatan khusus (penyewa bermasalah)</h2>
        @forelse ($blacklist as $row)
            <div class="border border-danger rounded p-2 mb-2">
                <strong>{{ $row->name }}</strong> &middot; {{ $row->plate }}<br>
                <span class="small">{{ $row->reason }} &middot; tunggakan Rp {{ number_format($row->arrears_amount, 0, ',', '.') }}</span>
            </div>
        @empty
            <p class="text-body-secondary">Belum ada.</p>
        @endforelse
    </div>
</div>
@endsection
