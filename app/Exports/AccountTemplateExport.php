<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AccountTemplateExport implements fromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Mengembalikan data baris (kosong karena hanya butuh header template).
     */
    public function array(): array
    {
        return [
            [
                '1-0000',           // kode_akun
                'Aset',             // nama_akun
                'debit',            // deskripsi
                '-1',               // parent_id (diisi -1 jika tidak ada parent)
                '1',                // dapat_diposting
                '1',                // urutan_tampilan (tidak boleh kosong)
                '1',                // status_aktif (1 = aktif, 0 = non-aktif)
                'Akun header utama', // deskripsi
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
            'nama_akun',
            'normal_balance',
            'parent_id',
            'dapat_diposting',
            'urutan_tampilan',
            'status_aktif',
            'deskripsi',
        ];
    }

    /**
     * Styling baris header (misal: tebal/bold).
     */
    public function styles(Worksheet $sheet): array
    {
        for ($row = 2; $row <= 100; $row++) {
            // 1. Pesan panduan untuk kode_akun (Kolom A) - Tidak boleh kosong, harus unik
            $validationParent = $sheet->getCell("A{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Akun');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode akun yang unik.');

            // 2. Pesan panduan untuk nama_cabang (Kolom B) - Tidak boleh kosong
            $validationParent = $sheet->getCell("B{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Akun');
            $validationParent->setPrompt('Tidak boleh kosong.');

            //  3. Terapkan dropdown pada kolom normal_balance (Kolom C)
            $validation = $sheet->getCell("C{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih Normal Balance');
            $validation->setPrompt('Silakan pilih normal balance.');
            // Nilai pilihan dipisahkan koma dan diapit tanda kutip ganda di dalam string
            $validation->setFormula1('"debit,credit"');

            // 4. Pesan panduan untuk parent_id (Kolom D) - Tidak boleh kosong, sarankan -1
            $validationParent = $sheet->getCell("D{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Parent ID');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan ID induk atau -1 kemudian sesuaikan di sistem.');
            
            //  5. Terapkan dropdown pada kolom dapat_diposting (Kolom E) 
            $validation = $sheet->getCell("E{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown (1 atau 0).');
            $validation->setPromptTitle('Pilih Status');
            $validation->setPrompt('Silakan pilih 1 untuk dapat diposting atau 0 untuk tidak dapat diposting.');
            // Nilai pilihan dipisahkan koma dan diapit tanda kutip ganda di dalam string
            $validation->setFormula1('"1,0"');

            // 6. Pesan panduan untuk urutan_tampilan (Kolom F) - Tidak boleh kosong, sarankan 1
            $validationUrutan = $sheet->getCell("F{$row}")->getDataValidation();
            $validationUrutan->setShowInputMessage(true);
            $validationUrutan->setPromptTitle('Urutan Tampilan');
            $validationUrutan->setPrompt('Tidak boleh kosong. Isi dengan 1 kemudian sesuaikan di sistem.');

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

            // 8. Pesan panduan untuk urutan_tampilan (Kolom H) - Tidak boleh kosong, sarankan 1
            $validationUrutan = $sheet->getCell("H{$row}")->getDataValidation();
            $validationUrutan->setShowInputMessage(true);
            $validationUrutan->setPromptTitle('Deskripsi');
            $validationUrutan->setPrompt('Boleh kosong.');

        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}
