<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialRecord;
use Illuminate\Support\Facades\Auth;

class FinancialRecordLivewire extends Component
{
    public $amount;
    public $type = 'expense';
    public $description;

    public $financialRecords;
    public $totalIncome = 0;
    public $totalExpense = 0;
    public $balance = 0;

    public $recordId; 
    public $editAmount;
    public $editType;
    public $editDescription;

    public function mount()
    {
        $this->loadRecords();
    }

    public function loadRecords()
    {
        $this->financialRecords = FinancialRecord::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        $this->calculateTotals();
    }
    
    public function calculateTotals()
    {
        $this->totalIncome = $this->financialRecords->where('type', 'income')->sum('amount');
        $this->totalExpense = $this->financialRecords->where('type', 'expense')->sum('amount');
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
        $this->loadRecords();
        session()->flash('message', 'Catatan keuangan berhasil ditambahkan!');
    }

    // Perbaikan: dispatch menggunakan named arguments
    public function edit($recordId)
    {
        $record = FinancialRecord::find($recordId);
        if (!$record || $record->user_id !== Auth::id()) {
            session()->flash('error', 'Catatan tidak ditemukan atau bukan milik Anda.');
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
            session()->flash('error', 'Catatan tidak ditemukan atau bukan milik Anda.');
            return;
        }

        $record->update([
            'amount' => $this->editAmount,
            'type' => $this->editType,
            'description' => $this->editDescription,
        ]);
        
        $this->loadRecords();
        $this->dispatch('closeModal', id: 'editRecordModal'); // Perbaikan: dispatch menggunakan named arguments
        session()->flash('message', 'Catatan keuangan berhasil diperbarui!');
        $this->reset(['recordId', 'editAmount', 'editType', 'editDescription']);
    }

    // Perbaikan: dispatch menggunakan named arguments
    public function delete($recordId)
    {
        $this->recordId = $recordId;
        $this->dispatch('showModal', id: 'deleteRecordModal'); 
    }

    // Perbaikan: dispatch menggunakan named arguments
    public function deleteRecord()
    {
        $record = FinancialRecord::find($this->recordId);

        if ($record && $record->user_id === Auth::id()) {
            $record->delete();
            session()->flash('message', 'Catatan keuangan berhasil dihapus.');
        } else {
            session()->flash('error', 'Catatan tidak ditemukan atau bukan milik Anda.');
        }

        $this->dispatch('closeModal', id: 'deleteRecordModal');
        $this->loadRecords();
        $this->reset(['recordId']);
    }
    
    public function render()
    {
        return view('livewire.financial-record-livewire');
    }
}