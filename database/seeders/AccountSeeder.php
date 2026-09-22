<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. ASET
        // ==========================================
        $aset = Account::firstOrCreate(['code' => '1-0000'], [
            'parent_id' => -1, 'order' => 1, 'name' => 'Aset', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
        ]);

            // Aset Lancar
            $aset_lancar = Account::firstOrCreate(['code' => '1-1000'], [
                'parent_id' => $aset->id, 'order' => 1, 'name' => 'Aset Lancar', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
            ]);

                $kas = Account::firstOrCreate(['code' => '1-1100'], [
                    'parent_id' => $aset_lancar->id, 'order' => 1, 'name' => 'Kas', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '1-1101'], ['parent_id' => $kas->id, 'order' => 1, 'name' => 'Kas Brangkas Pusat', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '1-1102'], ['parent_id' => $kas->id, 'order' => 2, 'name' => 'Kas Cabang', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

                $bank = Account::firstOrCreate(['code' => '1-1200'], [
                    'parent_id' => $aset_lancar->id, 'order' => 2, 'name' => 'Bank', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '1-1201'], ['parent_id' => $bank->id, 'order' => 1, 'name' => 'Bank Syariah', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '1-1202'], ['parent_id' => $bank->id, 'order' => 2, 'name' => 'Koperasi Syariah', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '1-1203'], ['parent_id' => $bank->id, 'order' => 3, 'name' => 'Bank Konvensional', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

                $piutang = Account::firstOrCreate(['code' => '1-1300'], [
                    'parent_id' => $aset_lancar->id, 'order' => 3, 'name' => 'Piutang', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '1-1301'], ['parent_id' => $piutang->id, 'order' => 1, 'name' => 'Piutang Dana', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '1-1302'], ['parent_id' => $piutang->id, 'order' => 2, 'name' => 'Piutang Pihak Ketiga', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

                $persediaan = Account::firstOrCreate(['code' => '1-1400'], [
                    'parent_id' => $aset_lancar->id, 'order' => 4, 'name' => 'Persediaan', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '1-1401'], ['parent_id' => $persediaan->id, 'order' => 1, 'name' => 'Persediaan Barang', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

                $uang_muka = Account::firstOrCreate(['code' => '1-1500'], [
                    'parent_id' => $aset_lancar->id, 'order' => 5, 'name' => 'Uang Muka', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '1-1501'], ['parent_id' => $uang_muka->id, 'order' => 1, 'name' => 'Uang Muka Kegiatan', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '1-1502'], ['parent_id' => $uang_muka->id, 'order' => 2, 'name' => 'Uang Muka Sewa', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

            // Aset Tidak Lancar
            $aset_tidak_lancar = Account::firstOrCreate(['code' => '1-2000'], [
                'parent_id' => $aset->id, 'order' => 2, 'name' => 'Aset Tidak Lancar', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
            ]);
                Account::firstOrCreate(['code' => '1-2100'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 1, 'name' => 'Tanah', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true]);
                Account::firstOrCreate(['code' => '1-2200'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 2, 'name' => 'Bangunan', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true]);
                Account::firstOrCreate(['code' => '1-2300'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 3, 'name' => 'Kendaraan', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true]);
                Account::firstOrCreate(['code' => '1-2400'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 4, 'name' => 'Inventaris', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true]);
                Account::firstOrCreate(['code' => '1-2500'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 5, 'name' => 'Akumulasi Penyusutan', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true]);
                Account::firstOrCreate(['code' => '1-2600'], ['parent_id' => $aset_tidak_lancar->id, 'order' => 6, 'name' => 'Investasi', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true]);

        // ==========================================
        // 2. KEWAJIBAN
        // ==========================================
        $kewajiban = Account::firstOrCreate(['code' => '2-0000'], [
            'parent_id' => -1, 'order' => 2, 'name' => 'Kewajiban', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
        ]);

            // Kewajiban Lancar
            $kewajiban_lancar = Account::firstOrCreate(['code' => '2-1000'], [
                'parent_id' => $kewajiban->id, 'order' => 1, 'name' => 'Kewajiban Lancar', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
            ]);
                $hutang = Account::firstOrCreate(['code' => '2-1100'], [
                    'parent_id' => $kewajiban_lancar->id, 'order' => 1, 'name' => 'Hutang', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '2-1101'], ['parent_id' => $hutang->id, 'order' => 1, 'name' => 'Hutang Pihak Dana', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '2-1102'], ['parent_id' => $hutang->id, 'order' => 2, 'name' => 'Hutang Pihak Ketiga', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);

            // Kewajiban Tidak Lancar
            $kewajiban_tidak_lancar = Account::firstOrCreate(['code' => '2-2000'], [
                'parent_id' => $kewajiban->id, 'order' => 2, 'name' => 'Kewajiban Tidak Lancar', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
            ]);
                $hutang_jangka_panjang = Account::firstOrCreate(['code' => '2-2100'], [
                    'parent_id' => $kewajiban_tidak_lancar->id, 'order' => 1, 'name' => 'Hutang Jangka Panjang', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
                ]);
                    Account::firstOrCreate(['code' => '2-2101'], ['parent_id' => $hutang_jangka_panjang->id, 'order' => 1, 'name' => 'Hutang Pihak Dana', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
                    Account::firstOrCreate(['code' => '2-2102'], ['parent_id' => $hutang_jangka_panjang->id, 'order' => 2, 'name' => 'Hutang Pihak Ketiga', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);

        // ==========================================
        // 3. DANA / EKUITAS
        // ==========================================
        $dana = Account::firstOrCreate(['code' => '3-0000'], [
            'parent_id' => -1, 'order' => 3, 'name' => 'Dana', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
        ]);
            Account::firstOrCreate(['code' => '3-1000'], ['parent_id' => $dana->id, 'order' => 1, 'name' => 'Dana Zakat', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
            Account::firstOrCreate(['code' => '3-2000'], ['parent_id' => $dana->id, 'order' => 2, 'name' => 'Dana Infaq/Sedekah', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
            Account::firstOrCreate(['code' => '3-3000'], ['parent_id' => $dana->id, 'order' => 3, 'name' => 'Dana Amil', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);

        // ==========================================
        // 4. PENERIMAAN
        // ==========================================
        $penerimaan = Account::firstOrCreate(['code' => '4-0000'], [
            'parent_id' => -1, 'order' => 4, 'name' => 'Penerimaan', 'normal_balance' => 'credit', 'is_postable' => false, 'is_active' => true,
        ]);
            Account::firstOrCreate(['code' => '4-1000'], ['parent_id' => $penerimaan->id, 'order' => 1, 'name' => 'Penerimaan Zakat', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
            Account::firstOrCreate(['code' => '4-2000'], ['parent_id' => $penerimaan->id, 'order' => 2, 'name' => 'Penerimaan Infaq/Sedekah', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);
            Account::firstOrCreate(['code' => '4-3000'], ['parent_id' => $penerimaan->id, 'order' => 3, 'name' => 'Penerimaan Amil', 'normal_balance' => 'credit', 'is_postable' => true, 'is_active' => true]);

        // ==========================================
        // 5. PENYALURAN / PENGELUARAN
        // ==========================================
        $pengeluaran = Account::firstOrCreate(['code' => '5-0000'], [
            'parent_id' => -1, 'order' => 5, 'name' => 'Penyaluran/Pengeluaran', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
        ]);
            $penyaluran = Account::firstOrCreate(['code' => '5-1000'], [
                'parent_id' => $pengeluaran->id, 'order' => 1, 'name' => 'Penyaluran', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
            ]);
                Account::firstOrCreate(['code' => '5-1100'], ['parent_id' => $penyaluran->id, 'order' => 1, 'name' => 'Penyaluran Zakat', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                Account::firstOrCreate(['code' => '5-1200'], ['parent_id' => $penyaluran->id, 'order' => 2, 'name' => 'Penyaluran Infaq/Sedekah', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);

            $beban = Account::firstOrCreate(['code' => '5-2000'], [
                'parent_id' => $pengeluaran->id, 'order' => 2, 'name' => 'Beban', 'normal_balance' => 'debit', 'is_postable' => false, 'is_active' => true,
            ]);
                Account::firstOrCreate(['code' => '5-2100'], ['parent_id' => $beban->id, 'order' => 1, 'name' => 'Beban Umum & Operasional', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                Account::firstOrCreate(['code' => '5-2200'], ['parent_id' => $beban->id, 'order' => 2, 'name' => 'Beban Belanja Pegawai', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                Account::firstOrCreate(['code' => '5-2300'], ['parent_id' => $beban->id, 'order' => 3, 'name' => 'Beban Marketing', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
                Account::firstOrCreate(['code' => '5-2400'], ['parent_id' => $beban->id, 'order' => 4, 'name' => 'Beban Penyusutan', 'normal_balance' => 'debit', 'is_postable' => true, 'is_active' => true]);
    }
}