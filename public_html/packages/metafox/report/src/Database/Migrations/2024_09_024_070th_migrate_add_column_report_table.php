<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Report\Models\ReportReason;

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
        if (!Schema::hasTable('report_reasons')) {
            return;
        }

        Schema::table('report_reasons', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('name');
        });

        ReportReason::query()->updateOrCreate(['name' => 'report::phrase.other_reason_title'], ['ordering' => 0, 'is_default' => true]);
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('report_reasons', 'is_default');
    }
};
