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
        Schema::create('activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item_id', 50)->nullable()->index('idx_item_id');
            $table->string('action', 50)->index('idx_action')->comment('e.g. encoded, matched, claimed');
            $table->unsignedInteger('actor_id')->nullable();
            $table->enum('actor_type', ['admin', 'student', 'system'])->nullable()->default('system');
            $table->json('details')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
