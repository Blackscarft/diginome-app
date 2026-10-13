<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionTypes = [
            [
                'type_code' => 'PHP',
                'type_name' => 'PHP',
                'description' => 'Uang masuk dari donasi.',
                'category' => 'income',
                'is_active' => true,
            ],
            [
                'type_code' => 'BSL',
                'type_name' => 'Basil',
                'description' => 'Uang masuk hasil dari bagi hasil.',
                'category' => 'income',
                'is_active' => true,
            ],
            [
                'type_code' => 'STR',
                'type_name' => 'Setoran Area',
                'description' => 'Uang masuk dari setoran donasi area.',
                'category' => 'income',
                'is_active' => true,
            ],
            [
                'type_code' => 'ADM_BANK',
                'type_name' => 'Biaya Administrasi Bank',
                'description' => 'Uang keluar untuk biaya administrasi bank.',
                'category' => 'expense',
                'is_active' => true,
            ],
            [
                'type_code' => 'ADM_TF',
                'type_name' => 'Biaya Administrasi Transfer',
                'description' => 'Uang keluar untuk biaya administrasi transfer.',
                'category' => 'expense',
                'is_active' => true,
            ],
            [
                'type_code' => 'TAX',
                'type_name' => 'Pajak',
                'description' => 'Uang keluar untuk biaya pajak.',
                'category' => 'expense',
                'is_active' => true,
            ],
            [
                'type_code' => 'TRF_IN',
                'type_name' => 'Transfer Masuk',
                'description' => 'Uang masuk ke akun tujuan dari akun internal lain.',
                'category' => 'transfer',
                'is_active' => true,
            ],
            [
                'type_code' => 'TRF_OUT',
                'type_name' => 'Transfer Keluar',
                'description' => 'Uang keluar dari akun asal untuk dipindah ke akun internal lain.',
                'category' => 'transfer',
                'is_active' => true,
            ],
            [
                'type_code' => 'MKZ_IN',
                'type_name' => 'MKZ Masuk.',
                'description' => 'Uang masuk dari mitra zakat.',
                'category' => 'income',
                'is_active' => true,
            ],
            [
                'type_code' => 'MKZ_OUT',
                'type_name' => 'MKZ Keluar.',
                'description' => 'Uang keluar ke mitra zakat.',
                'category' => 'expense',
                'is_active' => true,
            ],
            [
                'type_code' => 'OBS_IN',
                'type_name' => 'Dana Titipan Masuk.',
                'description' => 'Uang masuk dari pihak ketiga di luar pengelolaan.',
                'category' => 'income',
                'is_active' => true,
            ],
            [
                'type_code' => 'OBS_OUT',
                'type_name' => 'Dana Titipan Keluar.',
                'description' => 'Uang keluar dari dana titipan.',
                'category' => 'expense',
                'is_active' => true,
            ],
            [
                'type_code' => 'ADJ',
                'type_name' => 'Penyesuaian',
                'description' => 'Koreksi selisih pencatatan atau opname kas fisik.',
                'category' => 'adjustment',
                'is_active' => true,
            ]
        ];

        foreach ($transactionTypes as $type) {
            TransactionType::firstOrCreate(['type_code' => $type['type_code']], $type);
        }
    }
}
