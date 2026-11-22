@extends('layouts.app')
@section('title', $garage->exists ? 'Ubah Garasi' : 'Tambah Garasi')
@section('content')
<h1 class="h3 mb-3">{{ $garage->exists ? 'Ubah garasi' : 'Tambah garasi' }}</h1>

<form method="POST" enctype="multipart/form-data"
      action="{{ $garage->exists ? route('owner.garages.update', $garage) : route('owner.garages.store') }}">
    @csrf
    @if ($garage->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Nama garasi</label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $garage->name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="city">Kota / kabupaten</label>
            <input class="form-control" id="city" name="city" value="{{ old('city', $garage->city) }}" required>
        </div>
        <div class="col-12">
            <label class="form-label" for="address">Alamat</label>
            <input class="form-control" id="address" name="address" value="{{ old('address', $garage->address) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="size_note">Ukuran / keterangan slot</label>
            <input class="form-control" id="size_note" name="size_note" value="{{ old('size_note', $garage->size_note) }}" placeholder="Muat 6 mobil, maks. ukuran MPV">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="photo">Foto garasi (maks. 2 MB)</label>
            <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $garage->description) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="price_daily">Harga per hari (Rp)</label>
            <input class="form-control" type="number" min="0" id="price_daily" name="price_daily" value="{{ old('price_daily', $garage->price_daily) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="price_weekly">Harga per minggu (Rp)</label>
            <input class="form-control" type="number" min="0" id="price_weekly" name="price_weekly" value="{{ old('price_weekly', $garage->price_weekly) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="price_monthly">Harga per bulan (Rp)</label>
            <input class="form-control" type="number" min="0" id="price_monthly" name="price_monthly" value="{{ old('price_monthly', $garage->price_monthly) }}" required>
        </div>
        @if ($garage->exists)
            <div class="col-12 form-check ms-2">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $garage->is_active))>
                <label class="form-check-label" for="is_active">Garasi aktif (tampil di pencarian)</label>
            </div>
        @endif
    </div>

    <button class="btn btn-primary mt-3">Simpan</button>
</form>

@if ($garage->exists)
    <hr class="my-4">
    <h2 class="h5">Slot</h2>
    <table class="table align-middle">
        <thead><tr><th>Kode</th><th>Catatan</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($garage->slots as $slot)
            <tr>
                <td>{{ $slot->code }}</td>
                <td>{{ $slot->note }}</td>
                <td>{{ $slot->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <form method="POST" action="{{ route('owner.slots.toggle', $slot) }}"
                          onsubmit="return confirm('Ubah status slot {{ $slot->code }}?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-secondary">{{ $slot->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <form class="row g-2" method="POST" action="{{ route('owner.slots.store', $garage) }}">
        @csrf
        <div class="col-md-3"><input class="form-control" name="code" placeholder="Kode, mis. A7" required></div>
        <div class="col-md-6"><input class="form-control" name="note" placeholder="Catatan (opsional)"></div>
        <div class="col-md-3"><button class="btn btn-outline-primary w-100">Tambah slot</button></div>
    </form>
@endif
@endsection
