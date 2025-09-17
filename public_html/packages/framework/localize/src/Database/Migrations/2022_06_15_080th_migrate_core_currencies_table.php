<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('core_currencies')) {
            Schema::create('core_currencies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('code', 3)->unique();
                $table->string('symbol', 15);
                $table->string('name');
                $table->string('format')->default('{0} #,###.00 {1}');
                $table->unsignedTinyInteger('is_default')->default(0);
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedSmallInteger('ordering')->default(0);
            });
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_currencies');
    }
};
