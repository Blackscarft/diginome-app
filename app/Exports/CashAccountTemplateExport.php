<?php

namespace App\Exports;

use App\Models\Account;
use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class CashAccountTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{

    /* Mengembalikan data baris (kosong karena hanya butuh header template).
    */
    public function array(): array
    {
        return [
            [
                '1-0000',           // kode_akun
                'PST',              // kode_cabang
                'P-01',             // kode_kas
                'Kas Utama',        // nama_kas
                'John Doe',         // penanggung_jawab
                '10000',            // maksimal_saldo
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
            'kode_cabang',
            'kode_kas',
            'nama_kas',
            'penanggung_jawab',
            'maksimal_saldo',
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

        $cabang = Branch::where('parent_id', -1)
                ->pluck('branch_code')
                ->toArray();

        $cabangList = '"' . implode(',', $cabang) . '"';

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

            //  2. Terapkan dropdown pada kolom kode cabang (Kolom B)
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih Kode Cabang');
            $validation->setPrompt('Silakan pilih kode cabang.');
            $validation->setFormula1($cabangList);

            // 3. Pesan panduan untuk kode_kas (Kolom C) - Tidak boleh kosong
            $validationParent = $sheet->getCell("C{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Kas');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode kas yang unik');

            // 4. Pesan panduan untuk nama_kas (Kolom D) - Tidak boleh kosong
            $validationParent = $sheet->getCell("D{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Kas');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 5. Pesan panduan untuk nama_penanggung_jawab (Kolom E) - Tidak boleh kosong
            $validationParent = $sheet->getCell("E{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Penanggung Jawab');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 6. Pesan panduan untuk maksimal_saldo (Kolom F) - Tidak boleh kosong
            $validationParent = $sheet->getCell("F{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Maksimal Saldo Yang Diperbolehkan');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 7. Pesan panduan untuk saldo_awal (Kolom G) - Tidak boleh kosong
            $validationParent = $sheet->getCell("G{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Saldo Initial');
            $validationParent->setPrompt('Tidak boleh kosong.');

             //  8. Terapkan dropdown pada kolom status_aktif (Kolom H)
            $validation = $sheet->getCell("H{$row}")->getDataValidation();
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
