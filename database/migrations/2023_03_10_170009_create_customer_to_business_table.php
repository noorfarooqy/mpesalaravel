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
        Schema::create('mp_customer_to_business', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_account')->nullable()->references('id')->on('mp_customer_accounts');
            $table->uuid('trn_party_trn_id')->unique();
            $table->string('trn_type')->default('PayBill');
            $table->string('trn_id')->unique();
            $table->string('trn_fcc_ref')->nullable();
            $table->unsignedBigInteger('trn_time');
            $table->unsignedFloat('trn_amount');
            $table->string('trn_bill_ref');
            $table->string('trn_invoice_number')->nullable();
            $table->unsignedFloat('trn_org_balance')->nullable();
            $table->string('trn_msisdn');
            $table->string('trn_kyc_fn')->nullable();
            $table->string('trn_kyc_mn')->nullable();
            $table->string('trn_kyc_ln')->nullable();
            $table->string('val_error')->nullable();
            $table->string('val_error_code')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamp('validated_at')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('post_confirmation_status')->default(false);
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
        Schema::dropIfExists('mp_customer_to_business');
    }
};
