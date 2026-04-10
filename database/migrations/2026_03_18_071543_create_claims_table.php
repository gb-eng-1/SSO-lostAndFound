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
        Schema::create('claims', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_id', 50)->unique('reference_id')->comment('Unique claim identifier');
            $table->unsignedInteger('student_id')->index('idx_student_id');
            $table->string('found_item_id', 50)->index('idx_found_item_id');
            $table->string('lost_report_id', 50)->nullable();
            $table->longText('proof_photo')->nullable()->comment('Photo path or base64 data URL');
            $table->text('proof_description')->nullable()->comment('Student description of item');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Resolved'])->default('Pending')->index('idx_status');
            $table->dateTime('claim_date')->useCurrent();
            $table->dateTime('resolution_date')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['reference_id'], 'unique_reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
