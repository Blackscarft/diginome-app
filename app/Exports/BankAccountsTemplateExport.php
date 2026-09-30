<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankAccountsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
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
            'kode_akun',
            'kode_bank',
            'nama_bank',
            'no_rekening',
            'atas_nama',
            'catatan',
            'saldo_awal'
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
