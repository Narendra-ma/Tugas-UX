<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Garasi Surya')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* teks dan tombol dibuat besar karena pemilik garasi berusia 60 tahun (lihat laporan UX) */
        html { font-size: 18px; }
        .btn, .form-control, .form-select { min-height: 44px; }
        .garage-img { height: 180px; object-fit: cover; background: var(--bs-secondary-bg); }
    </style>
    <script>
        // set tema sebelum render supaya tidak berkedip
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('garages.index') }}">Garasi Surya</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('garages.index') }}">Cari Garasi</a></li>
                @auth
                    @if (auth()->user()->isOwner())
                        <li class="nav-item"><a class="nav-link" href="{{ route('owner.dashboard') }}">Ringkasan</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('owner.bookings.index') }}">Pengajuan</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('owner.garages.index') }}">Garasi &amp; Slot</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('owner.documents.index') }}">Surat</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('bookings.index') }}">Pengajuan Saya</a></li>
                    @endif
                @endauth
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary" type="button" id="themeBtn" aria-label="Ganti mode terang atau gelap">Terang / Gelap</button>
                @auth
                    <span class="small text-body-secondary">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger">Keluar</button>
                    </form>
                @else
                    <a class="btn btn-outline-primary" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('themeBtn').addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
    });
</script>
</body>
</html>
