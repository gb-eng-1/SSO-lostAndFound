<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Creates the item_matches junction table used by DashboardController::linkTicket().
    // The existing 'matches' table is the scoring/recommendation table;
    // 'item_matches' is the confirmed link between a found item and a lost report.
    public function up(): void
    {
        Schema::create('item_matches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('found_item_id', 50);
            $table->string('lost_report_id', 50);
            $table->dateTime('linked_at')->useCurrent();

            $table->unique(['found_item_id', 'lost_report_id']);
            $table->index('found_item_id');
            $table->index('lost_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_matches');
    }
};
