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
        Schema::create('process_guides', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('section', 100)->index('idx_section')->comment('report_lost, search_found, claim_item');
            $table->integer('step_number');
            $table->text('instruction');
            $table->integer('estimated_time_minutes')->nullable();
            $table->json('faq')->nullable()->comment('Array of {question, answer}');
            $table->json('troubleshooting')->nullable()->comment('Array of {issue, solution}');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_guides');
    }
};
