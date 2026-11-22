@extends('layouts.app')
@section('title', 'Cari Garasi')
@section('content')
<h1 class="h3 mb-3">Cari garasi sewa</h1>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <label class="form-label" for="city">Lokasi / kota</label>
        <input class="form-control" id="city" name="city" value="{{ request('city') }}" placeholder="Surabaya, Karangasem...">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="min_price">Harga bulanan dari</label>
        <input class="form-control" type="number" min="0" step="10000" id="min_price" name="min_price" value="{{ request('min_price') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="max_price">sampai</label>
        <input class="form-control" type="number" min="0" step="10000" id="max_price" name="max_price" value="{{ request('max_price') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="start_date">Tanggal mulai</label>
        <input class="form-control" type="date" id="start_date" name="start_date" value="{{ request('start_date') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="end_date">Tanggal selesai</label>
        <input class="form-control" type="date" id="end_date" name="end_date" value="{{ request('end_date') }}">
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Cari</button>
    </div>
</form>

<div class="row g-3">
    @forelse ($garages as $garage)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                @if ($garage->photo_path)
                    <img class="card-img-top garage-img" src="{{ asset('storage/'.$garage->photo_path) }}" alt="Foto {{ $garage->name }}">
                @else
                    <div class="card-img-top garage-img d-flex align-items-center justify-content-center text-body-secondary">Belum ada foto</div>
                @endif
                <div class="card-body">
                    <h2 class="h5 card-title">{{ $garage->name }}</h2>
                    <p class="text-body-secondary mb-1">{{ $garage->city }} &middot; {{ $garage->active_slots_count }} slot</p>
                    <p class="mb-1">Rp {{ number_format($garage->price_monthly, 0, ',', '.') }} / bulan</p>
                    <p class="small text-body-secondary mb-0">
                        Rp {{ number_format($garage->price_daily, 0, ',', '.') }} / hari &middot;
                        Rp {{ number_format($garage->price_weekly, 0, ',', '.') }} / minggu
                    </p>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a class="btn btn-outline-primary w-100"
                       href="{{ route('garages.show', ['garage' => $garage, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}">Lihat slot</a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-body-secondary">Tidak ada garasi yang cocok. Coba ubah filter.</p>
    @endforelse
</div>

<div class="mt-4">{{ $garages->links('pagination::bootstrap-5') }}</div>
@endsection
