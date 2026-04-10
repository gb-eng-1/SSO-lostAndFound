<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/*
 * SCHOOL DATABASE INTEGRATION NOTE
 * ---------------------------------
 * Students in this seeder use made-up student IDs (format: 6-digit numbers where
 * the first digit is 1 or 2, e.g. 240100x) and @ub.edu.ph email addresses for
 * local testing only.
 *
 * When connecting to the official UB student database:
 *   1. Replace the $students array below with a query/import from that database.
 *   2. The `students.student_id` column must match the official student number
 *      (numeric string, no spaces). The first digit is NOT a system requirement —
 *      we just used 1 or 2 here for testing.
 *   3. The `students.email` must be the official @ub.edu.ph email — items.user_id
 *      stores this email to link lost reports to students.
 *   4. Authentication currently uses `students.password_hash` (bcrypt). On the
 *      hosted version, replace this with SSO/LDAP by updating
 *      app/Http/Controllers/Student/LoginController.php.
 *   5. If the school DB is MySQL on a separate server, add a second DB connection
 *      in config/database.php and wrap the import in a DB::connection('school')
 *      query inside this seeder or a scheduled Artisan command.
 */

/**
 * Seeds 10 test students and a comprehensive set of found items, lost reports,
 * claims, activity log entries, and notifications covering every workflow scenario.
 *
 * Run:
 *   php artisan db:seed --class=TestDataSeeder
 */
