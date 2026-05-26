<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('items')
            ->where('item_type', 'Document & Identification')
            ->update(['item_type' => 'Unclaimed IDs']);
    }

    public function down(): void
    {
        DB::table('items')
            ->where('item_type', 'Unclaimed IDs')
            ->update(['item_type' => 'Document & Identification']);
    }
};
