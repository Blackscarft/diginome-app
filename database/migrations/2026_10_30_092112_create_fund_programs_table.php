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
        Schema::create('fund_programs', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(-1)->index();
            $table->foreignId('fund_id')->constrained('funds')->onDelete('restrict'); // Mencegah Akun dihapus jika masih terikat ke program

            $table->string('fund_program_code')->unique()->index();
            $table->string('fund_program_name');
            $table->string('description')->nullable();

            $table->enum('category', ['Sosial Kemanusiaan dan Lingkungan', 'Dakwah', 'Pendidikan', 'Kesehatan', 'Ekonomi', 'Kolektif', 'Lainnya']);
            $table->decimal('initial_balance', 15, 2)->default(0)->comment('Untuk pengisian saldo pertamakali sistem live');
            $table->boolean('is_active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_program');
    }
};
