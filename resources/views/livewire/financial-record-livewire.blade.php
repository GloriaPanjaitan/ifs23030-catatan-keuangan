<div>
    <h2 class="text-2xl font-semibold mb-4">Catatan Keuangan Anda</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-blue-100 rounded-lg shadow">
            <h3 class="font-bold text-gray-600">Total Pemasukan</h3>
            <p class="text-xl text-blue-800">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 bg-red-100 rounded-lg shadow">
            <h3 class="font-bold text-gray-600">Total Pengeluaran</h3>
            <p class="text-xl text-red-800">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 bg-green-100 rounded-lg shadow">
            <h3 class="font-bold text-gray-600">Saldo Akhir</h3>
            <p class="text-xl text-green-800">Rp {{ number_format($balance, 0, ',', '.') }}</p>
        </div>
    </div>

    <form wire:submit.prevent="addRecord" class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Tipe</label>
                <select id="type" wire:model.defer="type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="income">Pemasukan (+)</option>
                    <option value="expense">Pengeluaran (-)</option>
                </select>
                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                <input type="number" id="amount" wire:model.defer="amount" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Cth: 50000" min="1">
                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi/Keterangan</label>
                <input type="text" id="description" wire:model.defer="description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Cth: Gaji Bulanan atau Beli Kopi">
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-4 text-right">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-md">
                Tambah Catatan
            </button>
        </div>
    </form>

    <h3 class="text-xl font-semibold mt-8 mb-4">Riwayat Transaksi</h3>

    @forelse ($financialRecords as $record)
        <div class="flex justify-between items-center p-4 mb-2 rounded-lg shadow-md {{ $record->type == 'income' ? 'bg-white border-l-4 border-blue-500' : 'bg-gray-50 border-l-4 border-red-500' }}">
            <div>
                <p class="font-bold">{{ $record->description ?: 'Tanpa Deskripsi' }}</p>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($record->created_at)->format('d F Y') }}</p>
            </div>
            <div class="text-right">
                <p class="font-semibold {{ $record->type == 'income' ? 'text-blue-600' : 'text-red-600' }}">
                    {{ $record->type == 'income' ? '+' : '-' }} Rp {{ number_format($record->amount, 0, ',', '.') }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-gray-500 italic">Belum ada catatan keuangan yang dibuat.</p>
    @endforelse
</div>