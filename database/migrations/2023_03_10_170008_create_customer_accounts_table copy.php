<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->string('account_name')->unique();
            $table->string('short_name')->nullable();
            $table->boolean('no_credit')->default(false);
            $table->boolean('no_debit')->default(false);
            $table->boolean('is_dormant')->default(false);
            $table->boolean('is_norm')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_accounts');
    }
};
