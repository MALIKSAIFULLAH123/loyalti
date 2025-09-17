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
        if (!Schema::hasTable('auth_permissions')) {
            return;
        }

        Schema::table('auth_permissions', function (Blueprint $table) {
            $table->tinyInteger('is_editable')
                ->after('is_public')
                ->default(1)
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('auth_permissions')) {
            return;
        }

        Schema::table('auth_permissions', function (Blueprint $table) {
            $table->dropColumn('is_editable');
        });
    }
};
