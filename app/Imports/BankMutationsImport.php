<?php

namespace App\Imports;

use App\Models\BankMutation;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BankMutationsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * Rules Validasi untuk tiap baris Excel.
     */
    public function rules(): array
    {
        return [
            'kode_bank' => ['required'],
            'nama_bank' => ['required', 'string'],
            'tanggal'   => ['required'],
            'deskripsi' => ['required', 'string'],
        ];
    }

    public function model(array $row): Model|null
    {
        // dd($row); // Debug: Tampilkan data baris yang diimpor
        // Konversi tanggal jika format di Excel berupa angka serial desimal
        $transactionDate = is_numeric($row['tanggal']) 
            ? Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d') 
            : $row['tanggal'];

        return new BankMutation([
            // 'bank_account_id'  => $row['bank_account_id'] ?? null,
            'bank_code'        => (string) $row['kode_bank'],
            'bank_name'        => $row['nama_bank'],
            'transaction_date' => $transactionDate,
            'transaction_time' => $row['waktu'] ?? null,
            'debit'            => $row['debit'] ?? 0,
            'credit'           => $row['kredit'] ?? 0,
            'reference'        => $row['referensi'] ?? null,
            'description'      => $row['deskripsi'],
            'is_matched'       => false,
        ]);
    }

}
