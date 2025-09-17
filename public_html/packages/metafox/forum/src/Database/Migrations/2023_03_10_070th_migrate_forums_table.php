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
        if (!Schema::hasTable('forums')) {
            return;
        }

        if (Schema::hasColumn('forums', 'description')) {
            return;
        }

        Schema::table('forums', function (Blueprint $table) {
            $table->text('description')->after('title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('forums')) {
            return;
        }

        if (!Schema::hasColumn('forums', 'description')) {
            return;
        }

        Schema::table('forums', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
