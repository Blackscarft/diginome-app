<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankMutationsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Mengembalikan data baris (kosong karena hanya butuh header template).
     */
    public function array(): array
    {
        return [];
    }

    /**
     * Nama-nama kolom header di file Excel.
     */
    public function headings(): array
    {
        return [
            // 'bank_account_id',
            'kode_bank',
            'nama_bank',
            'tanggal',
            'waktu',
            'debit',
            'kredit',
            'referensi',
            'deskripsi',
        ];
    }

    /**
     * Styling baris header (misal: tebal/bold).
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
