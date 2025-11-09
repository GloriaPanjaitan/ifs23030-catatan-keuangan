<div>
    <h2 class="text-2xl font-semibold mb-4">Catatan Keuangan Anda</h2>

    <div class="row mb-4">
        {{-- Total Pemasukan/Pengeluaran/Saldo --}}
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan</h5>
                    <p class="card-text fs-4">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Pengeluaran</h5>
                    <p class="card-text fs-4">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow">
                <div class="card-body">
                    <h5 class="card-title">Saldo Akhir</h5>
                    <p class="card-text fs-4">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div> 

    {{-- CHART 1: BULANAN (MIXED CHART) --}}
    <div class="card shadow mb-4" wire:ignore>
        <div class="card-header">Visualisasi Data (Pemasukan, Pengeluaran, & Saldo Bulanan)</div>
        <div class="card-body">
            <div id="{{ $chartId1 }}"></div> 
        </div>
    </div>
    
    {{-- CHART 2: SALDO KUMULATIF --}}
    <div class="card shadow mb-4" wire:ignore>
        <div class="card-header">Saldo Kumulatif Berdasarkan Transaksi</div>
        <div class="card-body">
            <div id="{{ $chartId2 }}"></div>
        </div>
    </div>
    
    {{-- SERIALISASI DATA UNTUK JAVASCRIPT --}}
    <script>
        // Data Chart 1
        window.financialChartData1 = @json($chartData1);
        window.chartContainerId1 = @json($chartId1); 
        // Data Chart 2
        window.financialChartData2 = @json($chartData2);
        window.chartContainerId2 = @json($chartId2); 
    </script>

    <div class="card shadow mb-4">
        <div class="card-header">Tambah Catatan Baru</div>
        <div class="card-body">
            <form wire:submit.prevent="addRecord" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipe</label>
                    <select id="type" wire:model.defer="type" class="form-select">
                        <option value="expense">Pengeluaran (-)</option>
                        <option value="income">Pemasukan (+)</option>
                    </select>
                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                    <label for="amount" class="form-label">Jumlah (Rp)</label>
                    <input type="number" id="amount" wire:model.defer="amount" class="form-control" placeholder="Cth: 50000" min="1">
                    @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-5">
                    <label for="description" class="form-label">Deskripsi/Keterangan</label>
                    <div class="input-group">
                        <input type="text" id="description" wire:model.defer="description" class="form-control" placeholder="Cth: Gaji Bulanan atau Beli Kopi">
                        <button type="submit" class="btn btn-primary">
                            Tambah
                        </button>
                    </div>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </form>
        </div>
    </div>

    <h3 class="h4 mt-4 mb-3">Riwayat Transaksi</h3>
    
    {{-- Input Pencarian, Filter, dan Tombol Reset (Perbaikan Aksesibilitas di sini) --}}
    <div class="row mb-3 align-items-center">
        <div class="col-md-6 mb-2 mb-md-0">
            <input 
                type="text" 
                class="form-control" 
                placeholder="Cari berdasarkan deskripsi..."
                wire:model.live.debounce.300ms="search" 
                aria-label="Kolom Pencarian Transaksi" {{-- Tambahan aria-label untuk Search --}}
            >
        </div>
        
        <div class="col-md-3 mb-2 mb-md-0">
            <select wire:model.live="filterType" class="form-select" aria-label="Filter berdasarkan tipe transaksi"> {{-- PERBAIKAN UTAMA: Tambahkan aria-label --}}
                <option value="">Semua Tipe</option>
                <option value="income">Pemasukan Saja</option>
                <option value="expense">Pengeluaran Saja</option>
            </select>
        </div>

        <div class="col-md-3">
            @if ($search || $filterType)
                <button 
                    wire:click="resetFilter" 
                    class="btn btn-secondary w-100"
                    aria-label="Tombol Reset Filter dan Pencarian" {{-- Tambahan aria-label --}}
                >
                    Reset Filter
                </button>
            @endif
        </div>
    </div>
    {{-- END Input Pencarian, Filter, dan Tombol Reset --}}

    @forelse ($financialRecords as $record)
        <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded shadow-sm {{ $record->type == 'income' ? 'bg-white border border-primary border-start-5' : 'bg-light border border-danger border-start-5' }}">
            <div>
                <p class="mb-0 fw-bold">{{ $record->description ?: 'Tanpa Deskripsi' }}</p>
                <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($record->created_at)->format('d F Y H:i') }}</p>
            </div>
            <div class="d-flex align-items-center">
                <p class="fs-5 fw-bold mb-0 me-4 {{ $record->type == 'income' ? 'text-primary' : 'text-danger' }}">
                    {{ $record->type == 'income' ? '+' : '-' }} Rp {{ number_format($record->amount, 0, ',', '.') }}
                </p>
                
                <button wire:click="edit({{ $record->id }})" class="btn btn-sm btn-warning me-2 text-white" aria-label="Edit Catatan">
                    Edit
                </button>
                
                <button wire:click="delete({{ $record->id }})" class="btn btn-sm btn-danger" aria-label="Hapus Catatan">
                    Hapus
                </button>
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center">Belum ada catatan keuangan yang dibuat atau tidak ada data yang cocok dengan kriteria pencarian/filter.</div>
    @endforelse

    {{-- Tautan Pagination Livewire --}}
    <div class="mt-4">
        {{ $financialRecords->links() }}
    </div>

    {{-- MODAL EDIT (HARD-CODED) --}}
    <div wire:ignore.self class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRecordModalLabel">Edit Catatan Keuangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="updateRecord">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editType" class="form-label">Tipe</label>
                            <select id="editType" wire:model.defer="editType" class="form-select">
                                <option value="income">Pemasukan (+)</option>
                                <option value="expense">Pengeluaran (-)</option>
                            </select>
                            @error('editType') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editAmount" class="form-label">Jumlah (Rp)</label>
                            <input type="number" id="editAmount" wire:model.defer="editAmount" class="form-control" min="1">
                            @error('editAmount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Deskripsi</label>
                            <input type="text" id="editDescription" wire:model.defer="editDescription" class="form-control" maxlength="255">
                            @error('editDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>