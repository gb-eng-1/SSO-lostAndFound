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
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('recipient_id')->comment('Admin or Student ID');
            $table->enum('recipient_type', ['admin', 'student']);
            $table->string('type', 50)->comment('match_found, claim_approved, etc.');
            $table->string('title');
            $table->text('message');
            $table->string('related_id', 50)->nullable()->comment('Match ID, Claim ID, etc.');
            $table->boolean('is_read')->nullable()->default(false)->index('idx_is_read');
            $table->dateTime('created_at')->useCurrent()->index('idx_created_at');

            $table->index(['recipient_id', 'recipient_type'], 'idx_recipient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
