<?php

namespace App\Exports;

use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
use App\Models\TransactionType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountTransactionTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TransactionsSheet(),
            new AccountOptionsSheet(),
        ];
    }
}

class TransactionsSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Transactions';
    }

    public function headings(): array
    {
        return [
            'account_type',
            'account_name',
            'transaction_type',
            'debit',
            'credit',
            'transaction_date',
            'transaction_time',
            'description',
            'reference',
            'fee_amount',
        ];
    }

    public function array(): array
    {
        // 100 baris kosong untuk diisi user
        return array_fill(0, 100, array_fill(0, 10, null));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                for ($row = 2; $row <= 101; $row++) {
                    // Dropdown jenis akun
                    $this->setDropdown($sheet, "A{$row}", '=AccountTypes');

                    // Dropdown nama akun (menggunakan INDIRECT berdasarkan kolom A)
                    $this->setDropdown(
                        $sheet,
                        "B{$row}",
                        '=INDIRECT(SUBSTITUTE($A' . $row . '," ",""))'
                    );

                    // Dropdown jenis transaksi
                    $this->setDropdown($sheet, "C{$row}", '=TransactionTypes');
                }

                $sheet->getStyle('A1:J1')->getFont()->setBold(true);
                $sheet->freezePane('A2');

                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function setDropdown(Worksheet $sheet, string $cell, string $formula): void
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Pilihan tidak valid');
        $validation->setError('Pilih data yang tersedia pada dropdown.');
        $validation->setFormula1($formula);

        $sheet->getCell($cell)->setDataValidation($validation);
    }
}

class AccountOptionsSheet implements FromArray, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Options';
    }

    public function array(): array
    {
        // Asumsi masing-masing model mempunyai kolom "name".
        $cashes = CashAccount::query()->orderBy('cash_account_name')->get(['cash_account_code', 'cash_account_name']);
        $cashOption = $cashes->map(
                        fn ($cash) => $cash->cash_account_name . ' - ' . $cash->cash_account_code
                    )->all();
        
        
        $banks = BankAccount::query()->orderBy('bank_name')->get(['account_number','bank_name']);
        $bankOptions = $banks->map(
                            fn ($bank) => $bank->bank_name . ' - ' . $bank->account_number
                        )->all();

        $fintechs = FintechAccount::query()->orderBy('fintech_account_name')->get(['fintech_account_name', 'merchant_id']);
        $fintechOptions = $fintechs->map(
                            fn ($fintech) => $fintech->fintech_account_name . ' - ' . $fintech->merchant_id
                        )->all();

        $transactionTypes = TransactionType::query()->orderBy('type_name')->get(['type_code','type_name']);
        $transactionTypeOption = $transactionTypes->map(
                                    fn ($transactionTypes) => $transactionTypes->type_code. ' - ' . $transactionTypes->type_name
                                )->all();

        $accountTypes = ['Cash', 'Bank', 'Fintech'];

        $rows = [[
            'Cash',
            'Bank',
            'Fintech',
            'TransactionTypes',
            'AccountTypes',
        ]];

        $maxRows = max(
            count($cashes),
            count($banks),
            count($fintechs),
            count($transactionTypes),
            count($accountTypes)
        );

        for ($i = 0; $i < $maxRows; $i++) {
            $rows[] = [
                $cashOption[$i] ?? null,            // Kolom A [Cash]
                $bankOptions[$i] ?? null,           // Kolom B [Bank]
                $fintechOptions[$i] ?? null,        // Kolom D [Fintech]
                $transactionTypeOption[$i] ?? null, // Kolom E [TransactionTypes]
                $accountTypes[$i] ?? null,          // Kolom F [AccountTypes]
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();

                $ranges = [
                    'Cash' => 'A',
                    'Bank' => 'B',
                    'Fintech' => 'C',
                    'TransactionTypes' => 'D',
                    'AccountTypes' => 'E',
                ];

                foreach ($ranges as $name => $column) {
                    $lastRow = $sheet->getHighestDataRow($column);

                    if ($lastRow < 2) {
                        continue;
                    }

                    // Hapus Named Range lama jika sudah ada
                    $spreadsheet->removeNamedRange($name);

                    $spreadsheet->addNamedRange(
                        new NamedRange(
                            $name,
                            $sheet,
                            '$' . $column . '$2:$' . $column . '$' . $lastRow
                        )
                    );
                }
                
                // Sembunyikan sheet Options
                $sheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}