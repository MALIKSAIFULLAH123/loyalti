<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('advertise_sponsors')) {
            return;
        }

        Schema::table('advertise_sponsors', function (Blueprint $table) {
            $table->string('sponsor_type', 15)
                ->default('item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('advertise_sponsors')) {
            return;
        }

        Schema::table('advertise_sponsors', function (Blueprint $table) {
            $table->dropColumn('sponsor_type');
        });
    }
};
