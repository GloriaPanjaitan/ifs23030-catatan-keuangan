<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class FinancialRecordLivewire extends Component
{
    use WithPagination;

    // WAJIB: Listener untuk event global yang dikirim oleh SweetAlert setelah konfirmasi
    protected $listeners = ['executeDelete']; 

    // Properti untuk Input Form Create
    public $amount;
    public $type = 'expense';
    public $description;

    // Properti untuk Pencarian dan Filter
    public $search = '';
    public $filterType = ''; 

    // Properti untuk Edit/Update/Delete
    public $recordId; 
    public $editAmount;
    public $editType;
    public $editDescription;
    
    // Properti untuk Data dan Ringkasan
    public $totalIncome = 0;
    public $totalExpense = 0;
    public $balance = 0;
    
    protected $queryString = ['search' => ['except' => ''], 'filterType' => ['except' => '']];

    public function mount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $allRecords = FinancialRecord::where('user_id', Auth::id())->get();
        $this->totalIncome = $allRecords->where('type', 'income')->sum('amount');
        $this->totalExpense = $allRecords->where('type', 'expense')->sum('amount');
        $this->balance = $this->totalIncome - $this->totalExpense;
    }

    public function addRecord()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:255',
        ]);

        FinancialRecord::create([
            'user_id' => Auth::id(),
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
        ]);

        $this->reset(['amount', 'description']); 
        $this->calculateTotals(); 
        $this->resetPage();
        
        $this->dispatch('simpleSuccess', title: 'Berhasil!', text: 'Catatan keuangan berhasil ditambahkan.');
    }
    
    public function updated($property)
    {
        if ($property === 'search' || $property === 'filterType') {
            $this->resetPage();
        }
    }
    
    public function resetFilter()
    {
        $this->reset(['search', 'filterType']);
        $this->resetPage();
    }

    public function edit($recordId)
    {
        $record = FinancialRecord::find($recordId);
        if (!$record || $record->user_id !== Auth::id()) {
            $this->dispatch('simpleError', title: 'Error', text: 'Catatan tidak ditemukan.');
            return;
        }

        $this->recordId = $record->id;
        $this->editAmount = $record->amount;
        $this->editType = $record->type;
        $this->editDescription = $record->description;
        
        $this->dispatch('showModal', id: 'editRecordModal'); 
    }

    public function updateRecord()
    {
        $this->validate([
            'editAmount' => 'required|numeric|min:1',
            'editType' => 'required|in:income,expense',
            'editDescription' => 'nullable|string|max:255',
        ]);

        $record = FinancialRecord::find($this->recordId);
        
        if (!$record || $record->user_id !== Auth::id()) {
            $this->dispatch('simpleError', title: 'Error', text: 'Catatan tidak ditemukan.');
            return;
        }

        $record->update([
            'amount' => $this->editAmount,
            'type' => $this->editType,
            'description' => $this->editDescription,
        ]);
        
        $this->calculateTotals();
        $this->dispatch('closeModal', id: 'editRecordModal'); 
        
        $this->dispatch('simpleSuccess', title: 'Berhasil!', text: 'Catatan keuangan berhasil diperbarui.');
        
        $this->reset(['recordId', 'editAmount', 'editType', 'editDescription']);
    }

    public function delete($recordId)
    {
        $this->dispatch('confirmDelete', id: $recordId); 
    }

    // PERBAIKAN KRUSIAL: Menerima $recordId langsung dari payload JS
    public function executeDelete($recordId)
    {
        // $recordId sekarang sudah berisi ID dari Livewire tanpa masalah Dependency Injection
        
        if (!$recordId) {
             $this->dispatch('deleteError', message: 'Gagal menghapus: ID catatan tidak ditemukan.');
             return;
        }

        $record = FinancialRecord::find($recordId);

        if ($record && $record->user_id === Auth::id()) {
            $record->delete();
            $this->calculateTotals(); 
            $this->resetPage(); 
            
            $this->dispatch('recordDeleted');
        } else {
            $this->dispatch('deleteError', message: 'Gagal menghapus: Catatan tidak ditemukan atau bukan milik Anda.');
        }
    }
    
    public function render()
    {
        $query = FinancialRecord::where('user_id', Auth::id());
        
        if ($this->filterType && in_array($this->filterType, ['income', 'expense'])) {
            $query->where('type', $this->filterType);
        }

        if ($this->search) {
            $query->where('description', 'like', '%' . $this->search . '%');
        }

        $financialRecords = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('livewire.financial-record-livewire', [
            'financialRecords' => $financialRecords, 
        ]);
    }
}