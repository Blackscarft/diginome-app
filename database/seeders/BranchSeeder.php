<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pusat = Branch::firstOrCreate(['branch_code'=> 'PST'], [
            'parent_id' => -1,
            'order' => 1,
            'branch_name' => 'Pusat',
            'description' => 'Cabang Pusat.',
            'is_active' => true,
        ]);

            $pusatFund = Branch::firstOrCreate(['branch_code'=> 'PST-FUND'], [
                'parent_id' => $pusat->id,
                'order' => 1,
                'branch_name' => 'Pusat Fundraising',
                'description' => 'Cabang pusat bagian fundraising.',
                'is_active' => true,
            ]);

            $pusatPdg = Branch::firstOrCreate(['branch_code'=> 'PST-PDG'], [
                'parent_id' => $pusat->id,
                'order' => 2,
                'branch_name' => 'Pusat Pendayagunaan',
                'description' => 'Cabang pusat bagian pendayagunaan.',
                'is_active' => true,
            ]);

            $pusatOpt = Branch::firstOrCreate(['branch_code'=> 'PST-OPT'], [
                'parent_id' => $pusat->id,
                'order' => 3,
                'branch_name' => 'Pusat Operating',
                'description' => 'Cabang Pusat bagian operating.',
                'is_active' => true,
            ]);

                // Sub-branch for Pusat Operating
                Branch::firstOrCreate(['branch_code'=> 'PST-OPT-KEU'], [
                    'parent_id' => $pusatOpt->id,
                    'order' => 1,
                    'branch_name' => 'Pusat Operating Keuangan',
                    'description' => 'Cabang Pusat bagian operating keuangan.',
                    'is_active' => true,
                ]);

                Branch::firstOrCreate(['branch_code'=> 'PST-OPT-IT'], [
                    'parent_id' => $pusatOpt->id,
                    'order' => 2,
                    'branch_name' => 'Pusat Operating IT',
                    'description' => 'Cabang Pusat bagian operating IT.',
                    'is_active' => true,
                ]);

                Branch::firstOrCreate(['branch_code'=> 'PST-OPT-GA'], [
                    'parent_id' => $pusatOpt->id,
                    'order' => 3,
                    'branch_name' => 'Pusat Operating General Affairs',
                    'description' => 'Cabang Pusat bagian operating general affairs.',
                    'is_active' => true,
                ]);
            
            $pusatHumas = Branch::firstOrCreate(['branch_code'=> 'PST-HUMAS'], [
                'parent_id' => $pusat->id,
                'order' => 4,
                'branch_name' => 'Pusat Humas',
                'description' => 'Cabang pusat bagian humas.',
                'is_active' => true,
            ]);

            $pusatSekretariat = Branch::firstOrCreate(['branch_code'=> 'PST-SEK'], [
                'parent_id' => $pusat->id,
                'order' => 5,
                'branch_name' => 'Pusat Sekretariat',
                'description' => 'Cabang pusat bagian sekretariat.',
                'is_active' => true,
            ]);
        
        $solo = Branch::firstOrCreate(['branch_code'=> 'SLO'], [
            'parent_id' => -1,
            'order' => 2,
            'branch_name' => 'Solo',
            'description' => 'Cabang Solo.',
            'is_active' => true,
        ]);

        $karanganyar = Branch::firstOrCreate(['branch_code'=> 'KRA'], [
            'parent_id' => -1,
            'order' => 3,
            'branch_name' => 'Karanganyar',
            'description' => 'Cabang Karanganyar.',
            'is_active' => true,
        ]);

        $sragen = Branch::firstOrCreate(['branch_code'=> 'SRA'], [
            'parent_id' => -1,
            'order' => 4,
            'branch_name' => 'Sragen',
            'description' => 'Cabang Sragen.',
            'is_active' => true,
        ]);

        $sukoharjo = Branch::firstOrCreate(['branch_code'=> 'SKH'], [
            'parent_id' => -1,
            'order' => 5,
            'branch_name' => 'Sukoharjo',
            'description' => 'Cabang Sukoharjo.',
            'is_active' => true,
        ]);

        $wonogiri = Branch::firstOrCreate(['branch_code'=> 'WNG'], [
            'parent_id' => -1,
            'order' => 6,
            'branch_name' => 'Wonogiri',
            'description' => 'Cabang Wonogiri.',
            'is_active' => true,
        ]);

        $klaten = Branch::firstOrCreate(['branch_code'=> 'KTN'], [
            'parent_id' => -1,
            'order' => 7,
            'branch_name' => 'Klaten',
            'description' => 'Cabang Klaten.',
            'is_active' => true,
        ]);

        $boyolali = Branch::firstOrCreate(['branch_code'=> 'BYL'], [
            'parent_id' => -1,
            'order' => 8,
            'branch_name' => 'Boyolali',
            'description' => 'Cabang Boyolali.',
            'is_active' => true,
        ]);

        $salatiga = Branch::firstOrCreate(['branch_code'=> 'SLA'], [
            'parent_id' => -1,
            'order' => 9,
            'branch_name' => 'Salatiga',
            'description' => 'Cabang Salatiga.',
            'is_active' => true,
        ]);

        $semarang = Branch::firstOrCreate(['branch_code'=> 'SMG'], [
            'parent_id' => -1,
            'order' => 10,
            'branch_name' => 'Semarang',
            'description' => 'Cabang Semarang.',
            'is_active' => true,
        ]);

        $wonosobo = Branch::firstOrCreate(['branch_code'=> 'WNS'], [
            'parent_id' => -1,
            'order' => 11,
            'branch_name' => 'Wonosobo',
            'description' => 'Cabang Wonosobo.',
            'is_active' => true,
        ]);

        $online = Branch::firstOrCreate(['branch_code'=> 'ONL'], [
            'parent_id' => -1,
            'order' => 12,
            'branch_name' => 'Online',
            'description' => 'Cabang Online.',
            'is_active' => true,
        ]);

        $mitra_maslahat = Branch::firstOrCreate(['branch_code'=> 'MM'], [
            'parent_id' => -1,
            'order' => 13,
            'branch_name' => 'Mitra Maslahat',
            'description' => 'Cabang Mitra Maslahat.',
            'is_active' => true,
        ]);

        $mitra_kepanitiaan_zakat = Branch::firstOrCreate(['branch_code'=> 'MKZ'], [
            'parent_id' => -1,
            'order' => 14,
            'branch_name' => 'Mitra Kepanitiaan Zakat',
            'description' => 'Cabang Mitra Kepanitiaan Zakat.',
            'is_active' => true,
        ]);

        $solusi_wakaf = Branch::firstOrCreate(['branch_code'=> 'SWI'], [
            'parent_id' => -1,
            'order' => 15,
            'branch_name' => 'Solusi Wakaf',
            'description' => 'Cabang Solusi Wakaf.',
            'is_active' => true,
        ]);
    }
}
