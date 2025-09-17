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
        if (!Schema::hasTable('core_timezones')) {
            Schema::create('core_timezones', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('offset', 10);
                $table->string('diff_from_gtm');
                $table->unsignedTinyInteger('is_active')->default(1);
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
        Schema::dropIfExists('core_timezones');
    }
};
