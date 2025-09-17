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
    public const DELETED_AT = 'deleted_at';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn('forums', self::DELETED_AT)) {
            Schema::table('forums', function (Blueprint $table) {
                $table->softDeletes(self::DELETED_AT);
            });
        }

        if (!Schema::hasColumn('forums', 'level')) {
            Schema::table('forums', function (Blueprint $table) {
                $table->unsignedInteger('level')
                    ->default(1);
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
        if (Schema::hasColumn('forums', self::DELETED_AT)) {
            Schema::table('forums', function (Blueprint $table) {
                $table->dropSoftDeletes(self::DELETED_AT);
            });
        }

        if (Schema::hasColumn('forums', 'level')) {
            Schema::table('forums', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};
