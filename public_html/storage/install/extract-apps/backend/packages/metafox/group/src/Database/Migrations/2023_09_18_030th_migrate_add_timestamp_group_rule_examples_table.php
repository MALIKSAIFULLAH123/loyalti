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
        if (Schema::hasTable('group_rule_examples')) {
            Schema::table('group_rule_examples', function (Blueprint $table) {
                $table->timestamps();
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
        if (Schema::hasColumns(
            'group_rule_examples',
            ['created_at', 'updated_at']
        )) {
            Schema::table('group_rule_examples', function (Blueprint $table) {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
