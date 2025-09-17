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
        if (!Schema::hasTable('activity_subscriptions')) {
            return;
        }

        Schema::table('activity_subscriptions', function (Blueprint $table) {
            $table->string('special_type', 30)
                ->nullable()
                ->index('feed_subscribe_special_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('activity_subscriptions')) {
            return;
        }

        Schema::table('activity_subscriptions', function (Blueprint $table) {
            $table->dropColumn('special_type');
        });
    }
};
