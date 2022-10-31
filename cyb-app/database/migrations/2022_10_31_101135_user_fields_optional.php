<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserFieldsOptional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->unsigned()->nullable()->change();
            $table->string('email')->unsigned()->nullable()->change();
            $table->string('password')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->unsigned()->nullable(false)->change();
            $table->string('email')->unsigned()->nullable(false)->change();
            $table->string('password')->unsigned()->nullable(false)->change();
        });
    }
}
