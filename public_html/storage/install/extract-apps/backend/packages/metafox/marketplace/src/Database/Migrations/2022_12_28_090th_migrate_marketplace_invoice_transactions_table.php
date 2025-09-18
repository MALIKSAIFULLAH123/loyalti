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
        if (!Schema::hasTable('marketplace_invoice_transactions')) {
            return;
        }

        $column = 'payment_gateway';

        if (Schema::hasColumn('marketplace_invoice_transactions', $column)) {
            return;
        }

        Schema::table('marketplace_invoice_transactions', function (Blueprint $table) use ($column) {
            $table->integer($column, false, true)
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
