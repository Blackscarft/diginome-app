<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder; 

class BankMutation extends Model
{
    use SoftDeletes;

    protected $table = "bank_mutations";

    protected $fillable = [
        'journal_id',
        'bank_account_id',
        'source_id',
        'bank_code',
        'bank_name',
        'debit',
        'credit',
        'transaction_date',
        'transaction_time',
        'description',
        'reference',
        'is_matched',
    ];

    protected $casts = [
        'journal_id' => 'integer',
        'bank_account_id' => 'integer',
        'source_id' => 'integer',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'transaction_date' => 'date',
        'is_matched' => 'boolean',
    ];

    // ==========================================
    // RELASI (RELATIONSHIPS)
    // ==========================================

    /**
     * Relasi ke model Journal (Jurnal Akuntansi).
     */
    // public function journal(): BelongsTo
    // {
    //     return $this->belongsTo(Journal::class, 'journal_id');
    // }

    /**
     * Relasi ke model BankAccount (Akun/Rekening Bank).
     */
    // public function bankAccount(): BelongsTo
    // {
    //     return $this->belongsTo(BankAccount::class, 'bank_account_id');
    // }

    // ==========================================
    // SCOPES (UNTUK PERFOMA QUERY & PILIHAN INDEX)
    // ==========================================

    /**
     * Filter mutasi berdasarkan status penyesuaian (matching).
     */
    public function scopeMatched(Builder $query, bool $status = true){
        return $query->where('is_matched', $status);
    }

    /**
     * Filter mutasi yang belum di-match (unmatched).
     */
    public function scopeUnmatched(Builder $query){
        return $query->where('is_matched', false);
    }

    /**
     * Filter berdasarkan range tanggal transaksi.
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Filter berdasarkan akun bank tertentu.
     */
    public function scopeForAccount(Builder $query, int $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    /**
     * Filter berdasarkan kode bank tertentu.
     */
    public function scopeForBankCode(Builder $query, string $bankCode): Builder
    {
        return $query->where('bank_code', $bankCode);
    }

    // ==========================================
    // ACCESSORS (PROPERTI DUMMY / HELPER)
    // ==========================================

    /**
     * Mengambil nominal transaksi (mengembalikan nilai debit jika debit > 0, atau credit jika credit > 0).
     */
    public function getAmountAttribute(): float
    {
        return $this->debit > 0 ? $this->debit : $this->credit;
    }

    /**
     * Menentukan tipe mutasi ('CR' / Masuk atau 'DB' / Keluar).
     */
    public function getTypeAttribute(): string
    {
        return $this->debit > 0 ? 'CR' : 'DB';
    }

}
