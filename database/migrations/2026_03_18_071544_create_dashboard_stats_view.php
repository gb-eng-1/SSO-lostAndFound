<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW `dashboard_stats` AS select (select count(0) from `lostandfound_db`.`items` where `lostandfound_db`.`items`.`status` = 'Found') AS `found_count`,(select count(0) from `lostandfound_db`.`items` where `lostandfound_db`.`items`.`status` = 'Lost') AS `lost_count`,(select count(0) from `lostandfound_db`.`claims` where `lostandfound_db`.`claims`.`status` = 'Resolved') AS `resolved_count`,current_timestamp() AS `last_updated`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `dashboard_stats`");
    }
};
