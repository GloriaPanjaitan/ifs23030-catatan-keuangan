<!doctype html>
<html lang="id">
<head>
    {{-- Meta --}}
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Icon --}}
    <link rel="icon" href="/logo.png" type="image/x-icon" />

    {{-- Judul --}}
    <title>Laravel Catatan Keuangan</title>

    {{-- Styles --}}
    @livewireStyles
    {{-- PASTIKAN LINK CSS BOOTSTRAP INI BENAR --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    
    {{-- ========================================================= --}}
    {{-- NAVBAR (NAVIGASI) --}}
    {{-- ========================================================= --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            {{-- BRAND/LOGO --}}
            <a class="navbar-brand" href="{{ route('app.home') }}">Catatan Keuangan App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    {{-- Link Utama --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('app.home') ? 'active' : '' }}" href="{{ route('app.home') }}">
                            Catatan Keuangan
                        </a>
                    </li>
                </ul>
                
                {{-- Logout Button --}}
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link disabled text-white-50">{{ Auth::user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="{{ route('auth.logout') }}">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ========================================================= --}}
    {{-- KONTEN HALAMAN --}}
    {{-- ========================================================= --}}
    <div class="container py-4">
        @yield('content')
    </div>

    {{-- ========================================================= --}}
    {{-- SCRIPTS (PASTIKAN URUTAN INI) --}}
    {{-- ========================================================= --}}
    {{-- 1. FILE BOOTSTRAP (WAJIB ADA) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- 2. LIVEWIRE SCRIPTS --}}
    @livewireScripts

    {{-- 3. SCRIPT KONEKTOR LIVEWIRE/BOOTSTRAP (PERBAIKAN DIBUAT DI SINI) --}}
    <script>
        // Deklarasikan variabel Bootstrap secara eksplisit untuk penggunaan yang aman
        const BATCH_BOOTSTRAP = window.bootstrap; 
        
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("closeModal", (data) => {
                // Gunakan BATCH_BOOTSTRAP, dan pastikan data.id ada
                if (data && data.id && BATCH_BOOTSTRAP) {
                    const modal = BATCH_BOOTSTRAP.Modal.getInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.hide();
                }
            });

            Livewire.on("showModal", (data) => {
                // Gunakan BATCH_BOOTSTRAP, dan pastikan data.id ada
                if (data && data.id && BATCH_BOOTSTRAP) {
                    const modal = BATCH_BOOTSTRAP.Modal.getOrCreateInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.show();
                }
            });
        });
    </script>
</body>
</html>