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
        Schema::create('easy_sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('used_token')->references('id')->on('easy_notifications');
            $table->string('to');
            $table->string('content');
            $table->text('dlr_response')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->string('message_id')->nullable();
            $table->foreignId('user')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('easy_sms_notifications');
    }
};
