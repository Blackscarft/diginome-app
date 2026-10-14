<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BranchTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Mengembalikan data baris (kosong karena hanya butuh header template).
     */
    public function array(): array
    {
        return [
            [
                'KODE001',          // kode_cabang
                'Cabang Utama',     // nama_cabang
                'Kantor Cabang Pusat', // deskripsi
                '-1',               // parent_id (diisi -1 jika tidak ada parent)
                '1',                // urutan_tampilan (tidak boleh kosong)
                '1',                // status_aktif (1 = aktif, 0 = non-aktif)
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
            'kode_cabang',
            'nama_cabang',
            'deskripsi',
            'parent_id',
            'urutan_tampilan',
            'status_aktif',
        ];
    }

    /**
     * Styling baris header (misal: tebal/bold).
     */
    public function styles(Worksheet $sheet): array
    {
        for ($row = 2; $row <= 100; $row++) {
            //  1. Terapkan dropdown pada kolom status_aktif (Kolom F) dari baris 2 sampai 100
            $validation = $sheet->getCell("F{$row}")->getDataValidation();
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

            // 2. Pesan panduan untuk kode_cabang (Kolom A) - Tidak boleh kosong, harus unik
            $validationParent = $sheet->getCell("A{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Cabang');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode cabang yang unik.');

            // 3. Pesan panduan untuk nama_cabang (Kolom B) - Tidak boleh kosong
            $validationParent = $sheet->getCell("B{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Cabang');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 4. Pesan panduan untuk parent_id (Kolom D) - Tidak boleh kosong, sarankan -1
            $validationParent = $sheet->getCell("D{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Parent ID');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan ID induk atau -1 kemudian sesuaikan di sistem.');

            // 5. Pesan panduan untuk urutan_tampilan (Kolom E) - Tidak boleh kosong, sarankan 1
            $validationUrutan = $sheet->getCell("E{$row}")->getDataValidation();
            $validationUrutan->setShowInputMessage(true);
            $validationUrutan->setPromptTitle('Urutan Tampilan');
            $validationUrutan->setPrompt('Tidak boleh kosong. Isi dengan 1 kemudian sesuaikan di sistem.');
        }


        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
