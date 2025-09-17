<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\ActivityPoint\Jobs\MigratePackagePurchase;

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
        MigratePackagePurchase::dispatch();
        if (Schema::hasColumn('apt_package_purchases', 'transaction_id')) {
            return;
        }

        Schema::table('apt_package_purchases', function (Blueprint $table) {
            $table->string('transaction_id')->nullable();
        });

        MigratePackagePurchase::dispatch();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('apt_package_purchases', 'transaction_id')) {
            Schema::dropColumns('apt_package_purchases', 'transaction_id');
        }
    }
};
