<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class TransactionTypesTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Mengembalikan data baris (kosong karena hanya butuh header template).
     */
    public function array(): array
    {
        return [
            [
                'EXP',                              // kode_tipe
                'Pengeluaran',                      // nama_tipe
                'Uang keluar untuk operasional',    // deskripsi
                'Expense',                          // kategori
                '1',                                // status_aktif
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
            'kode_tipe',
            'nama_tipe',
            'deskripsi',
            'kategori',
            'status_aktif'
        ];
    }

    /**
     * Styling baris header (misal: tebal/bold).
     */
    public function styles(Worksheet $sheet): array
    {
        // Ambil tipe kolom enum dari database
        $enumCategory = DB::select(
            "SHOW COLUMNS FROM transaction_types WHERE Field = 'category'"
        );

        if (empty($enumCategory)) {
            throw new \RuntimeException('Kolom category tidak ditemukan.');
        }

        // Ekstrak nilai enum
        preg_match("/^enum\((.*)\)$/", $enumCategory[0]->Type, $matches);

        $enumValues = [];

        if (!empty($matches[1])) {
            preg_match_all("/'((?:[^']|'')*)'/", $matches[1], $values);

            $enumValues = array_map(
                fn ($value) => str_replace("''", "'", $value),
                $values[1]
            );
        }

        // Buat daftar dropdown
        $kategoriList = '"' . implode(',', $enumValues) . '"';

        for ($row = 2; $row <= 100; $row++) {
              // 1. Pesan panduan untuk kode_tipe (Kolom A) - Tidak boleh kosong
            $validationParent = $sheet->getCell("A{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Kode Bank');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan kode akun yang unik');

            // 2. Pesan panduan untuk nama_tipe (Kolom B) - Tidak boleh kosong
            $validationParent = $sheet->getCell("B{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Nama Bank');
            $validationParent->setPrompt('Tidak boleh kosong.');

            // 3. Deskripsi boleh kosong

            //  4. Terapkan dropdown pada kolom kategori (Kolom D)
            $validation = $sheet->getCell("D{$row}")->getDataValidation();
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
            $validation->setFormula1($kategoriList);

             //  5. Terapkan dropdown pada kolom status_aktif (Kolom E)
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
            $validation->setPrompt('Silakan pilih 1 untuk aktif atau 0 untuk non-aktif.');
            // Nilai pilihan dipisahkan koma dan diapit tanda kutip ganda di dalam string
            $validation->setFormula1('"1,0"');

        }


        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
