<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // AKUN AKUNTANSI Asset
        // ==========================================
        $aset = Account::firstOrCreate([
            'parent_id' => -1,
            'order' => 1,
            'code' => '1-0000',
            'name' => 'Aset',
            'normal_balance' => 'debit',
            'is_postable' => false,
            'is_active' => true,
        ]);

            $aset_lancar = Account::firstOrCreate([
                'parent_id' => $aset->id,
                'order' => 1,
                'code' => '1-1000',
                'name' => 'Aset Lancar',
                'normal_balance' => 'debit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                $kas = Account::firstOrCreate([
                    'parent_id' => $aset_lancar->id,
                    'order' => 1,
                    'code' => '1-1100',
                    'name' => 'Kas',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $kas->id,
                        'order' => 1,
                        'code' => '1-1101',
                        'name' => 'Kas Brangkas Pusat',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $kas->id,
                        'order' => 2,
                        'code' => '1-1102',
                        'name' => 'Kas Cabang',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);
                
                $bank = Account::firstOrCreate([
                    'parent_id' => $aset_lancar->id,
                    'order' => 2,
                    'code' => '1-1200',
                    'name' => 'Bank',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $bank->id,
                        'order' => 1,
                        'code' => '1-1201',
                        'name' => 'Bank Syariah',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $bank->id,
                        'order' => 2,
                        'code' => '1-1202',
                        'name' => 'Koperasi Syariah',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $bank->id,
                        'order' => 3,
                        'code' => '1-1203',
                        'name' => 'Bank Konvensional',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                $piutang = Account::firstOrCreate([
                    'parent_id' => $aset_lancar->id,
                    'order' => 3,
                    'code' => '1-1300',
                    'name' => 'Piutang',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $piutang->id,
                        'order' => 1,
                        'code' => '1-1301',
                        'name' => 'Piutang Dana',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $piutang->id,
                        'order' => 2,
                        'code' => '1-1302',
                        'name' => 'Piutang Pihak Ketiga',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);
                
                $persediaan = Account::firstOrCreate([
                    'parent_id' => $aset_lancar->id,
                    'order' => 4,
                    'code' => '1-1400',
                    'name' => 'Persediaan',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $persediaan->id,
                        'order' => 1,
                        'code' => '1-1401',
                        'name' => 'Persediaan Barang',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);
                
                $uang_muka = Account::firstOrCreate([
                    'parent_id' => $aset_lancar->id,
                    'order' => 5,
                    'code' => '1-1500',
                    'name' => 'Uang Muka',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $uang_muka->id,
                        'order' => 1,
                        'code' => '1-1501',
                        'name' => 'Uang Muka Kegiatan',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $uang_muka->id,
                        'order' => 1,
                        'code' => '1-1502',
                        'name' => 'Uang Muka Sewa',
                        'normal_balance' => 'debit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);


            // Sub-Akun Aset: Aset Tidak Lancar (Assets)
            $aset_tidak_lancar = Account::firstOrCreate([
                'parent_id' => $aset->id,
                'order' => 2,
                'code' => '1-2000',
                'name' => 'Aset Tidak Lancar',
                'normal_balance' => 'debit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                $tanah = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 1,
                    'code' => '1-2100',
                    'name' => 'Tanah',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);
                
                $bangunan = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 2,
                    'code' => '1-2200',
                    'name' => 'Bangunan',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                $kendaraan = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 3,
                    'code' => '1-2300',
                    'name' => 'Kendaraan',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                $inventaris = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 4,
                    'code' => '1-2400',
                    'name' => 'Inventaris',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);


                $akumulasi_penyusutan = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 5,
                    'code' => '1-2500',
                    'name' => 'Akumulasi Penyusutan',
                    'normal_balance' => 'credit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                $investasi = Account::firstOrCreate([
                    'parent_id' => $aset_tidak_lancar->id,
                    'order' => 6,
                    'code' => '1-2600',
                    'name' => 'Investasi',
                    'normal_balance' => 'debit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

        // ==========================================
        // AKUN AKUNTANSI Kewajiban (Liabilities)
        // ==========================================

        $kewajiban = Account::firstOrCreate([
            'parent_id' => -1,
            'order' => 2,
            'code' => '2-0000',
            'name' => 'Kewajiban',
            'normal_balance' => 'credit',
            'is_postable' => false,
            'is_active' => true,
        ]);

            //Kewajiban Lancar (jangka pendek)
            $kewajiban_lancar = Account::firstOrCreate([ 
                'parent_id' => $kewajiban->id,
                'order' => 1,
                'code' => '2-1000',
                'name' => 'Kewajiban Lancar',
                'normal_balance' => 'credit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                $hutang = Account::firstOrCreate([
                    'parent_id' => $kewajiban_lancar->id,
                    'order' => 1,
                    'code' => '2-1100',
                    'name' => 'Hutang',
                    'normal_balance' => 'credit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $hutang->id,
                        'order' => 2,
                        'code' => '2-1101',
                        'name' => 'Hutang Pihak Dana',
                        'normal_balance' => 'credit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $hutang->id,
                        'order' => 2,
                        'code' => '2-1102',
                        'name' => 'Hutang Pihak Ketiga',
                        'normal_balance' => 'credit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

            $kewajiban_tidak_lancar = Account::firstOrCreate([
                'parent_id' => $kewajiban->id,
                'order' => 2,
                'code' => '2-2000',
                'name' => 'Kewajiban Tidak Lancar',
                'normal_balance' => 'credit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                $hutang_jangka_panjang = Account::firstOrCreate([
                    'parent_id' => $kewajiban_tidak_lancar->id,
                    'order' => 1,
                    'code' => '2-2100',
                    'name' => 'Hutang Jangka Panjang',
                    'normal_balance' => 'credit',
                    'is_postable' => false,
                    'is_active' => true,
                ]);

                    Account::firstOrCreate([
                        'parent_id' => $hutang_jangka_panjang->id,
                        'order' => 2,
                        'code' => '2-2101',
                        'name' => 'Hutang Pihak Dana',
                        'normal_balance' => 'credit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

                    Account::firstOrCreate([
                        'parent_id' => $hutang_jangka_panjang->id,
                        'order' => 2,
                        'code' => '2-2102',
                        'name' => 'Hutang Pihak Ketiga',
                        'normal_balance' => 'credit',
                        'is_postable' => true,
                        'is_active' => true,
                    ]);

        // ==========================================
        // AKUN AKUNTANSI Modal / Dana (Equity)
        // ==========================================
        $dana = Account::firstOrCreate([
            'parent_id' => -1,
            'order' => 3,
            'code' => '3-0000',
            'name' => 'Dana',
            'normal_balance' => 'credit',
            'is_postable' => false,
            'is_active' => true,
        ]);

            Account::firstOrCreate([
                'parent_id' => $dana->id,
                'order' => 1,
                'code' => '3-1000',
                'name' => 'Dana Zakat',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

            Account::firstOrCreate([
                'parent_id' => $dana->id,
                'order' => 2,
                'code' => '3-2000',
                'name' => 'Dana Infaq/Sedekah',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

            Account::firstOrCreate([
                'parent_id' => $dana->id,
                'order' => 3,
                'code' => '3-3000',
                'name' => 'Dana Amil',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

        // ==========================================
        // AKUN AKUNTANSI Penerimaan (Revenue)
        // ==========================================

        $pengerimaan = Account::firstOrCreate([
            'parent_id' => -1,
            'order' => 4,
            'code' => '4-0000',
            'name' => 'Penerimaan',
            'normal_balance' => 'credit',
            'is_postable' => false,
            'is_active' => true,
        ]);

            Account::firstOrCreate([
                'parent_id' => $pengerimaan->id,
                'order' => 1,
                'code' => '4-1000',
                'name' => 'Penerimaan Zakat',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

            Account::firstOrCreate([
                'parent_id' => $pengerimaan->id,
                'order' => 2,
                'code' => '4-2000',
                'name' => 'Penerimaan Infaq/Sedekah',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

            Account::firstOrCreate([
                'parent_id' => $pengerimaan->id,
                'order' => 3,
                'code' => '4-3000',
                'name' => 'Penerimaan Amil',
                'normal_balance' => 'credit',
                'is_postable' => true,
                'is_active' => true,
            ]);

        // ==========================================
        // AKUN AKUNTANSI Pengeluaran / Penyaluran (Expense)
        // ==========================================

        $pengeluaran = Account::firstOrCreate([
            'parent_id' => -1,
            'order' => 5,
            'code' => '5-0000',
            'name' => 'Penyaluran/Pengeluaran',
            'normal_balance' => 'debit',
            'is_postable' => false,
            'is_active' => true,
        ]);

            $penyaluran = Account::firstOrCreate([
                'parent_id' => $pengeluaran->id,
                'order' => 1,
                'code' => '5-1000',
                'name' => 'Penyaluran',
                'normal_balance' => 'debit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                Account::firstOrCreate([
                    'parent_id' => $penyaluran->id,
                    'order' => 1,
                    'code' => '5-1100',
                    'name' => 'Penyaluran Zakat',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

                Account::firstOrCreate([
                    'parent_id' => $penyaluran->id,
                    'order' => 2,
                    'code' => '5-1200',
                    'name' => 'Penyaluran Infaq/Sedekah',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

            $beban = Account::firstOrCreate([
                'parent_id' => $pengeluaran->id,
                'order' => 2,
                'code' => '5-2000',
                'name' => 'Beban',
                'normal_balance' => 'debit',
                'is_postable' => false,
                'is_active' => true,
            ]);

                Account::firstOrCreate([
                    'parent_id' => $beban->id,
                    'order' => 1,
                    'code' => '5-2100',
                    'name' => 'Beban Umum & Operasional',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

                Account::firstOrCreate([
                    'parent_id' => $beban->id,
                    'order' => 2,
                    'code' => '5-2200',
                    'name' => 'Beban Belanja Pegawai',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

                Account::firstOrCreate([
                    'parent_id' => $beban->id,
                    'order' => 3,
                    'code' => '5-2300',
                    'name' => 'Beban Marketing',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

                Account::firstOrCreate([
                    'parent_id' => $beban->id,
                    'order' => 4,
                    'code' => '5-2400',
                    'name' => 'Beban Penyusutan',
                    'normal_balance' => 'debit',
                    'is_postable' => true,
                    'is_active' => true,
                ]);

    }
}
