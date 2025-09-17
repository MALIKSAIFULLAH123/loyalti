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
        if (Schema::hasTable('apt_transactions')) {
            Schema::table('apt_transactions', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_admincp')->default(0);
            });
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
        if (Schema::hasColumn('apt_transactions', 'is_admincp')) {
            Schema::table('apt_transactions', function (Blueprint $table) {
                $table->dropColumn(['is_admincp']);
            });
        }
    }
};
