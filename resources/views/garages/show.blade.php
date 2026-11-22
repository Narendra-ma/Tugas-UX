@extends('layouts.app')
@section('title', $garage->name)
@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        @if ($garage->photo_path)
            <img class="img-fluid rounded mb-3" src="{{ asset('storage/'.$garage->photo_path) }}" alt="Foto {{ $garage->name }}">
        @endif
        <h1 class="h3">{{ $garage->name }}</h1>
        <p class="text-body-secondary">{{ $garage->address }}, {{ $garage->city }}</p>
        @if ($garage->description)<p>{{ $garage->description }}</p>@endif
        @if ($garage->size_note)<p class="small">Ukuran: {{ $garage->size_note }}</p>@endif

        <table class="table table-sm w-auto">
            <tr><td>Harian</td><td>Rp {{ number_format($garage->price_daily, 0, ',', '.') }}</td></tr>
            <tr><td>Mingguan</td><td>Rp {{ number_format($garage->price_weekly, 0, ',', '.') }}</td></tr>
            <tr><td>Bulanan</td><td>Rp {{ number_format($garage->price_monthly, 0, ',', '.') }}</td></tr>
        </table>
        <p class="small text-body-secondary">Pemilik: {{ $garage->owner->name }}</p>
    </div>

    <div class="col-lg-7">
        <form method="GET" class="row g-2 mb-3">
            <div class="col">
                <label class="form-label" for="start_date">Cek slot dari</label>
                <input class="form-control" type="date" id="start_date" name="start_date" value="{{ $start }}">
            </div>
            <div class="col">
                <label class="form-label" for="end_date">sampai</label>
                <input class="form-control" type="date" id="end_date" name="end_date" value="{{ $end }}">
            </div>
            <div class="col-auto d-flex align-items-end">
                <button class="btn btn-outline-primary">Cek</button>
            </div>
        </form>

        @php
            $badge = ['tersedia' => 'success', 'menunggu' => 'warning', 'terisi' => 'danger'];
            $label = ['tersedia' => 'Tersedia', 'menunggu' => 'Menunggu', 'terisi' => 'Terisi'];
        @endphp

        <div class="row g-2 mb-4">
            @foreach ($garage->slots as $slot)
                @php $status = $slot->displayStatus(); @endphp
                <div class="col-4 col-md-3">
                    <div class="border rounded p-2 text-center">
                        <div class="fw-bold">{{ $slot->code }}</div>
                        <span class="badge text-bg-{{ $badge[$status] }}">{{ $label[$status] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        @guest
            <div class="alert alert-info">
                <a href="{{ route('login') }}">Masuk</a> atau <a href="{{ route('register') }}">daftar</a> dulu untuk mengajukan sewa.
            </div>
        @else
            @if (auth()->user()->isOwner())
                <div class="alert alert-secondary">Akun pemilik tidak bisa mengajukan sewa.</div>
            @else
                <h2 class="h5">Ajukan sewa</h2>
                <form method="POST" action="{{ route('bookings.store', $garage) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="slot_id">Slot</label>
                        <select class="form-select" id="slot_id" name="slot_id" required>
                            <option value="">Pilih slot</option>
                            @foreach ($garage->slots as $slot)
                                @if ($slot->displayStatus() === 'tersedia')
                                    <option value="{{ $slot->id }}" @selected(old('slot_id') == $slot->id)>{{ $slot->code }}{{ $slot->note ? ' - '.$slot->note : '' }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Hanya slot yang kosong di tanggal yang dicek di atas.</div>
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
                                    <option value="{{ $key }}" @selected(old('period', 'monthly') === $key)>{{ $name }}</option>
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
                    <button class="btn btn-primary">Kirim pengajuan</button>
                </form>
            @endif
        @endguest
    </div>
</div>
@endsection
