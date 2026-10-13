<?php

namespace Database\Seeders;

use App\Models\Fund;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zakat = Fund::firstOrCreate(['fund_code'=> 'ZKT'], [
            'parent_id' => -1,
            'order' => 1,
            'fund_name' => 'Zakat',
            'description' => 'Dana Zakat.',
            'is_active' => true,
        ]);

            // Sub-fund for Zakat
            Fund::firstOrCreate(['fund_code'=> 'ZKT-FIT'], [
                'parent_id' => $zakat->id,
                'order' => 1,
                'fund_name' => 'Zakat Fitrah',
                'description' => 'Dana Zakat Fitrah.',
                'is_active' => true,
            ]);

            Fund::firstOrCreate(['fund_code'=> 'ZKT-MAL'], [
                'parent_id' => $zakat->id,
                'order' => 2,
                'fund_name' => 'Zakat Mal',
                'description' => 'Dana Zakat Mal.',
                'is_active' => true,
            ]);
        
        $infaqSedekah = Fund::firstOrCreate(['fund_code'=> 'INF'], [
            'parent_id' => -1,
            'order' => 2,
            'fund_name' => 'Infaq/Sedekah',
            'description' => 'Dana Infaq/Sedekah.',
            'is_active' => true,
        ]);
            // Sub-fund for Infaq
            Fund::firstOrCreate(['fund_code'=> 'INF-UMUM'], [
                'parent_id' => $infaqSedekah->id,
                'order' => 1,
                'fund_name' => 'Infaq/Sedekah tidak terikat',
                'description' => 'Dana Infaq/Sedekah tidak terikat.',
                'is_active' => true,
            ]);

            Fund::firstOrCreate(['fund_code'=> 'INF-TER'], [
                'parent_id' => $infaqSedekah->id,
                'order' => 2,
                'fund_name' => 'Infaq/Sedekah terikat',
                'description' => 'Dana Infaq/Sedekah terikat.',
                'is_active' => true,
            ]);
        
        $wakaf = Fund::firstOrCreate(['fund_code'=> 'WKF'], [
            'parent_id' => -1,
            'order' => 3,
            'fund_name' => 'Wakaf',
            'description' => 'Dana Wakaf.',
            'is_active' => true,
        ]);
    }
}
