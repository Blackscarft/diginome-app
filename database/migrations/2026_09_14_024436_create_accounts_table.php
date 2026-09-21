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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(-1)->index();
            $table->integer('order')->default(0);
            $table->string('code', 25)->unique();
            $table->string('name');
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
            $table->boolean('is_postable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Composite Index
            $table->index(['parent_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
