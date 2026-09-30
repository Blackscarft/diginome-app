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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('restrict'); // Mencegah Akun dihapus jika masih terikat ke Bank

            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->string('account_holder');

            // Saldo Awal saat pertama kali aplikasi Go-Live
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('bank_code');
            $table->index('is_active');
            
            // Composite Index untuk kueri filter akun aktif berdasarkan kode bank
            $table->index(['bank_code', 'is_active']);

            // Composite Index untuk pencarian nama bank & pemilik akun
            $table->index(['bank_name', 'account_holder']);

           // Composite Index untuk pencarian & filter gabungan
            $table->index(['bank_name', 'bank_code', 'is_active'], 'idx_bank_search_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
