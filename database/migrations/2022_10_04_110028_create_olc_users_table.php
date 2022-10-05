<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlcUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olc_users', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            //$table->id();
            $table->integer('id')->autoIncrement();
            $table->string('userid',128)->unique()->comment('ユーザーID');
            $table->string('email',128)->comment('メール');
            $table->string('firstname',128)->comment('名');
            $table->string('lastname',128)->comment('姓');
            $table->string('nickname',128)->comment('氏名');
            $table->string('password',255)->comment('パスワード');
            $table->string('password_plain',255)->comment('パスワード');
            $table->date('birthday')->comment('生年月日');
            $table->integer('gender')->length(1)->comment('性別');
            $table->string('country',32)->comment('国コード');
            $table->string('mobile',20)->comment('携帯番号');
            $table->string('city',128)->comment('都市');
            $table->string('postal_code',16)->comment('郵便番号');
            $table->string('address',255)->comment('住所');
            $table->string('referrer',255)->comment('紹介者');
            $table->integer('kyc_status')->length(2)->comment('認証状態');
            $table->string('lang',4)->comment('言語');
            $table->string('session_id',255)->comment('セッションID');
            $table->timestamp('last_loginned')->comment('最終ログイン日時')->nullable();
            $table->integer('status')->length(2)->comment('状態');
            $table->string('avatar',255)->comment('プロフィール写真')->nullable();
            $table->string('token',255)->comment('トークン')->nullable();
            $table->string('remember_token',255)->comment('記憶トークン')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('olc_users');
    }
}
