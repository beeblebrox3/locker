<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
        Schema::create('users', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name', 140);
            $table->string('email', 120)->unique();
            $table->string('password', 60);
            $table->string('confirmation_code', 40)->nullable();
            $table->dateTime('confirmed')->nullable();
            $table->tinyInteger('change_password')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('users');
	}

}
