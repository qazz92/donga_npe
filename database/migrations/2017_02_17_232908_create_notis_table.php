<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notis', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('pnotis_id')->unsigned();
            $table->dateTime('created_at');
            $table->foreign('user_id')
                ->references('id')
                ->on('normal_users')
                ->onDelete('cascade');

            $table->foreign('pnotis_id')
                ->references('id')
                ->on('pnotis')
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
        Schema::table('notis', function (Blueprint $table) {
            // 외래키 관계를 선언했다면, 리버스 마이그레이션 할때 에러를 피하기 위해
            // 테이블을 삭제하기 전에 외래키를 먼저 삭제하는 것이 중요하다.
            $table->dropForeign('notis_user_id_foreign');
            $table->dropForeign('notis_pnotis_id_foreign');
        });
        Schema::dropIfExists('notis');
    }
}
