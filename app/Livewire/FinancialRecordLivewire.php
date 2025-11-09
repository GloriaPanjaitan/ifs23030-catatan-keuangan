<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class FinancialRecordLivewire extends Component
{
    use WithPagination;

    protected $listeners = ['executeDelete']; 

    public $amount;
    public $type = 'expense';
    public $description;

    public $search = '';
    public $filterType = ''; 

    public $recordId; 
    public $editAmount;
    public $editType;
    public $editDescription;
    
    public $totalIncome = 0;
    public $totalExpense = 0;
    public $balance = 0;

    // ID Chart yang berbeda
    public $chartId1 = 'monthlyChart'; // Chart 1: Bar Monthly
    public $chartId2 = 'cumulativeChart'; // Chart 2: Area Cumulative
    
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

    private function getMonthlyChartData()
    {
        $records = FinancialRecord::where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get();

        $monthlyData = $records->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('Y-m');
        })->map(function ($group) {
            return [
                'income' => $group->where('type', 'income')->sum('amount'),
                'expense' => $group->where('type', 'expense')->sum('amount'),
                'label' => \Carbon\Carbon::parse($group->first()->created_at)->format('M Y') 
            ];
        });

        $categories = $monthlyData->pluck('label')->toArray();
        $incomeSeries = $monthlyData->pluck('income')->toArray();
        $expenseSeries = $monthlyData->pluck('expense')->toArray();

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Total Pemasukan',
                    'data' => $incomeSeries
                ],
                [
                    'name' => 'Total Pengeluaran',
                    'data' => $expenseSeries
                ]
            ]
        ];
    }
    
    // FINAL: Metode untuk menghitung Saldo Kumulatif (Chart KATEGORIKAL)
    private function getCumulativeChartData()
    {
        $records = FinancialRecord::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = 0;
        $categories = [];
        $seriesData = [];
        $counter = 0;
        $initialBalance = 0; 

        // Tambahkan titik awal 'Mulai'
        $seriesData[] = $initialBalance;
        $categories[] = 'Mulai';

        foreach ($records as $record) {
            $amount = $record->amount;
            if ($record->type === 'expense') {
                $runningBalance -= $amount;
            } else {
                $runningBalance += $amount;
            }
            $counter++;

            $seriesData[] = $runningBalance; 
            
            // Label X-axis: Deskripsi Transaksi + Tanggal singkat
            $label = ($record->description ?: 'Trans #'.$counter) . ' (' . \Carbon\Carbon::parse($record->created_at)->format('d/m') . ')';
            $categories[] = $label;
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Saldo Kumulatif', 
                    'data' => $seriesData
                ]
            ]
        ];
    }

    // Dispatch data untuk kedua chart
    private function dispatchChartUpdate()
    {
        $monthlyData = $this->getMonthlyChartData();
        $cumulativeData = $this->getCumulativeChartData(); 

        $this->dispatch('chartDataUpdated', monthly: $monthlyData, cumulative: $cumulativeData);
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
        
        $this->dispatchChartUpdate(); 
        
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
        
        $this->dispatchChartUpdate(); 
        
        $this->dispatch('simpleSuccess', title: 'Berhasil!', text: 'Catatan keuangan berhasil diperbarui.');
        
        $this->reset(['recordId', 'editAmount', 'editType', 'editDescription']);
    }

    public function delete($recordId)
    {
        $this->dispatch('confirmDelete', id: $recordId); 
    }

    public function executeDelete($recordId)
    {
        if (!$recordId) {
             $this->dispatch('deleteError', message: 'Gagal menghapus: ID catatan tidak ditemukan.');
             return;
        }

        $record = FinancialRecord::find($recordId);

        if ($record && $record->user_id === Auth::id()) {
            $record->delete();
            $this->calculateTotals(); 
            $this->resetPage(); 
            
            $this->dispatchChartUpdate(); 
            
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
        
        $chartData1 = $this->getMonthlyChartData();
        $chartData2 = $this->getCumulativeChartData();
        
        return view('livewire.financial-record-livewire', [
            'financialRecords' => $financialRecords, 
            'chartData1' => $chartData1,
            'chartData2' => $chartData2,
            'chartId1' => $this->chartId1,
            'chartId2' => $this->chartId2,
        ]);
    }
}