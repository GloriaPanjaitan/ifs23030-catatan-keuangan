<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            
            // KOLOM DATA INTI
            $table->bigInteger('user_id')->unsigned(); 
            $table->enum('type', ['income', 'expense']); // Tipe: pemasukan atau pengeluaran
            $table->decimal('amount', 15, 2); // Jumlah uang (dengan 2 desimal)
            $table->string('description')->nullable(); // Deskripsi catatan

            $table->timestamps();

            // FOREIGN KEY KE TABEL USERS
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};