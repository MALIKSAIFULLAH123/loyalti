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
        $this->createRequestTable();
        $this->createStatisticTable();
        $this->createExchangeAggregateTable();
    }

    private function createExchangeAggregateTable()
    {
        if (Schema::hasTable('apt_conversion_aggregate')) {
            return;
        }

        Schema::create('apt_conversion_aggregate', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->timestamp('date');
            $table->integer('total', false, true)
                ->default(0);
        });
    }

    private function createStatisticTable(): void
    {
        if (Schema::hasTable('apt_conversion_statistics')) {
            return;
        }

        Schema::create('apt_conversion_statistics', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->bigInteger('total_converted', false, true)
                ->default(0);
            $table->bigInteger('total_pending', false, true)
                ->default(0);
        });
    }

    private function createRequestTable(): void
    {
        if (Schema::hasTable('apt_conversion_requests')) {
            return;
        }

        Schema::create('apt_conversion_requests', function (Blueprint $table) {
            $table->id();
            DbTableHelper::morphUserColumn($table);
            $table->bigInteger('points', false, true);
            $table->char('currency', 3);
            $table->decimal('total', 14, 2);
            $table->decimal('commission', 14, 2)
                ->default(0);
            $table->decimal('actual', 14, 2);
            $table->string('status', 15)
                ->default(\MetaFox\ActivityPoint\Support\PointConversion::TRANSACTION_STATUS_PENDING);
            $table->text('denied_reason')
                ->nullable();
            $table->timestamps();
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
