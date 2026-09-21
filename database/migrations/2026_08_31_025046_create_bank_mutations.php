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
        Schema::create('bank_mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_id')->nullable()->index();
            $table->unsignedBigInteger('bank_account_id')->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable();
            
            $table->string('bank_code')->index();
            $table->string('bank_name');

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->date('transaction_date')->index();
            $table->time('transaction_time')->nullable();
            $table->text('description');
            $table->string('reference')->nullable()->index();
            $table->boolean('is_matched')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // --- COMPOSITE INDEXES ---

            // 1. Sering digunakan saat Anda memfilter mutasi berdasarkan akun bank tertentu pada rentang tanggal tertentu
            $table->index(['bank_account_id', 'transaction_date']);

            // 2. Sering digunakan untuk mencari mutasi yang belum/sudah match pada akun bank tertentu
            $table->index(['bank_account_id', 'is_matched']);

            // 3. Gabungan lengkap jika sering melacak status match berdasarkan tanggal pada akun tertentu
            $table->index(['bank_account_id', 'transaction_date', 'is_matched'], 'bank_mutations_filter_index');

            // 4. Gabungan kode bank dan tanggal transaksi unyuk filter gabungan mutasi sesuai kode bank
            $table->index(['bank_code', 'transaction_date'], 'bank_mutations_code_date_index');

            // 5. Gabungan code bank dan status match untuk melihat mutasi yang belum match berdasarkan kode bank
            $table->index(['bank_code', 'is_matched'], 'bank_mutations_code_matched_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_mutations');
    }
};
