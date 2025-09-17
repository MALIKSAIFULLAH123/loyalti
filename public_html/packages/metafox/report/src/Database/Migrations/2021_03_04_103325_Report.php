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
        if (!Schema::hasTable('report_reasons')) {
            Schema::create('report_reasons', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->string('name');
                $table->unsignedInteger('ordering')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('report_items')) {
            Schema::create('report_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphItemColumn($table);
                $table->unsignedInteger('reason_id')->nullable();
                $table->string('ip_address', 50)->nullable();
                $table->mediumText('feedback')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('report_owners')) {
            Schema::create('report_owners', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                DbTableHelper::totalColumns($table, ['report']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('report_owner_users')) {
            Schema::create('report_owner_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::morphUserColumn($table);
                $table->unsignedInteger('report_id');
                $table->unsignedInteger('reason_id')->nullable();
                $table->string('ip_address', 50)->nullable();
                $table->mediumText('feedback')->nullable();
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
        Schema::dropIfExists('report_data');
        Schema::dropIfExists('report_reasons');
        Schema::dropIfExists('report_items');
        Schema::dropIfExists('report_owners');
        Schema::dropIfExists('report_owner_users');
    }
};