class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '256M');

        // The government-id.png test image exceeds MySQL's default 1 MB max_allowed_packet.
        // Raise it globally and reconnect so this session picks up the new limit.
        DB::unprepared('SET GLOBAL max_allowed_packet = 16777216');
        DB::reconnect();

        $now = Carbon::now();
        $hash = Hash::make('Password123');

        // ── 1. Truncate all transactional tables ──────────────────────────

        Schema::disableForeignKeyConstraints();

        foreach ([
            'notifications', 'activity_log', 'claims', 'archives',
            'matches', 'item_matches', 'items', 'students',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();

        // ── 2. Ensure admin exists ────────────────────────────────────────

        $adminId = null;
        if (Schema::hasTable('admins')) {
            $adminId = DB::table('admins')->where('email', 'admin@ub.edu.ph')->value('id');
            if (! $adminId) {
                $adminId = DB::table('admins')->insertGetId([
                    'email'         => 'admin@ub.edu.ph',
                    'password_hash' => Hash::make('Admin'),
                    'name'          => 'Administrator',
                    'role'          => 'Admin',
                    'created_at'    => $now,
                ]);
            }
        }

        // ── 3. Insert 10 students ─────────────────────────────────────────

        $students = [
            ['2401001', 'lea.robles@ub.edu.ph',    'Lea Robles',    'CICT'],
            ['2401002', 'marco.vega@ub.edu.ph',    'Marco Vega',    'CITE'],
            ['2401003', 'dina.cruz@ub.edu.ph',     'Dina Cruz',     'CBA'],
            ['2401004', 'jay.ortiz@ub.edu.ph',     'Jay Ortiz',     'CAS'],
            ['1920501', 'anna.santos@ub.edu.ph',   'Anna Santos',   'CON'],
            ['2310602', 'miguel.reyes@ub.edu.ph',  'Miguel Reyes',  'CICT'],
            ['2215703', 'grace.lim@ub.edu.ph',     'Grace Lim',     'CBA'],
            ['1830804', 'kevin.tan@ub.edu.ph',     'Kevin Tan',     'CITE'],
            ['2108905', 'sofia.gabriel@ub.edu.ph',  'Sofia Gabriel', 'CAS'],
            ['2312006', 'ben.aquino@ub.edu.ph',    'Ben Aquino',    'CON'],
        ];

        $studentDbIds = [];
        foreach ($students as [$studentNum, $email, $name, $dept]) {
            $studentDbIds[$email] = DB::table('students')->insertGetId([
                'student_id'    => $studentNum,
                'email'         => $email,
                'password_hash' => $hash,
                'name'          => $name,
                'department'    => $dept,
                'phone'         => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        // ── 4. Load test images as base64 data URLs ──────────────────────

        $imgDir = base_path('test-img');
        $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];

        $loadImg = function (string $filename) use ($imgDir, $mimeMap): ?string {
            $path = $imgDir . DIRECTORY_SEPARATOR . $filename;
            $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mime = $mimeMap[$ext] ?? 'application/octet-stream';
            return file_exists($path)
                ? 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path))
                : null;
        };

        // Pre-load small images (total < 200 KB); government-id.png (1.1 MB) is loaded lazily
        $img = [
            'dice'     => $loadImg('6-sided-dice.jpg'),
            'iphone'   => $loadImg('iphone-swirlycase.png'),
            'necklace' => $loadImg('small-silver-ring-necklace.png'),
            'keychain' => $loadImg('star-keychain.jpg'),
        ];

        // Helper: build a lost-report item_description with embedded metadata
        $lostDesc = function (string $studentNum, string $name, string $contact, string $dept, string $itemName, string $extra = ''): string {
            $d = "Student Number: {$studentNum}\nFull Name: {$name}\nContact: {$contact}\nDepartment: {$dept}\nItem: {$itemName}";
            if ($extra !== '') {
                $d .= "\n{$extra}";
            }
            return $d;
        };

        // Helper: build a found-item item_description with embedded metadata
        $foundDesc = function (string $itemName, string $encodedBy, string $extra = ''): string {
            $d = "Item: {$itemName}\nEncoded By: {$encodedBy}";
            if ($extra !== '') {
                $d .= "\n{$extra}";
            }
            return $d;
        };

        // ── 5. Seed items per scenario ────────────────────────────────────
        //
        // Each scenario block inserts found items, lost reports, item_matches,
        // claims, activity_log, and notifications as appropriate.

        $activityRows = [];
        $notifRows    = [];
        $claimRows    = [];
        $matchRows    = [];

        $activity = function (string $action, string $itemId, string $details, ?int $actorId, string $actorType, Carbon $at) use (&$activityRows) {
            $activityRows[] = [
                'action'     => $action,
                'item_id'    => $itemId,
                'details'    => $details,
                'actor_id'   => $actorId,
                'actor_type' => $actorType,
                'created_at' => $at,
            ];
        };

        $notif = function (int $recipientId, string $recipientType, string $type, string $title, string $message, ?string $relatedId, Carbon $at) use (&$notifRows) {
            $notifRows[] = [
                'recipient_id'   => $recipientId,
                'recipient_type' => $recipientType,
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'related_id'     => $relatedId,
                'is_read'        => false,
                'created_at'     => $at,
            ];
        };

        // ------------------------------------------------------------------
        // SCENARIO 1: Report filed by Lea, no match yet
        // ------------------------------------------------------------------
        $s1Date = $now->copy()->subDays(5);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000001',
            'user_id'            => 'lea.robles@ub.edu.ph',
            'item_type'          => 'Electronics & Gadgets',
            'color'              => 'Black',
            'brand'              => 'Samsung',
            'date_lost'          => $s1Date->toDateString(),
            'item_description'   => $lostDesc('2401001', 'Lea Robles', '09171234567', 'CICT', 'Samsung Galaxy Earbuds', 'Lost near CICT building lobby.'),
            'image_data'         => $img['iphone'],
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $s1Date,
            'updated_at'         => $s1Date,
        ]);
        $activity('lost_report', 'REF-0000000001', 'Student filed lost report', $studentDbIds['lea.robles@ub.edu.ph'], 'student', $s1Date);
        $notif($studentDbIds['lea.robles@ub.edu.ph'], 'student', 'report_submitted', 'Report Submitted', 'Your lost report REF-0000000001 has been submitted.', 'REF-0000000001', $s1Date);

        // ------------------------------------------------------------------
        // SCENARIO 2: Found item encoded by admin, no match
        // ------------------------------------------------------------------
        $s2Date = $now->copy()->subDays(4);
        DB::table('items')->insert([
            'id'               => 'UB10001',
            'user_id'          => null,
            'item_type'        => 'Miscellaneous',
            'color'            => 'White',
            'brand'            => null,
            'found_at'         => 'CBA Hallway',
            'found_by'         => 'Guard Ramos',
            'date_encoded'     => $s2Date->toDateString(),
            'item_description' => $foundDesc('6-sided Dice', 'Admin', 'Small white dice found on hallway floor.'),
            'storage_location' => 'Cabinet A-3',
            'image_data'       => $img['dice'],
            'status'           => 'Unclaimed Items',
            'created_at'       => $s2Date,
            'updated_at'       => $s2Date,
        ]);
        $activity('encoded', 'UB10001', 'Found item encoded', $adminId, 'admin', $s2Date);

        // ------------------------------------------------------------------
        // SCENARIO 3: Auto-matched, pending verification (Marco lost, admin found)
        // ------------------------------------------------------------------
        $s3FoundDate = $now->copy()->subDays(10);
        $s3LostDate  = $now->copy()->subDays(8);
        $s3MatchDate = $now->copy()->subDays(8);

        DB::table('items')->insert([
            'id'               => 'UB10002',
            'user_id'          => null,
            'item_type'        => 'Electronics & Gadgets',
            'color'            => 'Blue',
            'brand'            => 'Apple',
            'found_at'         => 'CITE Computer Lab',
            'found_by'         => 'Prof. Lim',
            'date_encoded'     => $s3FoundDate->toDateString(),
            'item_description' => $foundDesc('iPhone with Swirly Case', 'Admin', 'Found under desk in CITE lab.'),
            'storage_location' => 'Cabinet B-1',
            'image_data'       => $img['iphone'],
            'status'           => 'For Verification',
            'created_at'       => $s3FoundDate,
            'updated_at'       => $s3MatchDate,
        ]);
        $activity('encoded', 'UB10002', 'Found item encoded', $adminId, 'admin', $s3FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000002',
            'user_id'            => 'marco.vega@ub.edu.ph',
            'item_type'          => 'Electronics & Gadgets',
            'color'              => 'Blue',
            'brand'              => 'Apple',
            'date_lost'          => $s3LostDate->toDateString(),
            'item_description'   => $lostDesc('2401002', 'Marco Vega', '09281234567', 'CITE', 'iPhone 14 with swirly silicone case', 'Left at the computer lab.'),
            'image_data'         => $img['iphone'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10002',
            'created_at'         => $s3LostDate,
            'updated_at'         => $s3MatchDate,
        ]);
        $activity('lost_report', 'REF-0000000002', 'Student filed lost report', $studentDbIds['marco.vega@ub.edu.ph'], 'student', $s3LostDate);
        $activity('matched', 'UB10002', 'Auto-linked to REF-0000000002', null, 'system', $s3MatchDate);
        $matchRows[] = ['found_item_id' => 'UB10002', 'lost_report_id' => 'REF-0000000002', 'linked_at' => $s3MatchDate];
        $notif($studentDbIds['marco.vega@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000002. An Electronics & Gadgets (UB10002) may be your item.', 'REF-0000000002', $s3MatchDate);
        if ($adminId) {
            $notif($adminId, 'admin', 'item_matched', 'Auto-Match Found', 'Lost report REF-0000000002 was automatically matched to found item UB10002.', 'UB10002', $s3MatchDate);
        }

        // ------------------------------------------------------------------
        // SCENARIO 4: Two lost reports matched to same found item (necklace)
        //   - Dina's report auto-matched
        //   - Anna's report admin-linked manually (same found item)
        // ------------------------------------------------------------------
        $s4FoundDate  = $now->copy()->subDays(12);
        $s4Lost1Date  = $now->copy()->subDays(11);
        $s4Lost2Date  = $now->copy()->subDays(9);
        $s4Match1Date = $now->copy()->subDays(11);
        $s4Match2Date = $now->copy()->subDays(7);

        DB::table('items')->insert([
            'id'               => 'UB10003',
            'user_id'          => null,
            'item_type'        => 'Apparel & Accessories',
            'color'            => 'Silver',
            'brand'            => null,
            'found_at'         => 'CBA Ladies Room',
            'found_by'         => 'Janitor Cruz',
            'date_encoded'     => $s4FoundDate->toDateString(),
            'item_description' => $foundDesc('Small Silver Ring Necklace', 'Admin', 'Silver chain with small ring pendant.'),
            'storage_location' => 'Cabinet C-2',
            'image_data'       => $img['necklace'],
            'status'           => 'For Verification',
            'created_at'       => $s4FoundDate,
            'updated_at'       => $s4Match2Date,
        ]);
        $activity('encoded', 'UB10003', 'Found item encoded', $adminId, 'admin', $s4FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000003',
            'user_id'            => 'dina.cruz@ub.edu.ph',
            'item_type'          => 'Apparel & Accessories',
            'color'              => 'Silver',
            'brand'              => null,
            'date_lost'          => $s4Lost1Date->toDateString(),
            'item_description'   => $lostDesc('2401003', 'Dina Cruz', '09351234567', 'CBA', 'Silver necklace with ring pendant', 'Lost somewhere in CBA building.'),
            'image_data'         => $img['necklace'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10003',
            'created_at'         => $s4Lost1Date,
            'updated_at'         => $s4Match1Date,
        ]);
        $activity('lost_report', 'REF-0000000003', 'Student filed lost report', $studentDbIds['dina.cruz@ub.edu.ph'], 'student', $s4Lost1Date);
        $activity('matched', 'UB10003', 'Auto-linked to REF-0000000003', null, 'system', $s4Match1Date);
        $matchRows[] = ['found_item_id' => 'UB10003', 'lost_report_id' => 'REF-0000000003', 'linked_at' => $s4Match1Date];
        $notif($studentDbIds['dina.cruz@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000003. An Apparel & Accessories (UB10003) may be your item.', 'REF-0000000003', $s4Match1Date);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000004',
            'user_id'            => 'anna.santos@ub.edu.ph',
            'item_type'          => 'Apparel & Accessories',
            'color'              => 'Silver',
            'brand'              => null,
            'date_lost'          => $s4Lost2Date->toDateString(),
            'item_description'   => $lostDesc('1920501', 'Anna Santos', '09451234567', 'CON', 'Silver ring necklace', 'Think I lost it in the restroom area.'),
            'image_data'         => $img['necklace'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10003',
            'created_at'         => $s4Lost2Date,
            'updated_at'         => $s4Match2Date,
        ]);
        $activity('lost_report', 'REF-0000000004', 'Student filed lost report', $studentDbIds['anna.santos@ub.edu.ph'], 'student', $s4Lost2Date);
        $activity('matched', 'UB10003', 'Admin-linked to REF-0000000004', $adminId, 'admin', $s4Match2Date);
        $matchRows[] = ['found_item_id' => 'UB10003', 'lost_report_id' => 'REF-0000000004', 'linked_at' => $s4Match2Date];
        $notif($studentDbIds['anna.santos@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000004. An Apparel & Accessories (UB10003) may be your item.', 'REF-0000000004', $s4Match2Date);
        if ($adminId) {
            $notif($adminId, 'admin', 'item_matched', 'Auto-Match Found', 'Lost report REF-0000000004 was manually matched to found item UB10003.', 'UB10003', $s4Match2Date);
        }

        // ------------------------------------------------------------------
        // SCENARIO 5: Matched + student pressed Claim intent (Miguel)
        // ------------------------------------------------------------------
        $s5FoundDate = $now->copy()->subDays(15);
        $s5LostDate  = $now->copy()->subDays(14);
        $s5MatchDate = $now->copy()->subDays(14);
        $s5ClaimDate = $now->copy()->subDays(13);

        DB::table('items')->insert([
            'id'               => 'UB10004',
            'user_id'          => null,
            'item_type'        => 'Electronics & Gadgets',
            'color'            => 'Black',
            'brand'            => 'Apple',
            'found_at'         => 'CICT Room 301',
            'found_by'         => 'Guard Santos',
            'date_encoded'     => $s5FoundDate->toDateString(),
            'item_description' => $foundDesc('iPhone 13', 'Admin', 'Black phone found in classroom.'),
            'storage_location' => 'Cabinet A-1',
            'image_data'       => $img['iphone'],
            'status'           => 'For Verification',
            'created_at'       => $s5FoundDate,
            'updated_at'       => $s5MatchDate,
        ]);
        $activity('encoded', 'UB10004', 'Found item encoded', $adminId, 'admin', $s5FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000005',
            'user_id'            => 'miguel.reyes@ub.edu.ph',
            'item_type'          => 'Electronics & Gadgets',
            'color'              => 'Black',
            'brand'              => 'Apple',
            'date_lost'          => $s5LostDate->toDateString(),
            'item_description'   => $lostDesc('2310602', 'Miguel Reyes', '09561234567', 'CICT', 'iPhone 13 black', 'Left in room 301 after class.'),
            'image_data'         => $img['iphone'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10004',
            'created_at'         => $s5LostDate,
            'updated_at'         => $s5MatchDate,
        ]);
        $activity('lost_report', 'REF-0000000005', 'Student filed lost report', $studentDbIds['miguel.reyes@ub.edu.ph'], 'student', $s5LostDate);
        $activity('matched', 'UB10004', 'Auto-linked to REF-0000000005', null, 'system', $s5MatchDate);
        $matchRows[] = ['found_item_id' => 'UB10004', 'lost_report_id' => 'REF-0000000005', 'linked_at' => $s5MatchDate];
        $notif($studentDbIds['miguel.reyes@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000005. An Electronics & Gadgets (UB10004) may be your item.', 'REF-0000000005', $s5MatchDate);

        $claimRows[] = [
            'reference_id'      => 'CLM-SEED0005',
            'student_id'        => $studentDbIds['miguel.reyes@ub.edu.ph'],
            'found_item_id'     => 'UB10004',
            'lost_report_id'    => 'REF-0000000005',
            'proof_photo'       => null,
            'proof_description' => 'Student acknowledged intent to claim via matched-item workflow. Visit the security office (lost and found).',
            'status'            => 'Pending',
            'claim_date'        => $s5ClaimDate,
            'resolution_date'   => null,
            'created_at'        => $s5ClaimDate,
            'updated_at'        => $s5ClaimDate,
        ];

        // ------------------------------------------------------------------
        // SCENARIO 6: Fully claimed by admin (Jay's keychain)
        // ------------------------------------------------------------------
        $s6FoundDate = $now->copy()->subDays(25);
        $s6LostDate  = $now->copy()->subDays(23);
        $s6MatchDate = $now->copy()->subDays(23);
        $s6ClaimDate = $now->copy()->subDays(22);
        $s6ConfDate  = $now->copy()->subDays(20);

        $claimRecord6 = "\n\n--- Claim Record ---\nClaimed By: Jay Ortiz\nEmail: jay.ortiz@ub.edu.ph\nContact: 09671234567\nDate Accomplished: " . $s6ConfDate->toDateString();

        DB::table('items')->insert([
            'id'               => 'UB10005',
            'user_id'          => null,
            'item_type'        => 'Personal Belongings',
            'color'            => 'Gold',
            'brand'            => null,
            'found_at'         => 'CAS Canteen',
            'found_by'         => 'Student (turned in)',
            'date_encoded'     => $s6FoundDate->toDateString(),
            'item_description' => $foundDesc('Star-shaped Keychain', 'Admin', 'Gold star keychain with 3 keys attached.') . $claimRecord6,
            'storage_location' => 'Cabinet D-1',
            'image_data'       => $img['keychain'],
            'status'           => 'Claimed',
            'created_at'       => $s6FoundDate,
            'updated_at'       => $s6ConfDate,
        ]);
        $activity('encoded', 'UB10005', 'Found item encoded', $adminId, 'admin', $s6FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000006',
            'user_id'            => 'jay.ortiz@ub.edu.ph',
            'item_type'          => 'Personal Belongings',
            'color'              => 'Gold',
            'brand'              => null,
            'date_lost'          => $s6LostDate->toDateString(),
            'item_description'   => $lostDesc('2401004', 'Jay Ortiz', '09671234567', 'CAS', 'Gold star keychain', 'Lost near the canteen.'),
            'image_data'         => $img['keychain'],
            'status'             => 'Resolved',
            'matched_barcode_id' => 'UB10005',
            'created_at'         => $s6LostDate,
            'updated_at'         => $s6ConfDate,
        ]);
        $activity('lost_report', 'REF-0000000006', 'Student filed lost report', $studentDbIds['jay.ortiz@ub.edu.ph'], 'student', $s6LostDate);
        $activity('matched', 'UB10005', 'Auto-linked to REF-0000000006', null, 'system', $s6MatchDate);
        $activity('claimed', 'UB10005', 'Claimed by Jay Ortiz (jay.ortiz@ub.edu.ph)', $adminId, 'admin', $s6ConfDate);
        $matchRows[] = ['found_item_id' => 'UB10005', 'lost_report_id' => 'REF-0000000006', 'linked_at' => $s6MatchDate];

        $claimRows[] = [
            'reference_id'      => 'CLM-SEED0006',
            'student_id'        => $studentDbIds['jay.ortiz@ub.edu.ph'],
            'found_item_id'     => 'UB10005',
            'lost_report_id'    => 'REF-0000000006',
            'proof_photo'       => null,
            'proof_description' => 'Student acknowledged intent to claim via matched-item workflow. Visit the security office (lost and found).',
            'status'            => 'Pending',
            'claim_date'        => $s6ClaimDate,
            'resolution_date'   => null,
            'created_at'        => $s6ClaimDate,
            'updated_at'        => $s6ClaimDate,
        ];

        $notif($studentDbIds['jay.ortiz@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000006.', 'REF-0000000006', $s6MatchDate);
        $notif($studentDbIds['jay.ortiz@ub.edu.ph'], 'student', 'claim_approved', 'Item Claimed', 'Your claim for item UB10005 has been completed. Visit the office to collect.', 'UB10005', $s6ConfDate);
        if ($adminId) {
            $notif($adminId, 'admin', 'item_claimed', 'Item Claimed', 'Found item UB10005 has been claimed by Jay Ortiz.', 'UB10005', $s6ConfDate);
        }

        // ------------------------------------------------------------------
        // SCENARIO 7: External ID (ID & Nameplate) — found, matched, claimed
        // ------------------------------------------------------------------
        $s7FoundDate = $now->copy()->subDays(18);
        $s7LostDate  = $now->copy()->subDays(17);
        $s7ConfDate  = $now->copy()->subDays(15);

        $claimRecord7 = "\n\n--- Claim Record ---\nClaimed By: Grace Lim\nEmail: grace.lim@ub.edu.ph\nContact: 09781234567\nDate Accomplished: " . $s7ConfDate->toDateString();

        $govIdImg = $loadImg('government-id.png');

        DB::table('items')->insert([
            'id'               => 'UB10006',
            'user_id'          => null,
            'item_type'        => 'ID & Nameplate',
            'color'            => null,
            'brand'            => null,
            'found_at'         => 'Guard Station Main Gate',
            'found_by'         => 'Guard Reyes',
            'date_encoded'     => $s7FoundDate->toDateString(),
            'item_description' => $foundDesc('Student ID Card', 'Admin', 'UB Student ID for Grace Lim, student number 2215703.') . $claimRecord7,
            'storage_location' => 'ID Box',
            'image_data'       => $govIdImg,
            'status'           => 'Claimed',
            'created_at'       => $s7FoundDate,
            'updated_at'       => $s7ConfDate,
        ]);
        $activity('encoded', 'UB10006', 'Found item encoded', $adminId, 'admin', $s7FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000007',
            'user_id'            => 'grace.lim@ub.edu.ph',
            'item_type'          => 'Document & Identification',
            'color'              => null,
            'brand'              => null,
            'date_lost'          => $s7LostDate->toDateString(),
            'item_description'   => $lostDesc('2215703', 'Grace Lim', '09781234567', 'CBA', 'Student ID card', 'Dropped it somewhere between gate and CBA.'),
            'image_data'         => $govIdImg,
            'status'             => 'Resolved',
            'matched_barcode_id' => 'UB10006',
            'created_at'         => $s7LostDate,
            'updated_at'         => $s7ConfDate,
        ]);
        $activity('lost_report', 'REF-0000000007', 'Student filed lost report', $studentDbIds['grace.lim@ub.edu.ph'], 'student', $s7LostDate);
        $activity('claimed', 'UB10006', 'Claimed by Grace Lim (grace.lim@ub.edu.ph)', $adminId, 'admin', $s7ConfDate);
        $matchRows[] = ['found_item_id' => 'UB10006', 'lost_report_id' => 'REF-0000000007', 'linked_at' => $s7FoundDate];

        $notif($studentDbIds['grace.lim@ub.edu.ph'], 'student', 'claim_approved', 'ID Claimed', 'Your ID UB10006 has been claimed. Collect at the guard station.', 'UB10006', $s7ConfDate);
        if ($adminId) {
            $notif($adminId, 'admin', 'item_claimed', 'Item Claimed', 'Found item UB10006 (ID & Nameplate) has been claimed by Grace Lim.', 'UB10006', $s7ConfDate);
        }

        unset($govIdImg);

        // ------------------------------------------------------------------
        // SCENARIO 8: Overdue / expired retention (date_encoded 3 years ago)
        // ------------------------------------------------------------------
        $s8Date = $now->copy()->subYears(3)->subDays(10);
        DB::table('items')->insert([
            'id'               => 'UB10007',
            'user_id'          => null,
            'item_type'        => 'Apparel & Accessories',
            'color'            => 'Silver',
            'brand'            => null,
            'found_at'         => 'CBA Lost & Found',
            'found_by'         => 'Janitor Tan',
            'date_encoded'     => $s8Date->toDateString(),
            'item_description' => $foundDesc('Small Silver Ring', 'Admin', 'Silver ring, no markings. Unclaimed for years.'),
            'storage_location' => 'Old Items Bin',
            'image_data'       => $img['necklace'],
            'status'           => 'Unclaimed Items',
            'created_at'       => $s8Date,
            'updated_at'       => $s8Date,
        ]);
        $activity('encoded', 'UB10007', 'Found item encoded', $adminId, 'admin', $s8Date);

        // ------------------------------------------------------------------
        // SCENARIO 9: Report cancelled by student (Kevin)
        // ------------------------------------------------------------------
        $s9FoundDate  = $now->copy()->subDays(7);
        $s9LostDate   = $now->copy()->subDays(6);
        $s9MatchDate  = $now->copy()->subDays(6);
        $s9CancelDate = $now->copy()->subDays(3);

        DB::table('items')->insert([
            'id'               => 'UB10008',
            'user_id'          => null,
            'item_type'        => 'Miscellaneous',
            'color'            => 'Red',
            'brand'            => null,
            'found_at'         => 'CITE Parking Lot',
            'found_by'         => 'Guard Perez',
            'date_encoded'     => $s9FoundDate->toDateString(),
            'item_description' => $foundDesc('Red Dice Set', 'Admin', 'Set of dice in red pouch.'),
            'storage_location' => 'Cabinet A-4',
            'image_data'       => $img['dice'],
            'status'           => 'Unclaimed Items',
            'created_at'       => $s9FoundDate,
            'updated_at'       => $s9CancelDate,
        ]);
        $activity('encoded', 'UB10008', 'Found item encoded', $adminId, 'admin', $s9FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000008',
            'user_id'            => 'kevin.tan@ub.edu.ph',
            'item_type'          => 'Miscellaneous',
            'color'              => 'Red',
            'brand'              => null,
            'date_lost'          => $s9LostDate->toDateString(),
            'item_description'   => $lostDesc('1830804', 'Kevin Tan', '09891234567', 'CITE', 'Red dice pouch', 'Left it in the parking lot.'),
            'image_data'         => $img['dice'],
            'status'             => 'Cancelled',
            'matched_barcode_id' => null,
            'created_at'         => $s9LostDate,
            'updated_at'         => $s9CancelDate,
        ]);
        $activity('lost_report', 'REF-0000000008', 'Student filed lost report', $studentDbIds['kevin.tan@ub.edu.ph'], 'student', $s9LostDate);
        $activity('cancelled', 'REF-0000000008', 'Student cancelled report', $studentDbIds['kevin.tan@ub.edu.ph'], 'student', $s9CancelDate);

        // ------------------------------------------------------------------
        // SCENARIO 10: Disposed found item
        // ------------------------------------------------------------------
        $s10Date = $now->copy()->subYears(2)->subMonths(3);
        DB::table('items')->insert([
            'id'               => 'UB10009',
            'user_id'          => null,
            'item_type'        => 'Personal Belongings',
            'color'            => 'Gold',
            'brand'            => null,
            'found_at'         => 'CAS Hallway',
            'found_by'         => 'Student (turned in)',
            'date_encoded'     => $s10Date->toDateString(),
            'item_description' => $foundDesc('Star Keychain', 'Admin', 'Gold star keychain, heavily worn. Disposed after retention.'),
            'storage_location' => 'Disposed',
            'image_data'       => $img['keychain'],
            'status'           => 'Disposed',
            'created_at'       => $s10Date,
            'updated_at'       => $now->copy()->subDays(30),
        ]);
        $activity('encoded', 'UB10009', 'Found item encoded', $adminId, 'admin', $s10Date);
        $activity('disposed', 'UB10009', 'Item disposed after retention period', $adminId, 'admin', $now->copy()->subDays(30));

        // ------------------------------------------------------------------
        // SCENARIO 11: Unresolved Claimants — full claim with photo (Sofia)
        // ------------------------------------------------------------------
        $s11FoundDate = $now->copy()->subDays(9);
        $s11LostDate  = $now->copy()->subDays(8);
        $s11MatchDate = $now->copy()->subDays(8);
        $s11ClaimDate = $now->copy()->subDays(6);

        DB::table('items')->insert([
            'id'               => 'UB10010',
            'user_id'          => null,
            'item_type'        => 'Electronics & Gadgets',
            'color'            => 'Pink',
            'brand'            => 'Apple',
            'found_at'         => 'CAS Library',
            'found_by'         => 'Librarian Cruz',
            'date_encoded'     => $s11FoundDate->toDateString(),
            'item_description' => $foundDesc('iPhone with Pink Case', 'Admin', 'Pink phone found behind bookshelf in library.'),
            'storage_location' => 'Cabinet B-2',
            'image_data'       => $img['iphone'],
            'status'           => 'Unresolved Claimants',
            'created_at'       => $s11FoundDate,
            'updated_at'       => $s11ClaimDate,
        ]);
        $activity('encoded', 'UB10010', 'Found item encoded', $adminId, 'admin', $s11FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000009',
            'user_id'            => 'sofia.gabriel@ub.edu.ph',
            'item_type'          => 'Electronics & Gadgets',
            'color'              => 'Pink',
            'brand'              => 'Apple',
            'date_lost'          => $s11LostDate->toDateString(),
            'item_description'   => $lostDesc('2108905', 'Sofia Gabriel', '09901234567', 'CAS', 'iPhone with pink case', 'Left at the library.'),
            'image_data'         => $img['iphone'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10010',
            'created_at'         => $s11LostDate,
            'updated_at'         => $s11MatchDate,
        ]);
        $activity('lost_report', 'REF-0000000009', 'Student filed lost report', $studentDbIds['sofia.gabriel@ub.edu.ph'], 'student', $s11LostDate);
        $activity('matched', 'UB10010', 'Auto-linked to REF-0000000009', null, 'system', $s11MatchDate);
        $matchRows[] = ['found_item_id' => 'UB10010', 'lost_report_id' => 'REF-0000000009', 'linked_at' => $s11MatchDate];

        $claimRows[] = [
            'reference_id'      => 'CLM-SEED0011',
            'student_id'        => $studentDbIds['sofia.gabriel@ub.edu.ph'],
            'found_item_id'     => 'UB10010',
            'lost_report_id'    => 'REF-0000000009',
            'proof_photo'       => $img['iphone'],
            'proof_description' => 'This is my iPhone with the pink case. You can see the same scratch on the top corner.',
            'status'            => 'Pending',
            'claim_date'        => $s11ClaimDate,
            'resolution_date'   => null,
            'created_at'        => $s11ClaimDate,
            'updated_at'        => $s11ClaimDate,
        ];

        $notif($studentDbIds['sofia.gabriel@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000009. An Electronics & Gadgets (UB10010) may be your item.', 'REF-0000000009', $s11MatchDate);
        if ($adminId) {
            $notif($adminId, 'admin', 'claim_submitted', 'New Claim Submitted', 'Student submitted claim CLM-SEED0011 for item UB10010.', 'CLM-SEED0011', $s11ClaimDate);
        }

        // ------------------------------------------------------------------
        // SCENARIO 12: Claim rejected by admin (Ben)
        // ------------------------------------------------------------------
        $s12FoundDate  = $now->copy()->subDays(20);
        $s12LostDate   = $now->copy()->subDays(19);
        $s12MatchDate  = $now->copy()->subDays(19);
        $s12ClaimDate  = $now->copy()->subDays(18);
        $s12RejectDate = $now->copy()->subDays(16);

        DB::table('items')->insert([
            'id'               => 'UB10011',
            'user_id'          => null,
            'item_type'        => 'Apparel & Accessories',
            'color'            => 'Silver',
            'brand'            => 'Pandora',
            'found_at'         => 'CON Building Hallway',
            'found_by'         => 'Guard Mendoza',
            'date_encoded'     => $s12FoundDate->toDateString(),
            'item_description' => $foundDesc('Silver Bracelet', 'Admin', 'Silver chain bracelet with small charms.'),
            'storage_location' => 'Cabinet C-3',
            'image_data'       => $img['necklace'],
            'status'           => 'Unclaimed Items',
            'created_at'       => $s12FoundDate,
            'updated_at'       => $s12RejectDate,
        ]);
        $activity('encoded', 'UB10011', 'Found item encoded', $adminId, 'admin', $s12FoundDate);

        DB::table('items')->insert([
            'id'                 => 'REF-0000000010',
            'user_id'            => 'ben.aquino@ub.edu.ph',
            'item_type'          => 'Apparel & Accessories',
            'color'              => 'Silver',
            'brand'              => 'Pandora',
            'date_lost'          => $s12LostDate->toDateString(),
            'item_description'   => $lostDesc('2312006', 'Ben Aquino', '09111234567', 'CON', 'Silver Pandora bracelet', 'Lost near the hallway in CON building.'),
            'image_data'         => $img['necklace'],
            'status'             => 'For Verification',
            'matched_barcode_id' => 'UB10011',
            'created_at'         => $s12LostDate,
            'updated_at'         => $s12MatchDate,
        ]);
        $activity('lost_report', 'REF-0000000010', 'Student filed lost report', $studentDbIds['ben.aquino@ub.edu.ph'], 'student', $s12LostDate);
        $activity('matched', 'UB10011', 'Auto-linked to REF-0000000010', null, 'system', $s12MatchDate);
        $matchRows[] = ['found_item_id' => 'UB10011', 'lost_report_id' => 'REF-0000000010', 'linked_at' => $s12MatchDate];

        $claimRows[] = [
            'reference_id'      => 'CLM-SEED0012',
            'student_id'        => $studentDbIds['ben.aquino@ub.edu.ph'],
            'found_item_id'     => 'UB10011',
            'lost_report_id'    => 'REF-0000000010',
            'proof_photo'       => $img['necklace'],
            'proof_description' => 'I believe this is my bracelet. The charms match.',
            'status'            => 'Rejected',
            'claim_date'        => $s12ClaimDate,
            'resolution_date'   => $s12RejectDate,
            'created_at'        => $s12ClaimDate,
            'updated_at'        => $s12RejectDate,
        ];

        $notif($studentDbIds['ben.aquino@ub.edu.ph'], 'student', 'item_matched', 'Potential Match Found!', 'A potential match has been found for your lost report REF-0000000010.', 'REF-0000000010', $s12MatchDate);
        $notif($studentDbIds['ben.aquino@ub.edu.ph'], 'student', 'claim_rejected', 'Claim Rejected', 'Your claim CLM-SEED0012 for item UB10011 was rejected. The item did not match your description.', 'REF-0000000010', $s12RejectDate);
        if ($adminId) {
            $notif($adminId, 'admin', 'claim_submitted', 'New Claim Submitted', 'Student submitted claim CLM-SEED0012 for item UB10011.', 'CLM-SEED0012', $s12ClaimDate);
        }

        // ------------------------------------------------------------------
        // EXTRA REPORTS — more variety across remaining students
        // ------------------------------------------------------------------

        // Lea: second report, also unmatched
        $exDate1 = $now->copy()->subDays(2);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000011',
            'user_id'            => 'lea.robles@ub.edu.ph',
            'item_type'          => 'Personal Belongings',
            'color'              => 'Brown',
            'brand'              => null,
            'date_lost'          => $exDate1->toDateString(),
            'item_description'   => $lostDesc('2401001', 'Lea Robles', '09171234567', 'CICT', 'Brown leather wallet', 'Lost near CICT canteen area.'),
            'image_data'         => $img['keychain'],
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $exDate1,
            'updated_at'         => $exDate1,
        ]);
        $activity('lost_report', 'REF-0000000011', 'Student filed lost report', $studentDbIds['lea.robles@ub.edu.ph'], 'student', $exDate1);

        // Kevin: second report, unmatched
        $exDate2 = $now->copy()->subDays(1);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000012',
            'user_id'            => 'kevin.tan@ub.edu.ph',
            'item_type'          => 'Electronics & Gadgets',
            'color'              => 'White',
            'brand'              => 'Samsung',
            'date_lost'          => $exDate2->toDateString(),
            'item_description'   => $lostDesc('1830804', 'Kevin Tan', '09891234567', 'CITE', 'Samsung earbuds', 'Lost somewhere in CITE building.'),
            'image_data'         => $img['iphone'],
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $exDate2,
            'updated_at'         => $exDate2,
        ]);
        $activity('lost_report', 'REF-0000000012', 'Student filed lost report', $studentDbIds['kevin.tan@ub.edu.ph'], 'student', $exDate2);

        // Anna: second report, unmatched (Document & Identification)
        $exDate3 = $now->copy()->subDays(3);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000013',
            'user_id'            => 'anna.santos@ub.edu.ph',
            'item_type'          => 'Document & Identification',
            'color'              => null,
            'brand'              => null,
            'date_lost'          => $exDate3->toDateString(),
            'item_description'   => $lostDesc('1920501', 'Anna Santos', '09451234567', 'CON', 'National ID', 'ID Type: National ID\nMight have left it in CON office.'),
            'image_data'         => $loadImg('government-id.png'),
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $exDate3,
            'updated_at'         => $exDate3,
        ]);
        $activity('lost_report', 'REF-0000000013', 'Student filed lost report', $studentDbIds['anna.santos@ub.edu.ph'], 'student', $exDate3);

        // Extra found item — unmatched (for variety)
        $exDate4 = $now->copy()->subDays(6);
        DB::table('items')->insert([
            'id'               => 'UB10012',
            'user_id'          => null,
            'item_type'        => 'Electronics & Gadgets',
            'color'            => 'White',
            'brand'            => 'Samsung',
            'found_at'         => 'CITE Lobby',
            'found_by'         => 'Guard Torres',
            'date_encoded'     => $exDate4->toDateString(),
            'item_description' => $foundDesc('Samsung Galaxy Buds', 'Admin', 'White earbuds case found on lobby bench.'),
            'storage_location' => 'Cabinet B-3',
            'image_data'       => $img['dice'],
            'status'           => 'Unclaimed Items',
            'created_at'       => $exDate4,
            'updated_at'       => $exDate4,
        ]);
        $activity('encoded', 'UB10012', 'Found item encoded', $adminId, 'admin', $exDate4);

        // Ben: second report, also unmatched
        $exDate5 = $now->copy()->subDays(4);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000014',
            'user_id'            => 'ben.aquino@ub.edu.ph',
            'item_type'          => 'Personal Belongings',
            'color'              => 'Blue',
            'brand'              => null,
            'date_lost'          => $exDate5->toDateString(),
            'item_description'   => $lostDesc('2312006', 'Ben Aquino', '09111234567', 'CON', 'Blue water bottle', 'Left at the CON canteen.'),
            'image_data'         => $img['dice'],
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $exDate5,
            'updated_at'         => $exDate5,
        ]);
        $activity('lost_report', 'REF-0000000014', 'Student filed lost report', $studentDbIds['ben.aquino@ub.edu.ph'], 'student', $exDate5);

        // Sofia: second report, unmatched
        $exDate6 = $now->copy()->subDays(2);
        DB::table('items')->insert([
            'id'                 => 'REF-0000000015',
            'user_id'            => 'sofia.gabriel@ub.edu.ph',
            'item_type'          => 'Apparel & Accessories',
            'color'              => 'Black',
            'brand'              => null,
            'date_lost'          => $exDate6->toDateString(),
            'item_description'   => $lostDesc('2108905', 'Sofia Gabriel', '09901234567', 'CAS', 'Black umbrella', 'Left the umbrella in CAS classroom.'),
            'image_data'         => $img['keychain'],
            'status'             => 'Unclaimed Items',
            'matched_barcode_id' => null,
            'created_at'         => $exDate6,
            'updated_at'         => $exDate6,
        ]);
        $activity('lost_report', 'REF-0000000015', 'Student filed lost report', $studentDbIds['sofia.gabriel@ub.edu.ph'], 'student', $exDate6);

        // ── 6. Bulk insert item_matches ───────────────────────────────────

        if (Schema::hasTable('item_matches') && count($matchRows) > 0) {
            DB::table('item_matches')->insert($matchRows);
        }

        // ── 7. Bulk insert claims ─────────────────────────────────────────

        if (count($claimRows) > 0) {
            DB::table('claims')->insert($claimRows);
        }

        // ── 8. Bulk insert activity log ───────────────────────────────────

        if (count($activityRows) > 0) {
            DB::table('activity_log')->insert($activityRows);
        }

        // ── 9. Bulk insert notifications ──────────────────────────────────

        if (count($notifRows) > 0) {
            DB::table('notifications')->insert($notifRows);
        }

        $this->command->info('TestDataSeeder: 10 students, 12 found items, 15 lost reports, '
            . count($claimRows) . ' claims, '
            . count($matchRows) . ' item_matches, '
            . count($activityRows) . ' activity_log entries, '
            . count($notifRows) . ' notifications seeded.');
    }
}
