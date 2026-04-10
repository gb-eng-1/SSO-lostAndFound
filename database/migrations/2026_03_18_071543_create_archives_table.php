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
        Schema::create('archives', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_id', 50)->unique('reference_id');
            $table->string('found_item_id', 50);
            $table->unsignedInteger('student_id')->index('idx_student_id');
            $table->string('claimant_name', 100)->index('idx_claimant_name');
            $table->string('claimant_email');
            $table->string('claimant_phone', 20)->nullable();
            $table->json('item_details')->comment('Snapshot of found item');
            $table->longText('proof_photo')->nullable();
            $table->dateTime('claim_date');
            $table->dateTime('resolution_date')->index('idx_resolution_date');
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['reference_id'], 'unique_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
