@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 mb-3">Masuk</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Kata sandi</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button class="btn btn-primary w-100">Masuk</button>
        </form>
        <p class="mt-3">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai penyewa</a></p>
    </div>
</div>
@endsection
