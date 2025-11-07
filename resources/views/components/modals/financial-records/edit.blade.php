{{-- resources/views/components/modals/financial-records/edit.blade.php --}}

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