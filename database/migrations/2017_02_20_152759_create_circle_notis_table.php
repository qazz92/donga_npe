<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCircleNotisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('circle_notis', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('pcircle_notis_id')->unsigned();
            $table->integer('check_att')->unsigned()->default(0);
            $table->dateTime('created_at');
            $table->foreign('user_id')
                ->references('id')
                ->on('normal_users')
                ->onDelete('cascade');

            $table->foreign('pcircle_notis_id')
                ->references('id')
                ->on('pcircle_notis')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('circle_notis');
    }
}
