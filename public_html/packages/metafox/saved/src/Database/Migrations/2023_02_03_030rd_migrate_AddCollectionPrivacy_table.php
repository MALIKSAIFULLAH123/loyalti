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
        if (!Schema::hasColumn('saved_lists', 'privacy')) {
            Schema::table('saved_lists', function (Blueprint $table) {
                $table->unsignedTinyInteger('privacy')
                    ->default(0)
                    ->index();
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
        if (Schema::hasColumn('saved_lists', 'privacy')) {
            Schema::table('saved_lists', function (Blueprint $table) {
                $table->dropColumn(['privacy']);
            });
        }
    }
};
