<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (Schema::hasTable('subscription_packages')) {
            if (!Schema::hasColumn('subscription_packages', 'image_file_id')) {
                Schema::table('subscription_packages', function (Blueprint $table) {
                    DbTableHelper::imageColumns($table);
                });
            }
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('subscription_packages', 'image_file_id')) {
            Schema::table('subscription_packages', function (Blueprint $table) {
                $table->dropColumn(['image_file_id']);
            });
        }
    }
};
