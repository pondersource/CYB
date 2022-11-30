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
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('interval');
            $table->string('function');
            $table->string('parameters');
            $table->timestamps();
        });

        Schema::table('authfunctions', function (Blueprint $table) {
            $table->bigInteger('recurring_task_id')->unsigned()->nullable();

            $table->foreign('recurring_task_id')->references('id')->on('recurring_tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authfunctions', function (Blueprint $table) {
            $table->dropColumn('recurring_task_id');
        });

        Schema::dropIfExists('recurring_tasks');
    }
};
