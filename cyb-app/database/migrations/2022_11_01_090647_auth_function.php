<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AuthFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authfunctions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('auth_id')->unsigned();
            $table->string('data_type');
            $table->boolean('read');
            $table->boolean('write');
            $table->timestamps();

            $table->foreign('auth_id')->references('id')->on('authentications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authfunctions');
    }
}
