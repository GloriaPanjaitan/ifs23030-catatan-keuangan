{{-- resources/views/pages/app/financial-record.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @livewire('financial-record-livewire')
            </div>
        </div>
    </div>
@endsection