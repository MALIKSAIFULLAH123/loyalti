<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Models\ReportItemAggregate;

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
        if (!Schema::hasTable('report_items')) {
            return;
        }

        if (!Schema::hasTable('report_item_aggregate')) {
            $this->addAggregateTable();
            $this->generateReportAggregation();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function addAggregateTable(): void
    {
        Schema::create('report_item_aggregate', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphItemColumn($table);
            DbTableHelper::morphColumn($table, 'last_user');
            DbTableHelper::totalColumns($table, ['reports']);
            $table->timestamps();
        });
    }

    protected function generateReportAggregation(): void
    {
        $data       = [];
        $allReports = ReportItem::query()
            ->orderBy('item_type', 'desc')
            ->orderBy('item_id', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->collect()
            ->groupBy(['item_type', 'item_id']);

        foreach ($allReports as $itemType => $items) {
            foreach ($items as $itemId => $list) {
                $last = $list->first();
                if (!$last instanceof ReportItem) {
                    continue;
                }

                $data[] = [
                    'item_type'      => $itemType,
                    'item_id'        => $itemId,
                    'last_user_id'   => $last->userId(),
                    'last_user_type' => $last->userType(),
                    'total_reports'  => $list->count(),
                    'created_at'     => $last->created_at,
                    'updated_at'     => $last->updated_at,
                ];
            }
        }

        ReportItemAggregate::factory()->createMany($data);
    }
};
