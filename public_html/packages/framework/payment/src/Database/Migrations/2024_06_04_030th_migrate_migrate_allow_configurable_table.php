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
        if (!Schema::hasTable('payment_gateway')) {
            return;
        }

        $this->addBuyerConfigurableColumn();
        $this->addSellerConfigurableColumn();
    }

    protected function addBuyerConfigurableColumn(): void
    {
        if (Schema::hasColumn('payment_gateway', 'enable_buyer_config')) {
            return;
        }

        Schema::table('payment_gateway', function (Blueprint $table) {
            $table->boolean('enable_buyer_config')
                ->default(false)
                ->index('pg_buyer_config');
        });
    }

    protected function addSellerConfigurableColumn(): void
    {
        if (Schema::hasColumn('payment_gateway', 'enable_seller_config')) {
            return;
        }

        Schema::table('payment_gateway', function (Blueprint $table) {
            $table->boolean('enable_seller_config')
                ->default(false)
                ->index('pg_seller_config');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('payment_gateway', ['enable_buyer_config', 'enable_seller_config']);
    }
};
