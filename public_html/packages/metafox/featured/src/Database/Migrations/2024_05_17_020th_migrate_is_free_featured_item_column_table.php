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
        if (!Schema::hasTable('featured_items')) {
            return;
        }

        if (Schema::hasColumn('featured_items', 'is_free')) {
            return;
        }

        Schema::table('featured_items', function (Blueprint $table) {
            $table->boolean('is_free')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('featured_items')) {
            return;
        }

        if (!Schema::hasColumn('featured_items', 'is_free')) {
            return;
        }

        Schema::dropColumns('featured_items', ['is_free']);
    }
};
