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
        if (!Schema::hasTable('payment_gateway_filters')) {
            Schema::create('payment_gateway_filters', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->string('entity_type');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_gateway_filter_data')) {
            Schema::create('payment_gateway_filter_data', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->unsignedBigInteger('gateway_id');
                $table->unsignedBigInteger('filter_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_filters');
        Schema::dropIfExists('payment_gateway_filter_data');
    }
};
