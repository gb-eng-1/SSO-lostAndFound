<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Adds or updates the four UB-format test students without truncating other data.
 *
 *   php artisan db:seed --class=TestStudentsSeeder
 */
class TestStudentsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('students')) {
            return;
        }

        $now = now();
        $hash = Hash::make('Password123');

        $rows = [
            ['2401001', 'lea.robles@ub.edu.ph', 'Lea Robles', 'CICT'],
            ['2401002', 'marco.vega@ub.edu.ph', 'Marco Vega', 'CITE'],
            ['2401003', 'dina.cruz@ub.edu.ph', 'Dina Cruz', 'CBA'],
            ['2401004', 'jay.ortiz@ub.edu.ph', 'Jay Ortiz', 'CAS'],
        ];

        foreach ($rows as [$studentId, $email, $name, $dept]) {
            DB::table('students')->updateOrInsert(
                ['email' => $email],
                [
                    'student_id'    => $studentId,
                    'password_hash' => $hash,
                    'name'          => $name,
                    'department'    => $dept,
                    'phone'         => null,
                    'updated_at'    => $now,
                ]
            );
        }

        // Ensure created_at on rows that were just inserted (updateOrInsert update path skips it)
        DB::table('students')
            ->whereIn('email', array_column($rows, 1))
            ->whereNull('created_at')
            ->update(['created_at' => $now]);
    }
}
