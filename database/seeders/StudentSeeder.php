<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('students')) {
            $this->command->warn('StudentSeeder: students table does not exist — skipped.');
            return;
        }

        $now  = now();
        $hash = Hash::make('Password123');

        $students = [
            ['2501001', '2501001@ub.edu.ph', 'Juan Dela Cruz',    'CICT'],
            ['2501002', '2501002@ub.edu.ph', 'Maria Santos',      'CBA'],
            ['2501003', '2501003@ub.edu.ph', 'Carlo Reyes',       'CITE'],
            ['2501004', '2501004@ub.edu.ph', 'Ana Lim',           'CAS'],
            ['2501005', '2501005@ub.edu.ph', 'Marco Ramos',       'CON'],
            ['2501006', '2501006@ub.edu.ph', 'Jasmine Torres',    'CICT'],
            ['2501007', '2501007@ub.edu.ph', 'Diego Villanueva',  'CBA'],
            ['2501008', '2501008@ub.edu.ph', 'Sofia Mendoza',     'CITE'],
            ['2501009', '2501009@ub.edu.ph', 'Rafael Garcia',     'CAS'],
            ['2501010', '2501010@ub.edu.ph', 'Angela Cruz',       'CON'],
        ];

        foreach ($students as [$studentId, $email, $name, $dept]) {
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

        DB::table('students')
            ->whereIn('email', array_column($students, 1))
            ->whereNull('created_at')
            ->update(['created_at' => $now]);

        $this->command->info('StudentSeeder: 10 student accounts seeded.');
    }
}
