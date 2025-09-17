<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Payment\Models\Gateway;

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

        $paypal = Gateway::query()
            ->where('service', '=', 'paypal')
            ->first();

        if (!$paypal instanceof Gateway) {
            return;
        }

        $paypal->update(['enable_seller_config' => true]);
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
