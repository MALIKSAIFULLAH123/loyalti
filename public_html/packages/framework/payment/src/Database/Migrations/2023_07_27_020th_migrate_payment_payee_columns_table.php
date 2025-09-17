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
        if (!Schema::hasTable('payment_orders')) {
            return;
        }

        Schema::table('payment_orders', function (Blueprint $table) {
            DbTableHelper::morphColumn($table, 'payee', true, 'payment_payee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('payment_orders')) {
            return;
        }

        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropColumn(['payee_type', 'payee_id']);
        });
    }
};
