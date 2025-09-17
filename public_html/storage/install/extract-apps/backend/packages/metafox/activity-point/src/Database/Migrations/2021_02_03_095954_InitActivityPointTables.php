<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('apt_packages')) {
            Schema::create('apt_packages', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->string('title', 255);
                $table->text('price');
                DbTableHelper::imageColumns($table);
                $table->unsignedInteger('amount')->default(0);
                $table->unsignedTinyInteger('is_active')->default(1);
                DbTableHelper::totalColumns($table, ['purchase']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('apt_package_purchases')) {
            Schema::create('apt_package_purchases', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('package_id');
                $table->unsignedInteger('status')->default(0);
                $table->string('currency_id', 3);
                $table->decimal('price', 14)->default(0.00);
                $table->unsignedInteger('gateway_id');
                $table->unsignedInteger('points')->default(0);
                DbTableHelper::morphUserColumn($table);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('apt_settings')) {
            Schema::create('apt_settings', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('role_id', false);
                $table->string('name', 250);
                $table->string('description_phrase', 250)->nullable();
                $table->string('action', 250);
                $table->unsignedTinyInteger('is_active')->default(1);

                DbTableHelper::moduleColumn($table);

                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('max_earned')->default(0);
                $table->unsignedInteger('period')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('apt_statistics')) {
            Schema::create('apt_statistics', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();

                $table->unsignedInteger('current_points')
                    ->default(0);

                $table->unsignedInteger('total_earned')
                    ->default(0);

                $table->unsignedInteger('total_bought')
                    ->default(0);

                $table->unsignedInteger('total_sent')
                    ->default(0);

                $table->unsignedInteger('total_spent')
                    ->default(0);

                $table->unsignedInteger('total_received')
                    ->default(0);

                $table->unsignedInteger('total_retrieved')
                    ->default(0);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('apt_transactions')) {
            Schema::create('apt_transactions', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                DbTableHelper::morphOwnerColumn($table, true); //Recipient

                DbTableHelper::moduleColumn($table);

                $table->unsignedBigInteger('point_setting_id')->nullable();

                $table->unsignedInteger('type')->default(1);

                $table->string('action', 255);

                $table->integer('points')
                    ->default(0);

                $table->unsignedTinyInteger('is_hidden')
                    ->default(0);

                $table->text('action_params')
                    ->nullable(true);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('apt_adjust')) {
            Schema::create('apt_adjust', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->enum('type', ['Earned', 'Bought', 'Sent', 'Spent', 'Received', 'Retrieved'])
                    ->default('Earned');

                $table->integer('points')
                    ->default(0);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apt_packages');
        Schema::dropIfExists('apt_package_purchases');
        Schema::dropIfExists('apt_settings');
        Schema::dropIfExists('apt_statistics');
        Schema::dropIfExists('apt_transactions');
        Schema::dropIfExists('apt_adjust');
    }
};
