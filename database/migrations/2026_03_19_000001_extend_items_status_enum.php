<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add Resolved, Cancelled, Disposed to the items.status enum.
        // These statuses are used throughout controllers but were missing from
        // the original migration, causing DB errors on cancel/claim/dispose actions.
        DB::statement("
            ALTER TABLE items
            MODIFY COLUMN status ENUM(
                'Unclaimed Items',
                'Unresolved Claimants',
                'For Verification',
                'Matched',
                'Claimed',
                'Resolved',
                'Cancelled',
                'Disposed'
            ) NOT NULL DEFAULT 'Unclaimed Items'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE items
            MODIFY COLUMN status ENUM(
                'Unclaimed Items',
                'Unresolved Claimants',
                'For Verification',
                'Matched',
                'Claimed'
            ) NOT NULL DEFAULT 'Unclaimed Items'
        ");
    }
};
