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
        Schema::create('lp_identities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            // Having auth_id means update notifier is on
            $table->bigInteger('auth_id')->unsigned()->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country', 2);
            $table->string('zip')->nullable();
            $table->integer('kyc_status');
            $table->string('identifier_scheme')->nullable();
            $table->string('identifier_value')->nullable();
            $table->string('registrar')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('auth_id')->references('id')->on('authentications')->onDelete('cascade');
        });

        Schema::create('lp_messages', function (Blueprint $table) {
            $table->id();
            $table->bigIntegessssr('user_id')->unsigned();
            $table->bigInteger('identity_id')->unsigned();
            $table->string('registrar');
            $table->string('reference');
            $table->integer('type');
            $table->integer('direction');
            $table->string('file_name')->nullable();
            $table->integer('receive_time');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('identity_id')->references('id')->on('lp_identities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lp_identities');
        Schema::dropIfExists('lp_messages');
    }
};
