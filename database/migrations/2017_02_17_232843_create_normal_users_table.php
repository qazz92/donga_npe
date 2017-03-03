<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNormalUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('normal_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stuId')->unique();
            $table->string('name');
            $table->string('coll');
            $table->string('major');
//            $table->integer('circle_id')->unsigned()->default(1);
            $table->integer('push_permit')->default(0);
            $table->timestamps();

//            $table->foreign('circle_id')
//                ->references('id')
//                ->on('circles')
//                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('normal_users', function (Blueprint $table) {
            // 외래키 관계를 선언했다면, 리버스 마이그레이션 할때 에러를 피하기 위해
            // 테이블을 삭제하기 전에 외래키를 먼저 삭제하는 것이 중요하다.
            $table->dropForeign('normal_users_circle_id_foreign');
        });
        Schema::dropIfExists('normal_users');
    }
}
