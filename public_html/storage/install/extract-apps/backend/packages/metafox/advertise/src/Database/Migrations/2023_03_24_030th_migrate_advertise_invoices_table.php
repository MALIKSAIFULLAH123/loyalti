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
        if (!Schema::hasTable('advertise_invoices')) {
            return;
        }

        Schema::table('advertise_invoices', function (Blueprint $table) {
            $table->string('item_deleted_title', 255)
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('advertise_invoices')) {
            return;
        }

        Schema::table('advertise_invoices', function (Blueprint $table) {
            $table->dropColumn(['item_deleted_title']);
        });
    }
};
