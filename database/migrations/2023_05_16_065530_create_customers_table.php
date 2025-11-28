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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->uuid('customer_uid');
            $table->string('password');
            $table->string('company_name');
            $table->integer('primary_customer_market');
            $table->boolean('sell_online')->default(0);
            $table->string('website')->nullable();
            $table->string('seller_permit_number');
            $table->string('ein_path')->nullable();
            $table->string('birth_date')->nullable();
            $table->boolean('active')->default(1);
            $table->text('reset_token')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->float('points')->default(0);
            $table->float('points_spent')->default(0);
            $table->boolean('verified')->default(1);
            $table->boolean('block')->default(0);
            $table->string('attention')->nullable();
            $table->boolean('receive_offers')->default(0);
            $table->boolean('mailing_list')->default(0);
            $table->string('fb_user_id')->nullable();
            $table->string('fb_name')->nullable();
            $table->string('fb_image')->nullable();
            $table->unsignedInteger('git status')->nullable();
            $table->boolean('text_block')->default(0);
            $table->rememberToken();
            $table->string('fcm_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
