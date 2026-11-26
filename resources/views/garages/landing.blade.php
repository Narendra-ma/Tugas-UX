@extends('layouts.app')
@section('title', $garage->name)
@section('content')
@php
    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
    $badge = ['tersedia' => 'success', 'menunggu' => 'warning', 'terisi' => 'danger'];
    $label = ['tersedia' => 'Tersedia', 'menunggu' => 'Menunggu', 'terisi' => 'Terisi'];
    $free = $garage->slots->filter(fn ($s) => $s->displayStatus() === 'tersedia');
@endphp

<section class="row g-4 align-items-center mb-5">
    <div class="col-lg-7">
        <h1 class="display-5 fw-bold">{{ $garage->name }}</h1>
        <p class="fs-5 mb-2">Garasi sewa bulanan di {{ $garage->city }}, dikelola langsung oleh {{ $garage->owner->name }}.</p>
        <p class="text-body-secondary">{{ $garage->address }}</p>
        @if ($garage->description)<p>{{ $garage->description }}</p>@endif
        <p class="mb-4">
            <strong>{{ $free->count() }} dari {{ $garage->slots->count() }} slot</strong>
            kosong pada {{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }}@if ($end !== $start) sampai {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}@endif.
        </p>
        <a class="btn btn-primary btn-lg" href="#ajukan">Ajukan sewa</a>
        <a class="btn btn-outline-secondary btn-lg" href="#slot">Lihat slot</a>
    </div>
    <div class="col-lg-5">
        @if ($garage->photo_path)
            <img class="img-fluid rounded" src="{{ asset('storage/'.$garage->photo_path) }}" alt="Foto {{ $garage->name }}">
        @else
            <div class="rounded border garage-img d-flex align-items-center justify-content-center text-body-secondary" style="height:260px">Foto garasi belum ditambahkan</div>
        @endif
    </div>
</section>

<section class="mb-5">
    <h2 class="h4 mb-3">Tarif sewa</h2>
    <div class="row g-3">
        @foreach (['price_daily' => 'Harian', 'price_weekly' => 'Mingguan', 'price_monthly' => 'Bulanan'] as $col => $name)
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="text-body-secondary">{{ $name }}</div>
                    <div class="h3 mb-0">{{ $rp($garage->$col) }}</div>
                </div></div>
            </div>
        @endforeach
    </div>
    @if ($garage->size_note)<p class="small text-body-secondary mt-2 mb-0">{{ $garage->size_note }}</p>@endif
</section>

<section class="mb-5" id="slot">
    <h2 class="h4 mb-3">Status slot</h2>
    <form method="GET" action="{{ route('garages.index') }}#slot" class="row g-2 mb-3">
        <div class="col-sm-5 col-md-4">
            <label class="form-label" for="start_date">Dari tanggal</label>
            <input class="form-control" type="date" id="start_date" name="start_date" value="{{ $start }}">
        </div>
        <div class="col-sm-5 col-md-4">
            <label class="form-label" for="end_date">Sampai tanggal</label>
            <input class="form-control" type="date" id="end_date" name="end_date" value="{{ $end }}">
        </div>
        <div class="col-sm-2 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Cek slot</button>
        </div>
    </form>

    <div class="row g-2">
        @foreach ($garage->slots as $slot)
            @php $status = $slot->displayStatus(); @endphp
            <div class="col-4 col-md-2">
                <div class="border rounded p-3 text-center">
                    <div class="h5 mb-1">{{ $slot->code }}</div>
                    <span class="badge text-bg-{{ $badge[$status] }}">{{ $label[$status] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="mb-5">
    <h2 class="h4 mb-3">Cara menyewa</h2>
    <ol class="mb-0">
        <li>Pilih slot yang tersedia.</li>
        <li>Masuk atau daftar akun.</li>
        <li>Isi tipe mobil, plat, lama sewa, dan tanggal mulai.</li>
        <li>Tunggu jawaban pemilik, paling lama 1&times;24 jam. Status bisa dipantau di &ldquo;Pengajuan Saya&rdquo;.</li>
        <li>Setelah diterima, unggah bukti bayar. Surat perjanjian ditandatangani langsung bersama pemilik.</li>
    </ol>
</section>

<section id="ajukan" class="mb-4">
    <h2 class="h4 mb-3">Ajukan sewa</h2>
    @guest
        <div class="alert alert-info">
            <a href="{{ route('login') }}">Masuk</a> atau <a href="{{ route('register') }}">daftar</a> dulu untuk mengajukan sewa.
        </div>
    @else
        @if (auth()->user()->isOwner())
            <div class="alert alert-secondary">Akun pemilik tidak bisa mengajukan sewa. Buka <a href="{{ route('owner.dashboard') }}">Ringkasan</a> untuk mengelola garasi.</div>
        @elseif ($free->isEmpty())
            <div class="alert alert-warning">Semua slot terisi pada tanggal yang dipilih. Coba tanggal lain di bagian Status slot.</div>
        @else
            <form method="POST" action="{{ route('bookings.store', $garage) }}" class="col-lg-8 px-0">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="slot_id">Slot</label>
                    <select class="form-select" id="slot_id" name="slot_id" required>
                        <option value="">Pilih slot</option>
                        @foreach ($free as $slot)
                            <option value="{{ $slot->id }}" @selected(old('slot_id') == $slot->id)>{{ $slot->code }}{{ $slot->note ? ' - '.$slot->note : '' }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Hanya slot yang kosong pada tanggal yang dicek di atas.</div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="car_type">Tipe mobil</label>
                        <input class="form-control" id="car_type" name="car_type" value="{{ old('car_type') }}" placeholder="Avanza, Brio..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="plate">Plat nomor</label>
                        <input class="form-control" id="plate" name="plate" value="{{ old('plate') }}" placeholder="DK 1234 AB" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="period">Hitungan harga</label>
                        <select class="form-select" id="period" name="period" required>
                            @foreach (\App\Models\Booking::PERIODS as $key => $name)
                                <option value="{{ $key }}" @selected(old('period', 'monthly') === $key)>{{ $name }} ({{ $rp($garage->priceFor($key)) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="b_start">Mulai</label>
                        <input class="form-control" type="date" id="b_start" name="start_date" value="{{ old('start_date', $start) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="b_end">Selesai</label>
                        <input class="form-control" type="date" id="b_end" name="end_date" value="{{ old('end_date', $end) }}" required>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1">
                    <label class="form-check-label" for="agree">
                        Saya setuju dengan Syarat &amp; Ketentuan dan mengizinkan pemilik menyimpan data identitas serta plat kendaraan saya.
                    </label>
                </div>
                <button class="btn btn-primary btn-lg">Kirim pengajuan</button>
            </form>
        @endif
    @endguest
</section>
@endsection
