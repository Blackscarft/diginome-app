<?php

namespace App\Exports;

use App\Models\Fund;
use App\Models\FundProgram;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class FundProgramsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            // ['FP001', 'Program Pendidikan A', 'Deskripsi Program', 'Pendidikan', 1],
            // ['FP002', 'Program Sosial B', 'Deskripsi Program', 'Sosial Kemanusiaan dan Lingkungan', 1],
        ];
    }

    public function headings(): array
    {
        return [
            'parent_id',
            'jenis_sumber',
            'kode_program',
            'nama_program',
            'deskripsi',
            'kategori',
            'saldo_initial',
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
            "SHOW COLUMNS FROM fund_programs WHERE Field = 'category'"
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


        $sumber = Fund::query()
                    ->where('parent_id', '!=', -1)
                    ->get()
                    ->mapWithKeys(fn ($record) => [
                        $record->id => "{$record->fund_code} - {$record->fund_name}",
                    ]);
        $sumberList = '"' . $sumber->values()->implode(',') . '"';

        for ($row = 2; $row <= 100; $row++) {
            // Parent ID
            $validationParent = $sheet->getCell("A{$row}")->getDataValidation();
            $validationParent->setShowInputMessage(true);
            $validationParent->setPromptTitle('Parent ID');
            $validationParent->setPrompt('Tidak boleh kosong. Isi dengan ID induk dengan kategori kolektif atau -1 kemudian sesuaikan di sistem.');

            // Jenis Sumber
            $validation = $sheet->getCell("B{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih jenis sumber');
            $validation->setPrompt('Silakan pilih jenis sumber.');
            $validation->setFormula1($sumberList);
            
            // Kategori
            $validation = $sheet->getCell("F{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input tidak valid');
            $validation->setError('Harap pilih nilai dari dropdown.');
            $validation->setPromptTitle('Pilih kategori');
            $validation->setPrompt('Silakan pilih kategori.');
            $validation->setFormula1($kategoriList);

            // is_active
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
