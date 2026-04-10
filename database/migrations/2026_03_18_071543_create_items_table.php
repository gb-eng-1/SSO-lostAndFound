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
        Schema::create('items', function (Blueprint $table) {
            $table->string('id', 50)->primary()->comment('Barcode/Reference ID');
            $table->string('user_id', 100)->nullable()->index('idx_user_id')->comment('User who reported (e.g. email)');
            $table->string('item_type', 100)->nullable()->index('idx_item_type')->comment('Category: Electronics & Gadgets | Document & Identification | Personal Belongings | Apparel & Accessories | Miscellaneous | ID & Nameplate');
            $table->string('color', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('found_at', 200)->nullable()->comment('Location where item was found');
            $table->string('found_by', 200)->nullable()->comment('Person who found (e.g. email)');
            $table->date('date_encoded')->nullable()->index('idx_date_encoded')->comment('Date found/encoded');
            $table->date('date_lost')->nullable()->comment('Date lost (if reported as lost)');
            $table->text('item_description')->nullable();
            $table->string('storage_location', 200)->nullable();
            $table->longText('image_data')->nullable()->comment('Base64 data URL or path');
            $table->enum('status', ['Unclaimed Items', 'Unresolved Claimants', 'For Verification', 'Matched', 'Claimed'])->default('Unclaimed Items')->index('idx_status');
            $table->date('disposal_deadline')->nullable()->index('idx_disposal_deadline')->comment('Date after which unclaimed item may be disposed');
            $table->string('matched_barcode_id', 50)->nullable()->index('idx_matched_barcode_id')->comment('Reference to matched found item');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();

            $table->index(['date_encoded', 'status'], 'idx_retention');
            $table->index(['status', 'disposal_deadline'], 'idx_status_deadline');
            $table->index(['status', 'item_type'], 'idx_status_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
