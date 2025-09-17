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
        if (!Schema::hasTable('search_items')) {
            return;
        }

        if (Schema::hasColumn('search_items', 'status')) {
            return;
        }

        Schema::table('search_items', function (Blueprint $table) {
            $table->string('status', 15)
                ->default(\MetaFox\Search\Support\Support::STATUS_VIEWABLE)
                ->index('si_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('search_items')) {
            return;
        }

        if (!Schema::hasColumn('search_items', 'status')) {
            return;
        }

        Schema::dropColumns('search_items', ['status']);
    }
};
