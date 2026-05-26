<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds all test scenarios:
 *   - 35 found items across all 5 internal categories + guest IDs, all statuses
 *   - 14 lost reports covering all categories and all statuses
 *   - 13 claim records (pending intent + resolved)
 *   - item_matches links
 *   - activity_log entries
 *
 * Requires StudentSeeder to have run first (students 2501001–2501010).
 * Safe to re-run: uses updateOrInsert throughout.
 */
class ScenarioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['students', 'items', 'claims', 'item_matches', 'activity_log'] as $tbl) {
            if (!Schema::hasTable($tbl)) {
                $this->command->warn("ScenarioSeeder: '{$tbl}' table missing — aborting.");
                return;
            }
        }

        $emails = array_map(
            fn($n) => '2501' . str_pad($n, 3, '0', STR_PAD_LEFT) . '@ub.edu.ph',
            range(1, 10)
        );

        $studentIds = DB::table('students')
            ->whereIn('email', $emails)
            ->pluck('id', 'email');

        if ($studentIds->count() < 10) {
            $this->command->warn('ScenarioSeeder: Run StudentSeeder first. Found only ' . $studentIds->count() . ' students.');
            return;
        }

        $now = now()->toDateTimeString();
        $d   = fn(int $ago) => Carbon::now()->subDays($ago)->toDateString();

        $this->seedFoundItems($now, $d);
        $this->seedLostReports($now, $d);
        $this->seedItemMatches($now);
        $this->seedClaims($studentIds, $now, $d);
        $this->seedActivityLog($studentIds, $now, $d);
        $this->seedDemoMatchesForJuan($now, $d);

        $this->command->info('ScenarioSeeder: complete — 38 found items, 17 lost reports, 13 claims.');
    }

    // ── Found Items ───────────────────────────────────────────────────────────

    private function seedFoundItems(string $now, callable $d): void
    {
        $items = [

            /* ── Electronics & Gadgets ──────────────────────────── */
            ['UB30001', 'Electronics & Gadgets', 'Laptop Charger',       'Black',  'Dell',      'Library Building 2F',            'library.staff@ub.edu.ph',      $d(14),  'Cabinet A, Shelf 2',  'Unclaimed Items'],
            ['UB30002', 'Electronics & Gadgets', 'Earphones',            'White',  'Apple',     'Main Canteen',                   'canteen.staff@ub.edu.ph',      $d(30),  'Drawer 1',            'For Verification'],
            ['UB30003', 'Electronics & Gadgets', 'Headphones',           'Black',  'Sony',      'Gymnasium',                      'pe.instructor@ub.edu.ph',      $d(20),  'Cabinet B, Shelf 1',  'Unresolved Claimants'],
            ['UB30004', 'Electronics & Gadgets', 'USB Flash Drive',      'Silver', 'Kingston',  'CICT Computer Lab 2',            'lab.monitor@ub.edu.ph',        $d(120), 'Drawer 2',            'Claimed'],
            ['UB30005', 'Electronics & Gadgets', 'Scientific Calculator','Gray',   'Casio',     'CAS Building Room 203',          '2501011@ub.edu.ph',            $d(90),  'Drawer 3',            'Claimed'],
            ['UB30006', 'Electronics & Gadgets', 'Power Bank',           'White',  'Anker',     'Student Cafeteria',              'cafeteria.staff@ub.edu.ph',    $d(7),   'Cabinet A, Shelf 1',  'Unclaimed Items'],

            /* ── Books & School Supplies ────────────────────────── */
            ['UB30007', 'Books & School Supplies', 'Notebook',         'Blue',   null,        'CITE Hall Lobby',                'cite.staff@ub.edu.ph',         $d(10),  'Cabinet C, Shelf 1',  'Unclaimed Items'],
            ['UB30008', 'Books & School Supplies', 'Folder',           'Red',    null,        'CBA Building Corridor',          'cba.staff@ub.edu.ph',          $d(25),  'Drawer 4',            'For Verification'],
            ['UB30009', 'Books & School Supplies', 'Library Book',     'Brown',  null,        'Library Building 1F',            'library.staff@ub.edu.ph',      $d(5),   'Cabinet C, Shelf 2',  'Unclaimed Items'],
            ['UB30010', 'Books & School Supplies', 'Personal Planner', 'Black',  null,        'Administration Building Lobby',  'admin.staff@ub.edu.ph',        $d(150), 'Drawer 5',            'Claimed'],
            ['UB30011', 'Books & School Supplies', 'Exam Papers',      'White',  null,        'Faculty Room 2F',                'faculty@ub.edu.ph',            $d(60),  null,                  'Cancelled'],

            /* ── Personal Belongings ────────────────────────────── */
            ['UB30012', 'Personal Belongings', 'Wallet',                 'Brown',  null,        'Main Canteen',                   'canteen.staff@ub.edu.ph',      $d(28),  'Drawer 1',            'For Verification'],
            ['UB30013', 'Personal Belongings', 'Backpack',               'Black',  'JanSport',  'CICT Building Ground Floor',     'security.guard@ub.edu.ph',     $d(100), 'Cabinet D, Shelf 1',  'Claimed'],
            ['UB30014', 'Personal Belongings', 'Pencil Case',            'Blue',   null,        'CAS Building Room 105',          'cas.staff@ub.edu.ph',          $d(15),  'Drawer 2',            'For Verification'],
            ['UB30015', 'Personal Belongings', 'Water Bottle',           'Green',  'Hydro Flask','Gymnasium Bleachers',           'pe.instructor@ub.edu.ph',      $d(3),   'Cabinet A, Shelf 3',  'Unclaimed Items'],
            ['UB30016', 'Personal Belongings', 'Eyeglasses',             'Black',  null,        'Library Building 2F Reading Area','library.staff@ub.edu.ph',     $d(18),  'Cabinet B, Shelf 2',  'Unclaimed Items'],
            ['UB30017', 'Personal Belongings', 'Coin Purse',             'Pink',   null,        'Main Canteen',                   'canteen.staff@ub.edu.ph',      $d(45),  null,                  'Cancelled'],

            /* ── Apparel & Accessories ──────────────────────────── */
            ['UB30018', 'Apparel & Accessories', 'Jacket',               'Blue',   null,        'CON Building Nursing Lab',       'con.staff@ub.edu.ph',          $d(22),  'Drawer 3',            'For Verification'],
            ['UB30019', 'Apparel & Accessories', 'Cap',                  'Red',    null,        'Gymnasium Court',                'pe.instructor@ub.edu.ph',      $d(80),  'Cabinet D, Shelf 2',  'Claimed'],
            ['UB30020', 'Apparel & Accessories', 'Hoodie',               'Gray',   null,        'CICT Building Hallway',          'security.guard@ub.edu.ph',     $d(8),   'Cabinet A, Shelf 4',  'Unclaimed Items'],
            ['UB30021', 'Apparel & Accessories', 'Scarf',                'Black',  null,        'Library Building 1F',            'library.staff@ub.edu.ph',      $d(12),  'Cabinet B, Shelf 3',  'Unclaimed Items'],
            ['UB30022', 'Apparel & Accessories', 'Wristwatch',           'Black',  'Casio',     'Administration Building',        'admin.staff@ub.edu.ph',        $d(110), 'Drawer 5',            'Claimed'],
            ['UB30023', 'Apparel & Accessories', 'Belt',                 'Brown',  null,        'CBA Building Hallway',           'cba.staff@ub.edu.ph',          $d(6),   'Cabinet C, Shelf 3',  'Unclaimed Items'],

            /* ── Miscellaneous ──────────────────────────────────── */
            ['UB30024', 'Miscellaneous', 'Umbrella',                     'Black',  null,        'Main Gate Guard House',          'security.guard@ub.edu.ph',     $d(35),  'Drawer 1',            'For Verification'],
            ['UB30025', 'Miscellaneous', 'Keys',                         null,     null,        'Parking Area',                   'security.guard@ub.edu.ph',     $d(9),   'Cabinet A, Shelf 5',  'Unclaimed Items'],
            ['UB30026', 'Miscellaneous', 'ID Lanyard',                   'Red',    'UB',        'Student Center',                 'student.affairs@ub.edu.ph',    $d(4),   'Cabinet B, Shelf 4',  'Unclaimed Items'],
            ['UB30027', 'Miscellaneous', 'Lunchbox',                     'Blue',   null,        'Main Canteen Tables',            'canteen.staff@ub.edu.ph',      $d(11),  'Cabinet A, Shelf 6',  'Unclaimed Items'],
            ['UB30028', 'Miscellaneous', 'Glasses Case',                 'Black',  null,        'Library Building 1F',            'library.staff@ub.edu.ph',      $d(1100),'Cabinet E, Shelf 1',  'Unclaimed Items'], // EXPIRED (>2yrs)
            ['UB30029', 'Miscellaneous', 'Textbook',                     null,     null,        'CICT Building Room 104',         'lab.monitor@ub.edu.ph',        $d(1095),'Cabinet F, Shelf 1',  'Unclaimed Items'], // EXPIRED (>2yrs)
            ['UB30030', 'Miscellaneous', 'Toy Keychain',                 null,     null,        'Gymnasium Bleachers',            'pe.instructor@ub.edu.ph',      $d(30),  null,                  'Cancelled'],

            /* ── ID & Nameplate (External / Guest) ──────────────── */
            ['UB30031', 'ID & Nameplate', 'Student ID',                  null,     null,        'Guard House Main Gate',          'security.guard@ub.edu.ph',     $d(40),  'Drawer G, Slot 1',    'For Verification'],
            ['UB30032', 'ID & Nameplate', "Driver's License",            null,     null,        'Main Canteen',                   'canteen.staff@ub.edu.ph',      $d(130), 'Drawer G, Slot 2',    'Claimed'],
            ['UB30033', 'ID & Nameplate', "Voter's ID",                  null,     null,        'Main Gate Guard House',          'security.guard@ub.edu.ph',     $d(16),  'Drawer G, Slot 3',    'Unclaimed Items'],
            ['UB30034', 'ID & Nameplate', 'Company/Employee ID',         null,     null,        'Guard House Main Gate',          'security.guard@ub.edu.ph',     $d(50),  'Drawer G, Slot 4',    'For Verification'],
            ['UB30035', 'ID & Nameplate', 'National ID',                 null,     null,        'Student Center',                 'student.affairs@ub.edu.ph',    $d(6),   'Drawer G, Slot 5',    'Unclaimed Items'],
        ];

        // Claim records appended to description for claimed items
        $claimRecords = [
            'UB30004' => "\n\n--- Claim Record ---\nClaimed By: Angela Cruz\nEmail: 2501010@ub.edu.ph\nContact: 09876543210\nDate Accomplished: " . $d(95),
            'UB30005' => "\n\n--- Claim Record ---\nClaimed By: Rafael Garcia\nEmail: 2501009@ub.edu.ph\nContact: 09765432109\nDate Accomplished: " . $d(65),
            'UB30010' => "\n\n--- Claim Record ---\nClaimed By: Jasmine Torres\nEmail: 2501006@ub.edu.ph\nContact: 09234567890\nDate Accomplished: " . $d(125),
            'UB30013' => "\n\n--- Claim Record ---\nClaimed By: Diego Villanueva\nEmail: 2501007@ub.edu.ph\nContact: 09345678901\nDate Accomplished: " . $d(75),
            'UB30019' => "\n\n--- Claim Record ---\nClaimed By: Sofia Mendoza\nEmail: 2501008@ub.edu.ph\nContact: 09654321098\nDate Accomplished: " . $d(55),
            'UB30022' => "\n\n--- Claim Record ---\nClaimed By: Angela Cruz\nEmail: 2501010@ub.edu.ph\nContact: 09876543210\nDate Accomplished: " . $d(85),
            'UB30032' => "\n\n--- Claim Record ---\nClaimed By: Roberto Magno\nEmail: \nContact: 09112233445\nDate Accomplished: " . $d(110),
        ];

        foreach ($items as [$id, $type, $itemName, $color, $brand, $foundAt, $foundBy, $dateEnc, $storage, $status]) {
            $desc = "Item: {$itemName}\nFound By: {$foundBy}\nEncoded By: SSO Staff";
            if (isset($claimRecords[$id])) {
                $desc .= $claimRecords[$id];
            }

            DB::table('items')->updateOrInsert(['id' => $id], [
                'user_id'            => null,
                'item_type'          => $type,
                'color'              => $color,
                'brand'              => $brand,
                'found_at'           => $foundAt,
                'found_by'           => $foundBy,
                'date_encoded'       => $dateEnc,
                'date_lost'          => null,
                'item_description'   => $desc,
                'storage_location'   => $storage,
                'image_data'         => null,
                'status'             => $status,
                'disposal_deadline'  => null,
                'matched_barcode_id' => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('ScenarioSeeder: 35 found items seeded.');
    }

    // ── Lost Reports ──────────────────────────────────────────────────────────

    private function seedLostReports(string $now, callable $d): void
    {
        // [id, user_email, item_type, item_name, color, brand, date_lost, status, matched_barcode_id, contact, dept, student_num, full_name]
        $reports = [
            // Active For Verification matches
            ['REF-0000030001', '2501001@ub.edu.ph', 'Electronics & Gadgets',    'Earphones',   'White',  'Apple',     $d(35),  'For Verification',      'UB30002', '09123456789', 'CICT', '2501001', 'Juan Dela Cruz'],
            ['REF-0000030002', '2501002@ub.edu.ph', 'Books & School Supplies',  'Folder',      'Red',    null,        $d(30),  'For Verification',      'UB30008', '09234567890', 'CBA',  '2501002', 'Maria Santos'],
            ['REF-0000030003', '2501003@ub.edu.ph', 'Personal Belongings',      'Wallet',      'Brown',  null,        $d(32),  'For Verification',      'UB30012', '09345678901', 'CITE', '2501003', 'Carlo Reyes'],
            ['REF-0000030004', '2501004@ub.edu.ph', 'Apparel & Accessories',    'Jacket',      'Blue',   null,        $d(26),  'For Verification',      'UB30018', '09456789012', 'CAS',  '2501004', 'Ana Lim'],
            // Marco — 2 matched reports (Unresolved Claimants + For Verification)
            ['REF-0000030005', '2501005@ub.edu.ph', 'Electronics & Gadgets',    'Headphones',  'Black',  'Sony',      $d(25),  'Unresolved Claimants',  'UB30003', '09567890123', 'CON',  '2501005', 'Marco Ramos'],
            ['REF-0000030006', '2501005@ub.edu.ph', 'Personal Belongings',      'Pencil Case', 'Blue',   null,        $d(18),  'For Verification',      'UB30014', '09567890123', 'CON',  '2501005', 'Marco Ramos'],
            // Jasmine — 1 matched + 1 cancelled
            ['REF-0000030007', '2501006@ub.edu.ph', 'Miscellaneous',            'Umbrella',    'Black',  null,        $d(40),  'For Verification',      'UB30024', '09678901234', 'CICT', '2501006', 'Jasmine Torres'],
            ['REF-0000030008', '2501006@ub.edu.ph', 'Electronics & Gadgets',    'Earphones',   'White',  null,        $d(50),  'Cancelled',             null,      '09678901234', 'CICT', '2501006', 'Jasmine Torres'],
            // Resolved reports (fully claimed)
            ['REF-0000030009', '2501010@ub.edu.ph', 'Electronics & Gadgets',    'USB Flash Drive','Silver','Kingston', $d(130), 'Resolved',             'UB30004', '09876543210', 'CON',  '2501010', 'Angela Cruz'],
            ['REF-0000030010', '2501009@ub.edu.ph', 'Electronics & Gadgets',    'Calculator',  'Gray',   'Casio',     $d(100), 'Resolved',              'UB30005', '09765432109', 'CAS',  '2501009', 'Rafael Garcia'],
            ['REF-0000030011', '2501006@ub.edu.ph', 'Books & School Supplies',  'Planner',     'Black',  null,        $d(160), 'Resolved',              'UB30010', '09678901234', 'CICT', '2501006', 'Jasmine Torres'],
            ['REF-0000030012', '2501007@ub.edu.ph', 'Personal Belongings',      'Backpack',    'Black',  'JanSport',  $d(110), 'Resolved',              'UB30013', '09345678901', 'CBA',  '2501007', 'Diego Villanueva'],
            ['REF-0000030013', '2501008@ub.edu.ph', 'Apparel & Accessories',    'Cap',         'Red',    null,        $d(90),  'Resolved',              'UB30019', '09654321098', 'CITE', '2501008', 'Sofia Mendoza'],
            // Angela's second resolved report
            ['REF-0000030014', '2501010@ub.edu.ph', 'Apparel & Accessories',    'Wristwatch',  'Black',  'Casio',     $d(120), 'Resolved',              'UB30022', '09876543210', 'CON',  '2501010', 'Angela Cruz'],
        ];

        foreach ($reports as [$id, $email, $type, $item, $color, $brand, $dateLost, $status, $matched, $contact, $dept, $stuNum, $fullName]) {
            $desc = "Full Name: {$fullName}\nStudent Number: {$stuNum}\nContact: {$contact}\nDepartment: {$dept}\nItem: {$item}";
            if ($color) $desc .= "\nColor noted: {$color}";
            if ($brand)  $desc .= "\nBrand noted: {$brand}";

            DB::table('items')->updateOrInsert(['id' => $id], [
                'user_id'            => $email,
                'item_type'          => $type,
                'color'              => $color,
                'brand'              => $brand,
                'found_at'           => null,
                'found_by'           => null,
                'date_encoded'       => null,
                'date_lost'          => $dateLost,
                'item_description'   => $desc,
                'storage_location'   => null,
                'image_data'         => null,
                'status'             => $status,
                'disposal_deadline'  => null,
                'matched_barcode_id' => $matched,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('ScenarioSeeder: 14 lost reports seeded.');
    }

    // ── Item Matches ──────────────────────────────────────────────────────────

    private function seedItemMatches(string $now): void
    {
        $pairs = [
            ['UB30002', 'REF-0000030001'],
            ['UB30003', 'REF-0000030005'],
            ['UB30004', 'REF-0000030009'],
            ['UB30005', 'REF-0000030010'],
            ['UB30008', 'REF-0000030002'],
            ['UB30010', 'REF-0000030011'],
            ['UB30012', 'REF-0000030003'],
            ['UB30013', 'REF-0000030012'],
            ['UB30014', 'REF-0000030006'],
            ['UB30018', 'REF-0000030004'],
            ['UB30019', 'REF-0000030013'],
            ['UB30022', 'REF-0000030014'],
            ['UB30024', 'REF-0000030007'],
        ];

        foreach ($pairs as [$found, $report]) {
            $exists = DB::table('item_matches')
                ->where('found_item_id', $found)
                ->where('lost_report_id', $report)
                ->exists();

            if (!$exists) {
                DB::table('item_matches')->insert([
                    'found_item_id'  => $found,
                    'lost_report_id' => $report,
                    'linked_at'      => $now,
                ]);
            }
        }

        $this->command->info('ScenarioSeeder: 13 item_matches seeded.');
    }

    // ── Claims ────────────────────────────────────────────────────────────────

    private function seedClaims(\Illuminate\Support\Collection $studentIds, string $now, callable $d): void
    {
        $s = fn(string $email) => $studentIds[$email];

        // [reference_id, email, found_item_id, report_id, status, resolution_date, description]
        $claims = [
            // Pending claim intents (For Claiming tab)
            ['CLM-SEED-0001', '2501001@ub.edu.ph', 'UB30002', 'REF-0000030001', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            ['CLM-SEED-0002', '2501002@ub.edu.ph', 'UB30008', 'REF-0000030002', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            ['CLM-SEED-0003', '2501003@ub.edu.ph', 'UB30012', 'REF-0000030003', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            ['CLM-SEED-0004', '2501004@ub.edu.ph', 'UB30018', 'REF-0000030004', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            // Marco — 2 claims (Unresolved Claimants + pending For Verification)
            ['CLM-SEED-0005', '2501005@ub.edu.ph', 'UB30003', 'REF-0000030005', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            ['CLM-SEED-0006', '2501005@ub.edu.ph', 'UB30014', 'REF-0000030006', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            // Jasmine — 1 pending
            ['CLM-SEED-0007', '2501006@ub.edu.ph', 'UB30024', 'REF-0000030007', 'Pending', null,     'Student acknowledged intent to claim via matched-item workflow. Visit the SSO office (lost and found).'],
            // Resolved claims (Claimed tab)
            ['CLM-SEED-0008', '2501010@ub.edu.ph', 'UB30004', 'REF-0000030009', 'Resolved', $d(95), 'USB Flash Drive claimed in person. ID verified by SSO staff.'],
            ['CLM-SEED-0009', '2501010@ub.edu.ph', 'UB30022', 'REF-0000030014', 'Resolved', $d(85), 'Casio wristwatch claimed in person. ID verified by SSO staff.'],
            ['CLM-SEED-0010', '2501009@ub.edu.ph', 'UB30005', 'REF-0000030010', 'Resolved', $d(65), 'Calculator claimed in person. ID verified by SSO staff.'],
            ['CLM-SEED-0011', '2501006@ub.edu.ph', 'UB30010', 'REF-0000030011', 'Resolved', $d(125),'Personal planner claimed in person. ID verified by SSO staff.'],
            ['CLM-SEED-0012', '2501007@ub.edu.ph', 'UB30013', 'REF-0000030012', 'Resolved', $d(75), 'Backpack claimed in person. ID verified by SSO staff.'],
            ['CLM-SEED-0013', '2501008@ub.edu.ph', 'UB30019', 'REF-0000030013', 'Resolved', $d(55), 'Red cap claimed in person. ID verified by SSO staff.'],
        ];

        foreach ($claims as [$refId, $email, $foundId, $reportId, $status, $resDate, $desc]) {
            $stuId = $s($email);

            DB::table('claims')->updateOrInsert(['reference_id' => $refId], [
                'student_id'        => $stuId,
                'found_item_id'     => $foundId,
                'lost_report_id'    => $reportId,
                'proof_photo'       => null,
                'proof_description' => $desc,
                'status'            => $status,
                'claim_date'        => $now,
                'resolution_date'   => $resDate,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('ScenarioSeeder: 13 claims seeded.');
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    private function seedActivityLog(\Illuminate\Support\Collection $studentIds, string $now, callable $d): void
    {
        $s = fn(string $email) => $studentIds[$email];

        $entries = [];

        // Student-submitted lost reports (required for requiresStudentClaimIntentBeforeAdminClaim())
        $studentReports = [
            ['REF-0000030001', '2501001@ub.edu.ph'],
            ['REF-0000030002', '2501002@ub.edu.ph'],
            ['REF-0000030003', '2501003@ub.edu.ph'],
            ['REF-0000030004', '2501004@ub.edu.ph'],
            ['REF-0000030005', '2501005@ub.edu.ph'],
            ['REF-0000030006', '2501005@ub.edu.ph'],
            ['REF-0000030007', '2501006@ub.edu.ph'],
            ['REF-0000030008', '2501006@ub.edu.ph'],
            ['REF-0000030009', '2501010@ub.edu.ph'],
            ['REF-0000030010', '2501009@ub.edu.ph'],
            ['REF-0000030011', '2501006@ub.edu.ph'],
            ['REF-0000030012', '2501007@ub.edu.ph'],
            ['REF-0000030013', '2501008@ub.edu.ph'],
            ['REF-0000030014', '2501010@ub.edu.ph'],
        ];

        foreach ($studentReports as [$reportId, $email]) {
            $entries[] = [
                'item_id'    => $reportId,
                'action'     => 'lost_report',
                'actor_id'   => $s($email),
                'actor_type' => 'student',
                'details'    => json_encode(['source' => 'seed']),
                'created_at' => $now,
            ];
        }

        // Claimed items (populates "resolved this month" counter in MatchedItemController)
        $claimedItems = ['UB30004', 'UB30005', 'UB30010', 'UB30013', 'UB30019', 'UB30022', 'UB30032'];
        foreach ($claimedItems as $itemId) {
            $entries[] = [
                'item_id'    => $itemId,
                'action'     => 'claimed',
                'actor_id'   => null,
                'actor_type' => 'admin',
                'details'    => json_encode(['source' => 'seed']),
                'created_at' => $now,
            ];
        }

        // Matched links
        $matchedPairs = [
            ['UB30002', 'REF-0000030001'], ['UB30003', 'REF-0000030005'],
            ['UB30004', 'REF-0000030009'], ['UB30005', 'REF-0000030010'],
            ['UB30008', 'REF-0000030002'], ['UB30010', 'REF-0000030011'],
            ['UB30012', 'REF-0000030003'], ['UB30013', 'REF-0000030012'],
            ['UB30014', 'REF-0000030006'], ['UB30018', 'REF-0000030004'],
            ['UB30019', 'REF-0000030013'], ['UB30022', 'REF-0000030014'],
            ['UB30024', 'REF-0000030007'],
        ];

        foreach ($matchedPairs as [$found, $report]) {
            $entries[] = [
                'item_id'    => $found,
                'action'     => 'matched',
                'actor_id'   => null,
                'actor_type' => 'system',
                'details'    => json_encode(['linked_report' => $report, 'source' => 'seed']),
                'created_at' => $now,
            ];
        }

        // Only insert if no seed entries already exist (idempotency check)
        $alreadySeeded = DB::table('activity_log')
            ->whereIn('item_id', ['UB30001', 'REF-0000030001'])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(details, '$.source')) = 'seed'")
            ->exists();

        if (!$alreadySeeded) {
            DB::table('activity_log')->insert($entries);
            $this->command->info('ScenarioSeeder: ' . count($entries) . ' activity_log entries seeded.');
        } else {
            $this->command->info('ScenarioSeeder: activity_log already seeded — skipped.');
        }
    }

    // ── Demo matches for Juan Dela Cruz (2501001) — no claims yet ─────────────

    private function seedDemoMatchesForJuan(string $now, callable $d): void
    {
        // Three found items already matched (status For Verification)
        $foundItems = [
            ['UB30036', 'Personal Belongings',   'Backpack',  'Black', 'JanSport', 'CICT Building Ground Floor',   $d(12), 'Cabinet D, Shelf 3'],
            ['UB30037', 'Apparel & Accessories',  'Hoodie',    'Gray',  null,       'Library Building 2F',          $d(6),  'Cabinet A, Shelf 7'],
            ['UB30038', 'Miscellaneous',          'Keys',      null,    null,       'Main Canteen Tables',           $d(9),  'Cabinet A, Shelf 8'],
        ];

        foreach ($foundItems as [$id, $type, $item, $color, $brand, $foundAt, $dateEnc, $storage]) {
            DB::table('items')->updateOrInsert(['id' => $id], [
                'user_id'            => null,
                'item_type'          => $type,
                'color'              => $color,
                'brand'              => $brand,
                'found_at'           => $foundAt,
                'found_by'           => 'security.guard@ub.edu.ph',
                'date_encoded'       => $dateEnc,
                'date_lost'          => null,
                'item_description'   => "Item: {$item}\nFound By: security.guard@ub.edu.ph\nEncoded By: SSO Staff",
                'storage_location'   => $storage,
                'image_data'         => null,
                'status'             => 'For Verification',
                'disposal_deadline'  => null,
                'matched_barcode_id' => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // Three lost reports for Juan — no claim records attached
        $reports = [
            ['REF-0000030015', 'Personal Belongings',  'Backpack', 'Black', 'JanSport', $d(17), 'UB30036'],
            ['REF-0000030016', 'Apparel & Accessories', 'Hoodie',  'Gray',  null,        $d(10), 'UB30037'],
            ['REF-0000030017', 'Miscellaneous',         'Keys',    null,    null,         $d(14), 'UB30038'],
        ];

        foreach ($reports as [$id, $type, $item, $color, $brand, $dateLost, $matched]) {
            $desc = "Full Name: Juan Dela Cruz\nStudent Number: 2501001\nContact: 09123456789\nDepartment: CICT\nItem: {$item}";
            if ($color) $desc .= "\nColor noted: {$color}";
            if ($brand)  $desc .= "\nBrand noted: {$brand}";

            DB::table('items')->updateOrInsert(['id' => $id], [
                'user_id'            => '2501001@ub.edu.ph',
                'item_type'          => $type,
                'color'              => $color,
                'brand'              => $brand,
                'found_at'           => null,
                'found_by'           => null,
                'date_encoded'       => null,
                'date_lost'          => $dateLost,
                'item_description'   => $desc,
                'storage_location'   => null,
                'image_data'         => null,
                'status'             => 'For Verification',
                'disposal_deadline'  => null,
                'matched_barcode_id' => $matched,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // item_matches links
        $pairs = [
            ['UB30036', 'REF-0000030015'],
            ['UB30037', 'REF-0000030016'],
            ['UB30038', 'REF-0000030017'],
        ];

        foreach ($pairs as [$found, $report]) {
            $exists = DB::table('item_matches')
                ->where('found_item_id', $found)
                ->where('lost_report_id', $report)
                ->exists();

            if (!$exists) {
                DB::table('item_matches')->insert([
                    'found_item_id'  => $found,
                    'lost_report_id' => $report,
                    'linked_at'      => $now,
                ]);
            }
        }

        // Activity log — lost_report + matched entries
        $alreadySeeded = DB::table('activity_log')
            ->where('item_id', 'REF-0000030015')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(details, '$.source')) = 'seed'")
            ->exists();

        if (!$alreadySeeded) {
            $juanId = DB::table('students')->where('email', '2501001@ub.edu.ph')->value('id');

            $logEntries = [];
            foreach (['REF-0000030015', 'REF-0000030016', 'REF-0000030017'] as $rId) {
                $logEntries[] = [
                    'item_id'    => $rId,
                    'action'     => 'lost_report',
                    'actor_id'   => $juanId,
                    'actor_type' => 'student',
                    'details'    => json_encode(['source' => 'seed']),
                    'created_at' => $now,
                ];
            }
            foreach ($pairs as [$found, $report]) {
                $logEntries[] = [
                    'item_id'    => $found,
                    'action'     => 'matched',
                    'actor_id'   => null,
                    'actor_type' => 'system',
                    'details'    => json_encode(['linked_report' => $report, 'source' => 'seed']),
                    'created_at' => $now,
                ];
            }

            DB::table('activity_log')->insert($logEntries);
        }

        $this->command->info('ScenarioSeeder: 3 demo matched pairs for Juan Dela Cruz (no claims) seeded.');
    }
}
