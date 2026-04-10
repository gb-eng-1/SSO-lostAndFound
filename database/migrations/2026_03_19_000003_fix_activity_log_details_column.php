<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ActivityLog::record() passes plain strings into `details`, but the column
    // was declared as JSON. MySQL strict mode silently rejects non-JSON strings,
    // causing every activity log entry to fail. Changing to TEXT fixes this.
    public function up(): void
    {
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN details TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE activity_log MODIFY COLUMN details JSON NULL');
    }
};
