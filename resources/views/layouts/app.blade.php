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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="bg-light">
    
    {{-- NAVIGASI --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('app.home') }}">Catatan Keuangan App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('app.home') ? 'active' : '' }}" href="{{ route('app.home') }}">
                            Catatan Keuangan
                        </a>
                    </li>
                </ul>
                
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

    <div class="container py-4">
        @yield('content')
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts

    <script>
        const BATCH_BOOTSTRAP = window.bootstrap; 
        
        document.addEventListener("livewire:initialized", () => {
            // Livewire/Bootstrap Modal Handlers (untuk modal Edit)
            Livewire.on("closeModal", (data) => {
                if (data && data.id && BATCH_BOOTSTRAP) { 
                    const modal = BATCH_BOOTSTRAP.Modal.getInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.hide();
                }
            });

            Livewire.on("showModal", (data) => {
                if (data && data.id && BATCH_BOOTSTRAP) { 
                    const modal = BATCH_BOOTSTRAP.Modal.getOrCreateInstance(
                        document.getElementById(data.id)
                    );
                    if (modal) modal.show();
                }
            });

            // =========================================================
            // SWEETALERT LISTENERS
            // =========================================================
            
            // 1. Diterima dari PHP (Notifikasi Sukses Tambah/Update - TOAST)
            Livewire.on("simpleSuccess", (data) => {
                Swal.fire({
                  position: "top-end",
                  icon: "success",
                  title: data.text || "Your work has been saved", 
                  showConfirmButton: false,
                  timer: 1500
                });
            });
            
            // 2. Diterima dari PHP (Konfirmasi Hapus - MODAL KONFIRMASI)
            Livewire.on("confirmDelete", (data) => {
                const recordId = data.id;

                Swal.fire({
                    title: "Are you sure?", 
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6", 
                    cancelButtonColor: "#d33",   
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel!", 
                }).then((result) => {
                    if (result.isConfirmed) {
                        // WAJIB: Gunakan window.Livewire.dispatch global untuk event listener
                        window.Livewire.dispatch('executeDelete', { recordId: recordId });
                        
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            title: "Cancelled",
                            text: "Catatan keuangan Anda aman.",
                            icon: "error"
                        });
                    }
                });
            });
            
            // 3. Diterima dari PHP (setelah Hapus berhasil - MODAL SUKSES PENUH)
            Livewire.on("recordDeleted", () => {
                Swal.fire({
                    title: "Deleted!",
                    text: "Catatan keuangan berhasil dihapus.",
                    icon: "success"
                });
            });

            // 4. Diterima dari PHP (notifikasi error - TOAST)
            Livewire.on("deleteError", (data) => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    icon: 'error',
                    title: data.title || 'Gagal',
                    text: data.message,
                });
            });
            
        });
    </script>
</body>
</html>