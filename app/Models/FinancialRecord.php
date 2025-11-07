<?php

namespace App\Models; // <--- PASTIKAN NAMESPACE INI BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRecord extends Model // <--- PASTIKAN NAMA CLASS INI BENAR
{
    use HasFactory;

    // Kolom yang boleh diisi (mass assignable), sesuai dengan kolom di migration Anda
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
    ];

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}