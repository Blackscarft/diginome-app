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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            
            $table->morphs('accountable'); // Bank, Cash, Fintech
            $table->foreignId('transaction_types')->constrained()->onDelete('cascade');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            $table->date('transaction_date')->index();
            $table->time('transaction_time')->nullable();
            $table->text('description');

            $table->string('reference')->nullable()->index();
            $table->decimal('fee_amount', 15, 2)->default(0);

            $table->boolean('is_matched')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // --- COMPOSITE INDEX ---
            // Optimasi query laporan mutasi per akun berdasarkan tanggal
            $table->index(['accountable_type', 'accountable_id', 'transaction_date'], 'idx_account_date_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
