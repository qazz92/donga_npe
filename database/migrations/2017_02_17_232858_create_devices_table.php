<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('device_id');
            $table->string('os_enum');
            $table->string('model');
            $table->string('operator');
            $table->string('api_level');
            $table->string('push_service_enum')->default('fcm');
            $table->string('push_service_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('normal_users')
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
        Schema::table('devices', function (Blueprint $table) {
            // 외래키 관계를 선언했다면, 리버스 마이그레이션 할때 에러를 피하기 위해
            // 테이블을 삭제하기 전에 외래키를 먼저 삭제하는 것이 중요하다.
            $table->dropForeign('devices_normal_user_id_foreign');
        });
        Schema::dropIfExists('devices');
    }
}
