@extends('layouts.app')
@section('title', 'Garasi & Slot')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Garasi &amp; slot saya</h1>
    @if ($garages->isEmpty())
        <a class="btn btn-primary" href="{{ route('owner.garages.create') }}">Tambah garasi</a>
    @endif
</div>

@forelse ($garages as $garage)
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between flex-wrap gap-2">
            <div>
                <h2 class="h5 mb-1">{{ $garage->name }} @unless ($garage->is_active)<span class="badge text-bg-secondary">Nonaktif</span>@endunless</h2>
                <div class="text-body-secondary">{{ $garage->city }} &middot; {{ $garage->slots->where('is_active', true)->count() }} slot aktif</div>
                <div>Rp {{ number_format($garage->price_monthly, 0, ',', '.') }} / bulan</div>
            </div>
            <div><a class="btn btn-outline-primary" href="{{ route('owner.garages.edit', $garage) }}">Ubah</a></div>
        </div>
    </div>
@empty
    <p class="text-body-secondary">Belum ada garasi.</p>
@endforelse
@endsection
