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
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('restrict'); // Mencegah Akun dihapus jika masih terikat ke Bank

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->onDelete('restrict'); // Mencegah Cabang dihapus jika masih terikat ke Rekening Kas

            $table->string('cash_account_code')->unique()->index();
            $table->string('cash_account_name');
            $table->string('custodian_name')->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('maximum_balance', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->index(['cash_account_code', 'cash_account_name', 'is_active'], 'idx_cash_account_search_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
    }
};
