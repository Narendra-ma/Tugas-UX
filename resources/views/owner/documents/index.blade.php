@extends('layouts.app')
@section('title', 'Surat')
@section('content')
<h1 class="h3 mb-3">Surat perjanjian &amp; berkas</h1>

<form class="card card-body mb-4" method="POST" action="{{ route('owner.documents.store') }}" enctype="multipart/form-data">
    @csrf
    <h2 class="h5">Unggah surat baru</h2>
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label" for="booking_id">Penyewa</label>
            <select class="form-select" id="booking_id" name="booking_id" required>
                <option value="">Pilih penyewa</option>
                @foreach ($bookings as $b)
                    <option value="{{ $b->id }}">{{ $b->user->name }} - {{ $b->plate }} - Slot {{ $b->slot->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="category">Kategori</label>
            <select class="form-select" id="category" name="category" required>
                @foreach (\App\Models\Document::CATEGORIES as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="file">Foto / PDF (maks. 4 MB)</label>
            <input class="form-control" type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Unggah</button>
        </div>
    </div>
</form>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-5">
        <input class="form-control" name="q" value="{{ $q }}" placeholder="Cari nama penyewa, plat, atau kode slot">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="category">
            <option value="">Semua kategori</option>
            @foreach (\App\Models\Document::CATEGORIES as $key => $name)
                <option value="{{ $key }}" @selected($category === $key)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form>

<table class="table align-middle">
    <thead><tr><th>Penyewa</th><th>Slot</th><th>Kategori</th><th>Berkas</th><th>Diunggah</th></tr></thead>
    <tbody>
    @forelse ($documents as $doc)
        <tr>
            <td>{{ $doc->booking->user->name }}<br><span class="small text-body-secondary">{{ $doc->booking->plate }}</span></td>
            <td>{{ $doc->booking->slot->code }}</td>
            <td>{{ \App\Models\Document::CATEGORIES[$doc->category] ?? $doc->category }}</td>
            <td><a href="{{ route('files.document', $doc) }}">{{ $doc->original_name }}</a></td>
            <td>{{ $doc->created_at->translatedFormat('d M Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-body-secondary">Belum ada surat.</td></tr>
    @endforelse
    </tbody>
</table>

{{ $documents->links('pagination::bootstrap-5') }}
@endsection
