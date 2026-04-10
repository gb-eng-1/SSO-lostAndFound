<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lost_report_id', 50)->index('idx_lost_report_id')->comment('Reference ID of lost report');
            $table->string('found_item_id', 50)->index('idx_found_item_id')->comment('Barcode ID of found item');
            $table->decimal('confidence_score', 5)->nullable()->default(0)->comment('Match confidence 0-100');
            $table->json('matching_criteria')->nullable()->comment('Which fields matched');
            $table->enum('status', ['Pending_Review', 'Approved', 'Rejected'])->default('Pending_Review')->index('idx_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['lost_report_id', 'found_item_id'], 'unique_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
