<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BankAccountsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Mengembalikan data baris (kosong karena hanya butuh header template).
     */
    public function array(): array
    {
        return [
            [
                '1-0000',           // kode_akun
                '1',                // kode_bank
                'Bank BCA',         // nama_bank
                '1234567890',       // rekening
                'John Doe',         // atas_nama
                '10000',            // saldo_awal
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
            'kode_bank',
            'nama_bank',
            'rekening',
            'atas_nama',
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

            // 2. Pesan panduan untuk kode_bank (Kolom B) - Tidak boleh kosong
            $validationParent = $sheet->getCell("B{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Bank');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode akun yang unik');

            // 3. Pesan panduan untuk nama_bank (Kolom C) - Tidak boleh kosong
            $validationParent = $sheet->getCell("C{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Bank');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 4. Pesan panduan untuk rekening (Kolom D) - Tidak boleh kosong
            $validationParent = $sheet->getCell("D{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Rekening');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan data yang unik');

            // 5. Pesan panduan untuk kode_bank (Kolom E) - Tidak boleh kosong
            $validationParent = $sheet->getCell("E{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Atas Nama');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 6. Pesan panduan untuk saldo_awal (Kolom F) - Tidak boleh kosong
            $validationParent = $sheet->getCell("F{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Saldo Initial');
            $validationParent->setPrompt('Tidak boleh kosong.');

            //  7. Terapkan dropdown pada kolom status_aktif (Kolom G)
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

        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
