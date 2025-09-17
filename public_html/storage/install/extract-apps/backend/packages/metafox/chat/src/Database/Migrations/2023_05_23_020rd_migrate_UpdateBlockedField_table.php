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
        if (!Schema::hasColumn('chat_subscriptions', 'is_block')) {
            Schema::table('chat_subscriptions', function (Blueprint $table) {
                $table->tinyInteger('is_block')
                    ->nullable()
                    ->default(null);
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
        if (Schema::hasColumn('chat_subscriptions', 'is_block')) {
            Schema::table('chat_subscriptions', function (Blueprint $table) {
                $table->dropColumn(['is_block']);
            });
        }
    }
};
