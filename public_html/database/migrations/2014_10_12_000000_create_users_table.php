<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id', false);

            $table->string('user_name', 100)
                ->unique('user_name');
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('email', 122)
                ->unique('password');
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_invisible')->default(0);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
