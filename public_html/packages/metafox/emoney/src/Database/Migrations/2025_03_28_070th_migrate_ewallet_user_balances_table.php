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
        $this->createBalanceAdjustmentTable();
        $this->addColumnsForStatisticTable();
    }

    protected function createBalanceAdjustmentTable(): void
    {
        if (Schema::hasTable('emoney_balance_adjustments')) {
            return;
        }

        Schema::create('emoney_balance_adjustments', function (Blueprint $table) {
            $table->id();

            DbTableHelper::morphUserColumn($table);

            DbTableHelper::morphOwnerColumn($table);

            $table->char('currency', 3);

            $table->decimal('amount', 14, 2, true);

            $table->enum('type', [\MetaFox\EMoney\Support\Support::USER_BALANCE_ACTION_SEND, \MetaFox\EMoney\Support\Support::USER_BALANCE_ACTION_REDUCE]);

            $table->timestamps();
        });
    }

    protected function addColumnsForStatisticTable(): void
    {
        if (!Schema::hasTable('emoney_statistics')) {
            return;
        }

        Schema::table('emoney_statistics', function (Blueprint $table) {
            $table->decimal('total_sent', 20, 2, true)
                ->default(0);
            $table->decimal('total_reduced', 20, 2, true)
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
        Schema::dropIfExists('emoney_balance_adjustments');

        if (Schema::hasColumns('emoney_statistics', ['total_sent', 'total_reduced'])) {
            Schema::dropColumns('emoney_statistics', ['total_sent', 'total_reduced']);
        }
    }
};
