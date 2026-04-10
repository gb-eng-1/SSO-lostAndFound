<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Full reset: truncates transactional tables, then delegates to TestDataSeeder
 * for students, items, claims, and all supporting data.
 *
 * Run after pointing .env at the correct database:
 *   php artisan db:seed --class=CleanInstallSeeder
 */
class CleanInstallSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TestDataSeeder::class);
    }
}
