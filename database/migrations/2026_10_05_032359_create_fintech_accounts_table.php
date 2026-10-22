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
        Schema::create('fintech_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('restrict'); // Mencegah Akun dihapus jika masih terikat ke Bank

            $table->foreignId('fee_account_id')
                ->constrained('accounts')
                ->onDelete('restrict');

            $table->string('fintech_account_code')->unique()->index();
            $table->string('fintech_account_name');
            $table->string('merchant_id')->unique()->index();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            // Composite Index untuk pencarian & filter gabungan
            $table->index(['fintech_account_code', 'fintech_account_name', 'is_active'], 'idx_fintech_search_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fintech_accounts');
    }
};
