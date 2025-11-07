<div>
    <h2 class="text-2xl font-semibold mb-4">Catatan Keuangan Anda</h2>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="row mb-4">
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
                
                <button wire:click="edit({{ $record->id }})" class="btn btn-sm btn-warning me-2 text-white">
                    Edit
                </button>
                
                <button wire:click="delete({{ $record->id }})" class="btn btn-sm btn-danger">
                    Hapus
                </button>
            </div>
        </div>
    @empty
        <div class="alert alert-info text-center">Belum ada catatan keuangan yang dibuat.</div>
    @endforelse

    {{-- ========================================================================= --}}
    {{-- MODAL EDIT (HARD-CODED) --}}
    {{-- ========================================================================= --}}
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

    {{-- ========================================================================= --}}
    {{-- MODAL DELETE (HARD-CODED) --}}
    {{-- ========================================================================= --}}
    <div wire:ignore.self class="modal fade" id="deleteRecordModal" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteRecordModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus catatan keuangan ini? Aksi ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" wire:click.prevent="deleteRecord" class="btn btn-danger">Ya, Hapus!</button>
                </div>
            </div>
        </div>
    </div>

</div>