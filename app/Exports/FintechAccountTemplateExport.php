<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
class FintechAccountTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /* Mengembalikan data baris (kosong karena hanya butuh header template).
    */
    public function array(): array
    {
        return [
            [
                '1-0000',           // kode_akun
                '5-0000',           // kode_akun_fee
                'MDT',              // kode_fintech
                'Midtrans',         // nama_fintech
                '483920184920194',  // merchant_id
                '10000',            // saldo_initial
                '1',                // status_aktif
                'Contoh pengisian data, dapat dihapus dan diisi sesuai kebutuhan',
            ]    
        ];
    }

    /**
     * Nama-nama kolom header di file Excel.
     */
    public function headings(): array
    {
        return [
            'kode_akun',
            'kode_fee_akun',
            'kode_fintech',
            'nama_fintech',
            'merchant_id',
            'saldo_initial',
            'status_aktif',
            'catatan'
        ];
    }

    /**
     * Styling baris header (misal: tebal/bold).
     */
    public function styles(Worksheet $sheet): array
    {
        $akun = Account::where('is_postable', true)
                ->pluck('account_code')
                ->toArray();

        $akunList = '"' . implode(',', $akun) . '"';

        for ($row = 2; $row <= 100; $row++) {
            //  1. Terapkan dropdown pada kolom kode akun (Kolom A)
            $validation = $sheet->getCell("A{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih Kode Akun');
            $validation->setPrompt('Silakan pilih kode akun.');
            $validation->setFormula1($akunList);

            //  2. Terapkan dropdown pada kolom kode fee akun (Kolom B)
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih Kode Akun');
            $validation->setPrompt('Silakan pilih kode fee akun.');
            $validation->setFormula1($akunList);

            // 3. Pesan panduan untuk kode_kas (Kolom C) - Tidak boleh kosong
            $validationParent = $sheet->getCell("C{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Fintech');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode fintech yang unik');

            // 4. Pesan panduan untuk nama_fintech (Kolom D) - Tidak boleh kosong
            $validationParent = $sheet->getCell("D{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Fintech');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 5. Kolom merchand_id - boleh kosong (Kolom E)

             // 6. Pesan panduan untuk saldo_awal (Kolom F) - Tidak boleh kosong
            $validationParent = $sheet->getCell("F{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Saldo Initial');
            $validationParent->setPrompt('Tidak boleh kosong.');

             //  8. Terapkan dropdown pada kolom status_aktif (Kolom G)
            $validation = $sheet->getCell("G{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown (1 atau 0).');
            $validation->setPromptTitle('Pilih Status');
            $validation->setPrompt('Silakan pilih 1 untuk aktif atau 0 untuk non-aktif.');
            // Nilai pilihan dipisahkan koma dan diapit tanda kutip ganda di dalam string
            $validation->setFormula1('"1,0"');

            // 9. Kolom catatan - boleh kosong (Kolom H)

        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
