<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Resets the database to a minimal clean demo state.
 *
 * Truncates: items, item_matches, claims, activity_log
 * Preserves: students, admins, notifications, help_pages
 *
 * Inserts:
 *   4 found items  — 2 internal (UBBC) + 2 guest ID (UBBC)
 *   4 lost reports — 2 student-submitted (REF) + 2 admin-encoded (REF)
 *   1 item_match   — UBBC-2526-0002 ↔ REF-0000000001
 *   4 activity_log — one per lost report (actor_type student|admin)
 *   1 claim        — pending, UBBC-2526-0002 by demo student
 *
 * Usage: php artisan db:seed --class=FreshDemoSeeder
 */
class FreshDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['items', 'item_matches', 'claims', 'activity_log', 'students'] as $tbl) {
            if (!Schema::hasTable($tbl)) {
                $this->command->error("FreshDemoSeeder: '{$tbl}' table missing — aborting.");
                return;
            }
        }

        // Resolve demo student (created by StudentSeeder)
        $demoStudent = DB::table('students')->where('email', '2501001@ub.edu.ph')->first();
        if (!$demoStudent) {
            $this->command->warn('FreshDemoSeeder: demo student 2501001@ub.edu.ph not found. Run StudentSeeder first for claim record.');
        }

        $now = now()->toDateTimeString();
        $sy  = '2526'; // SY 2025-2026
        $retentionDays = 90;

        // ── Truncate ─────────────────────────────────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('claims')->truncate();
        DB::table('item_matches')->truncate();
        DB::table('activity_log')->truncate();
        DB::table('items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('FreshDemoSeeder: tables truncated.');

        // ── Found Items ───────────────────────────────────────────────────────────
        $dateFound1 = Carbon::now()->subDays(10)->toDateString();
        $dateFound2 = Carbon::now()->subDays(20)->toDateString();

        $foundItems = [
            // Internal — Unclaimed (Electronics)
            [
                'id'               => "UBBC-{$sy}-0001",
                'user_id'          => null,
                'item_type'        => 'Electronics & Gadgets',
                'color'            => 'Black',
                'brand'            => 'Generic',
                'found_at'         => 'Library Building 2F',
                'found_by'         => 'library.staff@ub.edu.ph',
                'date_encoded'     => $dateFound1,
                'date_lost'        => null,
                'item_description' => "Item: Laptop Charger\nFound By: library.staff@ub.edu.ph\nEncoded By: admin@ub.edu.ph",
                'storage_location' => 'Cabinet A, Shelf 1',
                'image_data'       => null,
                'status'           => 'Unclaimed Items',
                'disposal_deadline'=> Carbon::parse($dateFound1)->addDays($retentionDays)->toDateString(),
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // Internal — For Verification (matched to REF-0000000001)
            [
                'id'               => "UBBC-{$sy}-0002",
                'user_id'          => null,
                'item_type'        => 'Personal Belongings',
                'color'            => 'Brown',
                'brand'            => null,
                'found_at'         => 'Main Canteen',
                'found_by'         => 'canteen.staff@ub.edu.ph',
                'date_encoded'     => $dateFound2,
                'date_lost'        => null,
                'item_description' => "Item: Wallet\nFound By: canteen.staff@ub.edu.ph\nEncoded By: admin@ub.edu.ph",
                'storage_location' => 'Drawer 1',
                'image_data'       => null,
                'status'           => 'For Verification',
                'disposal_deadline'=> Carbon::parse($dateFound2)->addDays($retentionDays)->toDateString(),
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // Guest — For Verification
            [
                'id'               => "UBBC-{$sy}-0003",
                'user_id'          => null,
                'item_type'        => 'ID & Nameplate',
                'color'            => 'White',
                'brand'            => null,
                'found_at'         => 'CICT Building Lobby',
                'found_by'         => 'cict.staff@ub.edu.ph',
                'date_encoded'     => $dateFound1,
                'date_lost'        => null,
                'item_description' => "ID Type: School ID\nFull Name: Sample Guest\nFound By: cict.staff@ub.edu.ph\nEncoded By: admin@ub.edu.ph",
                'storage_location' => 'Cabinet B, Shelf 1',
                'image_data'       => null,
                'status'           => 'For Verification',
                'disposal_deadline'=> Carbon::parse($dateFound1)->addDays($retentionDays)->toDateString(),
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // Guest — Unclaimed ID (Unclaimed IDs subcategory)
            [
                'id'               => "UBBC-{$sy}-0004",
                'user_id'          => null,
                'item_type'        => 'Unclaimed IDs',
                'color'            => 'Blue',
                'brand'            => null,
                'found_at'         => null,
                'found_by'         => null,
                'date_encoded'     => $dateFound2,
                'date_lost'        => null,
                'item_description' => "Owner: Another Guest\nContact: 09171234567\nID Type: Driver's License",
                'storage_location' => null,
                'image_data'       => null,
                'status'           => 'Unclaimed Items',
                'disposal_deadline'=> Carbon::parse($dateFound2)->addDays($retentionDays)->toDateString(),
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('items')->insert($foundItems);
        $this->command->info('FreshDemoSeeder: 4 found items inserted.');

        // ── Lost Reports ──────────────────────────────────────────────────────────
        $dateLost1 = Carbon::now()->subDays(22)->toDateString();
        $dateLost2 = Carbon::now()->subDays(15)->toDateString();

        $demoEmail = $demoStudent->email ?? '2501001@ub.edu.ph';

        $lostReports = [
            // REF-0000000001 — student, For Verification, matched to UBBC-2526-0002
            [
                'id'               => 'REF-0000000001',
                'user_id'          => $demoEmail,
                'item_type'        => 'Personal Belongings',
                'color'            => 'Brown',
                'brand'            => null,
                'found_at'         => null,
                'found_by'         => null,
                'date_encoded'     => null,
                'date_lost'        => $dateLost1,
                'item_description' => "Full Name: Juan Dela Cruz\nStudent Number: 2501001\nContact: 09123456789\nDepartment: CICT\nItem: Wallet\nColor noted: Brown",
                'storage_location' => null,
                'image_data'       => null,
                'status'           => 'For Verification',
                'disposal_deadline'=> null,
                'matched_barcode_id' => "UBBC-{$sy}-0002",
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // REF-0000000002 — student, Unclaimed Items
            [
                'id'               => 'REF-0000000002',
                'user_id'          => $demoEmail,
                'item_type'        => 'Electronics & Gadgets',
                'color'            => 'White',
                'brand'            => 'Apple',
                'found_at'         => null,
                'found_by'         => null,
                'date_encoded'     => null,
                'date_lost'        => $dateLost2,
                'item_description' => "Full Name: Juan Dela Cruz\nStudent Number: 2501001\nContact: 09123456789\nDepartment: CICT\nItem: Earphones\nColor noted: White\nBrand noted: Apple",
                'storage_location' => null,
                'image_data'       => null,
                'status'           => 'Unclaimed Items',
                'disposal_deadline'=> null,
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // REF-0000000003 — admin walk-in encode, Unclaimed Items
            [
                'id'               => 'REF-0000000003',
                'user_id'          => null,
                'item_type'        => 'Apparel & Accessories',
                'color'            => 'Blue',
                'brand'            => null,
                'found_at'         => null,
                'found_by'         => null,
                'date_encoded'     => null,
                'date_lost'        => Carbon::now()->subDays(12)->toDateString(),
                'item_description' => "Full Name: Walk-in Student A\nStudent Number: N/A\nContact: 09111222333\nDepartment: CAS\nItem: Jacket\nColor noted: Blue",
                'storage_location' => null,
                'image_data'       => null,
                'status'           => 'Unclaimed Items',
                'disposal_deadline'=> null,
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // REF-0000000004 — admin walk-in encode, Unclaimed Items
            [
                'id'               => 'REF-0000000004',
                'user_id'          => null,
                'item_type'        => 'Miscellaneous',
                'color'            => 'Black',
                'brand'            => null,
                'found_at'         => null,
                'found_by'         => null,
                'date_encoded'     => null,
                'date_lost'        => Carbon::now()->subDays(8)->toDateString(),
                'item_description' => "Full Name: Walk-in Student B\nStudent Number: N/A\nContact: 09444555666\nDepartment: CON\nItem: Umbrella\nColor noted: Black",
                'storage_location' => null,
                'image_data'       => null,
                'status'           => 'Unclaimed Items',
                'disposal_deadline'=> null,
                'matched_barcode_id' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('items')->insert($lostReports);
        $this->command->info('FreshDemoSeeder: 4 lost reports inserted.');

        // ── Item Match ────────────────────────────────────────────────────────────
        DB::table('item_matches')->insert([
            'found_item_id'  => "UBBC-{$sy}-0002",
            'lost_report_id' => 'REF-0000000001',
            'linked_at'      => $now,
        ]);
        $this->command->info('FreshDemoSeeder: 1 item_match inserted.');

        // ── Activity Log ──────────────────────────────────────────────────────────
        $activityEntries = [
            // Student-submitted
            [
                'item_id'    => 'REF-0000000001',
                'action'     => 'lost_report',
                'actor_id'   => $demoStudent->id ?? null,
                'actor_type' => 'student',
                'details'    => json_encode(['source' => 'demo']),
                'created_at' => $now,
            ],
            [
                'item_id'    => 'REF-0000000002',
                'action'     => 'lost_report',
                'actor_id'   => $demoStudent->id ?? null,
                'actor_type' => 'student',
                'details'    => json_encode(['source' => 'demo']),
                'created_at' => $now,
            ],
            // Admin-encoded walk-in
            [
                'item_id'    => 'REF-0000000003',
                'action'     => 'lost_report',
                'actor_id'   => null,
                'actor_type' => 'admin',
                'details'    => json_encode(['source' => 'demo']),
                'created_at' => $now,
            ],
            [
                'item_id'    => 'REF-0000000004',
                'action'     => 'lost_report',
                'actor_id'   => null,
                'actor_type' => 'admin',
                'details'    => json_encode(['source' => 'demo']),
                'created_at' => $now,
            ],
        ];

        DB::table('activity_log')->insert($activityEntries);
        $this->command->info('FreshDemoSeeder: 4 activity_log entries inserted.');

        // ── Claim ─────────────────────────────────────────────────────────────────
        if ($demoStudent) {
            DB::table('claims')->insert([
                'reference_id'      => 'CLM-DEMO-0001',
                'student_id'        => $demoStudent->id,
                'found_item_id'     => "UBBC-{$sy}-0002",
                'lost_report_id'    => 'REF-0000000001',
                'proof_photo'       => null,
                'proof_description' => 'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).',
                'status'            => 'Pending',
                'claim_date'        => $now,
                'resolution_date'   => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $this->command->info('FreshDemoSeeder: 1 claim inserted.');
        } else {
            $this->command->warn('FreshDemoSeeder: skipped claim (no demo student).');
        }

        $this->command->info('FreshDemoSeeder: complete — 4 found items, 4 lost reports, 1 match, 1 claim.');
    }
}
