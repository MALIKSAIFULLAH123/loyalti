<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\EMoney\Support\Support;

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
        if (!Schema::hasTable('emoney_transactions')) {
            return;
        }

        if (!Schema::hasColumn('emoney_transactions', 'actor_type')) {
            Schema::table('emoney_transactions', function (Blueprint $table) {
                $table->string('actor_type', 10)
                    ->index('et_actor_type')->default(Support::TRANSACTION_ACTOR_TYPE_USER);
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
        if (Schema::hasColumn('emoney_transactions', 'level')) {
            Schema::table('emoney_transactions', function (Blueprint $table) {
                $table->dropColumn(['actor_type']);
            });
        }
    }
};
