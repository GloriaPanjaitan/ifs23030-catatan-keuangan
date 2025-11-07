<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Karena controller ini hanya me-return view, kita tidak perlu mengimpor Model FinancialRecord di sini.

class FinancialRecordController extends Controller
{
    /**
     * Menampilkan halaman utama aplikasi Catatan Keuangan.
     * Route /app/home sekarang menunjuk ke fungsi ini.
     */
    public function index()
    {
        // Mengembalikan view utama yang berisi Livewire Component: financial-record-livewire
        return view('pages.app.financial-record');
    }
}