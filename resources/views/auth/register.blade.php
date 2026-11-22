@extends('layouts.app')
@section('title', 'Daftar')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-3">Daftar sebagai penyewa</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">Nama lengkap</label>
                <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="phone">Nomor HP</label>
                <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="081234567890" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Kata sandi (min. 8 karakter)</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            <button class="btn btn-primary w-100">Daftar</button>
        </form>
    </div>
</div>
@endsection
