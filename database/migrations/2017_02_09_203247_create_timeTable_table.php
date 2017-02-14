<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timeTables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('day')->unsigned();
            $table->integer('time')->unsigned();
            $table->string('subject_code');
            $table->string('subject_name');
            $table->integer('room_id')->unsigned()->nullable();
            $table->foreign('room_id')
                ->references('id')
                ->on('rooms')
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
        Schema::table('timeTables', function (Blueprint $table) {
            // 외래키 관계를 선언했다면, 리버스 마이그레이션 할때 에러를 피하기 위해
            // 테이블을 삭제하기 전에 외래키를 먼저 삭제하는 것이 중요하다.
            $table->dropForeign('timetables_room_id_foreign');
        });
        Schema::dropIfExists('timeTables');
    }
}
