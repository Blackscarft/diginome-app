<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Utility Seeders
            RoleSeeder::class,
            UserSeeder::class,

            // COA Master Data
            AccountSeeder::class,
            
            // Master Data
            FundSeeder::class,
            BranchSeeder::class,

            // Initial Transaction Data
            TransactionTypeSeeder::class
        ]);
    }
}
